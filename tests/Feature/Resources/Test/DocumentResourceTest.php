<?php

namespace Tests\Feature\Resources\Test;

use App\Core\Application\Filters\Fields\Select;
use App\Core\Criteria\UserCriteria;
use App\Models\Aircraft;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Resources\ResourceTestCase;

class DocumentResourceTest extends ResourceTestCase
{
    protected $resourceName = 'documents';

    public $defaultData = [
        'office' => 'DOA',
        'type' => 'EO',
        'subject' => 'test',
        'reference' => 'test',
    ];

    public $expectedJson = [
        'no',
        'office',
        'type',
        'subject',
        'reference',
        'aircraft_id',
        'assignee_id',
    ];

    public function data($data = [])
    {
        $user = $this->createUser();
        $aircraft = Aircraft::factory()->create();

        return array_merge($this->defaultData, ['aircraft_id' => $aircraft->id, 'assignee_id' => $user->id], $data);
    }

    public function getLatestRevision($documentId)
    {
        return $this->model()->find($documentId)->revisions()->orderBy('id', 'desc')->latest()->first();
    }

    public function test_documents_has_revisions()
    {
        $this->withoutExceptionHandling();

        $document = $this->performTestCreateRequest($this->data());

        $latestRevision = $this->getLatestRevision($document->id);

        $file = UploadedFile::fake()->create('test-file.pdf', 'test-content', 'application/pdf');
        $file2 = UploadedFile::fake()->create('test-file-2.pdf', 'test-content', 'application/pdf');

        $this->postJson('/api/revisions/'.$latestRevision->id.'/upload', [
            'files' => $file,
        ])->assertOk();

        $this->postJson('/api/revisions/'.$latestRevision->id.'/upload', [
            'files' => $file2,
        ])->assertOk();

        $this->putJson('/api/revisions/'.$latestRevision->id.'/close', ['is_closed' => true]);

        $this->assertTrue($this->model()->find($document->id)->revisions()->find($latestRevision->id)->is_closed);

        $this->performTestUpdateRequest($document->id, [
            'title' => fake()->title,
            'body' => fake()->word,
        ]);

        $this->assertSame('B', $this->getLatestRevision($document->id)->index);
    }

    public function test_numbering_system()
    {
        $aircraft = Aircraft::factory()->create();
        $this->factory(7)->for($aircraft)->state(new Sequence(
            ['type' => 'JE'],
            ['office' => 'AMO', 'type' => 'ES'],
            ['office' => 'AMO', 'type' => 'TD'],
            ['office' => 'AMO', 'type' => 'EO'],
            ['office' => 'DOA', 'type' => 'ES'],
            ['office' => 'DOA', 'type' => 'TD'],
            ['office' => 'DOA', 'type' => 'EO'],
        ))->create();

        $onlyAircraftType = filter_var($aircraft->type, FILTER_SANITIZE_NUMBER_INT);
        $this->assertSame($this->model()->generateNumber('JE', null, $aircraft->type), 'JE/002/MS1000/12/2023', 'error JE');
        $this->assertSame($this->model()->generateNumber('ES', 'AMO', $aircraft->type), 'ES/'.$aircraft->type.'/MS1000/23-002', 'error ES AMO');
        $this->assertSame($this->model()->generateNumber('EO', 'AMO', $aircraft->type), 'EO-'.$onlyAircraftType.'-2023-002', 'error EO AMO');
        $this->assertSame($this->model()->generateNumber('TD', 'AMO', $aircraft->type), $aircraft->type.'/MS1000/23-002', 'error TD AMO');
        $this->assertSame($this->model()->generateNumber('ES', 'DOA', $aircraft->type), $onlyAircraftType.'.AS.ES.2023.002', 'error ES DOA');
        $this->assertSame($this->model()->generateNumber('EO', 'DOA', $aircraft->type), $onlyAircraftType.'.AS.EO.2023.002', 'error EO DOA');
        $this->assertSame($this->model()->generateNumber('TD', 'DOA', $aircraft->type), $onlyAircraftType.'.AS.TD.2023.002', 'error TD DOA');

        $new = $this->performTestCreateRequest([
            'aircraft_id' => $aircraft->id,
            'office' => 'AMO',
            'type' => 'ES',
            'subject' => 'test',
            'reference' => 'test',
        ]);

        $this->assertSame($new->no, 'ES/'.$aircraft->type.'/MS1000/23-002', 'error ES AMO update');

    }

    public function test_filter_fields()
    {
        $user = $this->asRegularUser()->signIn();
        $documents = $this->factory(10)->for($user, 'assignee')->create();

        $user2 = $this->createUser();
        $this->factory(10)->for($user2, 'assignee')->create();

        $rules = [
            [
                'type' => 'select',
                'attribute' => 'assignee.name',
                'operator' => 'equal',
                'value' => $user->name,
            ],
        ];

        $filters = [
            Select::make('assignee.name', 'By User'),
        ];

        $result = $this->getFilterByFields($rules, $filters);

        $this->assertSame($result->count(), $documents->count());
    }

    public function test_filter_owned_documents()
    {
        $user = $this->asRegularUser()->signIn();
        $documents = $this->factory(10)->for($user, 'assignee')->create();

        $user2 = $this->createUser();
        $this->factory(10)->for($user2, 'assignee')->create();

        $result = $this->repository()->pushCriteria(new UserCriteria($user, 'assignee_id'))->all();

        $this->assertSame($result->count(), $documents->count());
    }

    public function test_table_order_by()
    {
        $this->factory()->count(10)->create();
        $this->performTestTableOrderBy('order=no|asc&per_page=10', function ($model) {
            return [
                'id' => $model->id,
                'no' => $model->no,
                'type' => $model->type->value,
                'subject' => $model->subject,
            ];
        });
    }

    public function test_table_search()
    {
        $model = $this->factory()->count(10)->create();
        $result = $this->performTestTableSearch('q='.substr($model[0]->serial_num, 1, -1));
        $this->assertSame($model[0]->serial_num, $result[0]->serial_num);
    }

    public function test_unauthenticated_user_cannot_access_resource_ednpoints()
    {
        $this->testUnauthenticatedUserCannotAccessResourceEdnpoints();
    }

    public function test_table_can_load_with_all_fields()
    {
        $this->performTestTableRequestWithAllFields();
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

    public function test_request_create()
    {
        $this->performTestCreateRequest($this->data());
    }

    public function test_request_delete()
    {
        $factory = $this->factory()->create();
        $this->performTestDeleteRequest($factory);
    }

    /* OPTIONAL TEST */

    public function test_regular_admin_policy()
    {
        $this->asRegularAdmin()->signIn();

        $factory = $this->factory()->create();

        $this->getJson($this->indexEndpoint())->assertOk();
        $this->getJson($this->showEndpoint($factory))->assertOk();
        $this->postJson($this->createEndpoint(), $this->data())->assertCreated();
        $this->deleteJson($this->deleteEndpoint($factory))->assertForbidden();
    }
}
