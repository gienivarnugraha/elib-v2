<?php

namespace Tests\Feature\Resources\Test;

use App\Core\Application\Filters\Fields\Numeric;
use App\Core\Application\Filters\Fields\Select;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Feature\Resources\ResourceTestCase;

class AircraftResourceTest extends ResourceTestCase
{
    protected $resourceName = 'aircraft';

    public $defaultData = [
        'type' => '212',
        'serial_num' => 'N110',
        'reg_code' => 'A2119',
        'effectivity' => 'A1235',
        'owner' => 'PHAF',
        'manuf_date' => '2018',
    ];

    public $expectedJson = [
        'type',
        'serial_num',
        'reg_code',
        'effectivity',
        'owner',
        'manuf_date',
    ];

    public function test_filter_by_type_and_manuf_date()
    {
        $this->factory(10)->state(new Sequence(
            ['manuf_date' => $lastyear = 2020],
            ['manuf_date' => 2018],
            ['manuf_date' => $yearsago = 2015],
            ['manuf_date' => 2010],
        ))->create();

        $type = 'CN235';

        $rules = [
            [
                'type' => 'select',
                'attribute' => 'type',
                'operator' => 'equal',
                'value' => $type,
            ],
            [
                'type' => 'numeric',
                'attribute' => 'manuf_date',
                'operator' => 'between',
                'value' => [$yearsago, $lastyear],
            ],
        ];

        $filters = [
            Select::make('type', 'Aircraft Type'),
            Numeric::make('manuf_date', 'Aircraft Type'),
        ];

        $result = $this->getFilterByFields($rules, $filters);

        $this->assertSame($this->model()->type($type)->betweenManufDate([$yearsago, $lastyear])->count(), $result->count());
    }

    public function test_table_order_by()
    {
        $this->factory()->count(100)->create();

        $this->performTestTableOrderBy('order=serial_num|asc,type|asc&per_page=50', function ($model) {
            return [
                'id' => $model->id,
                'type' => $model->type,
                'serial_num' => $model->serial_num,
            ];
        });
    }

    public function test_table_search()
    {
        $model = $this->factory()->count(1000)->create();

        $result = $this->performTestTableSearch('q='.substr($model[0]->owner, 1, -1));

        $this->assertSame($model[0]->owner, $result[0]->owner);
    }

    public function test_unauthenticated_user_cannot_access_resource_ednpoints()
    {
        $this->testUnauthenticatedUserCannotAccessResourceEdnpoints();
    }

    public function test_table_can_load_with_all_fields()
    {
        $this->performTestTableRequestWithAllFields();
    }

    public function test_resource_update()
    {
        $result = $this->performTestUpdateResource($this->data());

    }

    public function test_resource_create()
    {
        $result = $this->performTestCreateResource($this->data());
    }

    public function test_resource_delete()
    {
        $factory = $this->factory(5)->create();
        $this->performTestDeleteResource($factory);
    }

    public function test_request_update()
    {
        $factory = $this->factory()->create();

        $result = $this->performTestUpdateRequest($factory, $this->data())
            ->assertJsonStructure($this->expectedJson);
    }

    public function test_request_create()
    {
        $result = $this->performTestCreateRequest($this->data())
            ->assertJsonStructure($this->expectedJson);
    }

    public function test_request_delete()
    {
        $factory = $this->factory()->create();
        $this->performTestDeleteRequest($factory);
    }

    /* OPTIONAL TEST */

    public function test_regular_admin_policy()
    {
        $this->regularAdminPolicy($this->data());
    }
}
