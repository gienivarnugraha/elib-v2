<?php

namespace Tests\Feature\Resources\Test;

use App\Models\Aircraft;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Resources\ResourceTestCase;

class ManualResourceTest extends ResourceTestCase
{
    protected $resourceName = 'manuals';

    public $defaultData = [
        'type' => 'test',
        'part_number' => 'test',
        'lib_call' => 'test',
        'title' => 'test',
        'volume' => 'test',
        'vendor' => 'test',
        'caplist' => 'test',
        'collector' => 'test',
        'index' => 'A', 'index_date' => '1975',
    ];

    public $expectedJson = [
        'type',
        'part_number',
        'lib_call',
        'title',
        'volume',
        'vendor',
        'caplist',
        'collector',
    ];

    public function data($data = [])
    {
        $aircraft = Aircraft::factory()->create();

        return array_merge($this->defaultData, ['aircraft_id' => $aircraft->id], $data);
    }

    public function test_manuals_has_revisions()
    {
        $this->withoutExceptionHandling();

        $manual = $this->performTestCreateRequest(
            $this->data(),
            ['index', 'index_date']
        );

        $revision = $this->model()->find($manual->id)->revisions();

        $file = UploadedFile::fake()->create('test-file.pdf', 'test-content', 'application/pdf');

        $this->postJson('/api/revisions/' . $revision->first()->id . '/upload', [
            'files' => $file,
        ])->assertOk();

        $file2 = UploadedFile::fake()->create('test-file-2.pdf', 'test-content', 'application/pdf');

        $this->postJson('/api/revisions/' . $revision->first()->id . '/upload', [
            'files' => $file2,
        ])->assertOk();

        $this->performTestUpdateRequest($manual->id, [
            'revision' => [
                [
                    'title' => fake()->title,
                    'body' => fake()->word,
                ],
                [
                    'title' => fake()->title,
                    'body' => fake()->word,
                ],
            ],
        ]);

        $update = $revision->latest()->id;

        dd($update);

        $file3 = UploadedFile::fake()->create('test-file-3.pdf', 'test-content', 'application/pdf');

        $this->postJson('/api/revisions/' . $update->id . '/upload', [
            'files' => $file3,
        ])->assertOk();

        dd();
    }

    public function test_table_order_by()
    {
        $this->factory()->count(100)->create();
        $this->performTestTableOrderBy('order=subject|asc&per_page=100', function ($model) {
            return $model;
        });
    }

    public function test_table_search()
    {
        $model = $this->factory()->count(1000)->create();
        $result = $this->performTestTableSearch('q=' . substr($model[0]->name, 1, -1));
        $this->assertSame($model[0]->name, $result[0]->name);
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
        $this->performTestCreateResource(
            $this->data(),
            ['index', 'index_date']
        );
    }

    public function test_resource_delete()
    {
        $factory = $this->factory(5)->create();
        $this->performTestDeleteResource($factory);
    }

    public function test_request_update()
    {
        $factory = $this->factory()->create();

        $this->performTestUpdateRequest($factory, $this->data());
    }

    public function test_request_create()
    {
        $this->performTestCreateRequest($this->data(
            ['index' => 'A', 'index_date' => '1975']
        ));
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
