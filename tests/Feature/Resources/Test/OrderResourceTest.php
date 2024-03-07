<?php

namespace Tests\Feature\Resources\Test;

use App\Models\Manual;
use Tests\Feature\Resources\ResourceTestCase;

class OrderResourceTest extends ResourceTestCase
{
  protected $resourceName = 'orders';

  public $defaultData = [
    'date_from' => '15-01-2023',
    'date_to' => '16-01-2023',
    'user_id' => 1,
    'manual_id' => 1,
  ];

  public $expectedJson = [
    'date_from',
    'date_to',
    'user_id',
    'manual_id',
  ];

  public function data($data = [])
  {
    $user = $this->createUser();

    return array_merge($this->defaultData, ['user_id' => $user->id], $data);
  }

  public function test_table_order_by()
  {
    $user = $this->createUser();

    $manual = Manual::factory()->create();

    $this->factory()->count(2)->for($user)->for($manual)->create();

    $this->performTestTableOrderBy('order=date_from|desc&per_page=2', function ($model) {
      return $model;
    });
  }

  public function test_table_search()
  {
    $model = $this->factory()->count(100)->create();
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

    $result = $this->performTestUpdateRequest($factory, $this->data());
  }

  public function test_request_create()
  {
    $result = $this->performTestCreateRequest($this->data());
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
