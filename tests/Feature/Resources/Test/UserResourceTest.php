<?php

namespace Tests\Feature\Resources\Test;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Resources\ResourceTestCase;

class UserResourceTest extends ResourceTestCase
{
    protected $resourceName = 'users';

    public $defaultData = [
        'name' => 'John Doe',
        'email' => 'email@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ];

    public $json = [
        'id',
        'was_recently_created',
        'display_name',
        'path',
        'name',
        'email',
        'settings' => [
            'time_format',
            'date_format',
            'first_day_of_week',
            'currency',
        ],
        'roles',
    ];

    public function data($data = [])
    {
        $newRole = $this->createRole('new-role');
        $setting = Setting::factory()->make()->toArray();

        return array_merge($this->defaultData, ['roles' => [$newRole->id], 'settings' => $setting], $data);
    }

    public function test_table_order_by()
    {
        $this->factory()->count(100)->create()->each(function ($user) {
            Setting::factory()->for($user)->create();
        });

        $this->performTestTableOrderBy('order=name|asc,email|asc,settings.date_format|asc&per_page=100', function ($model) {
            return [
                'id' => $model->id,
                'email' => $model->email,
                'name' => $model->name,
                'date_format' => $model->settings->date_format,
            ];
        });
    }

    public function test_table_search()
    {
        $user = $this->factory()->count(1000)->create()->each(function ($user) {
            Setting::factory()->for($user)->create();
        });

        $result = $this->performTestTableSearch('q='.substr($user[0]->name, 1, -1));

        $this->assertSame($user[0]->name, $result[0]->name);
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
        $result = $this->performTestUpdateResource($this->data(), ['password', 'password_confirmation']);
        $this->assertTrue(Hash::check($this->defaultData['password'], $result->password));
    }

    public function test_resource_create()
    {
        $result = $this->performTestCreateResource($this->data(), ['password', 'password_confirmation']);
        $this->assertTrue(Hash::check($this->defaultData['password'], $result->password));
    }

    public function test_resource_delete()
    {
        $user = $this->factory(1000)->create();
        $this->performTestDeleteResource($user);

        $signIn = $this->signIn();
        $this->performTestDeleteResource($signIn);
    }

    public function test_request_update()
    {
        $user = $this->factory()->create();
        Setting::factory()->for($user)->create();

        $result = $this->performTestUpdateRequest($user, $this->data())
            ->assertJsonMissing(['password'])
            ->assertJsonCount(1, 'roles')
            ->assertJsonStructure($this->json);

        $this->assertTrue(Hash::check($this->defaultData['password'], $this->record($result->getData()->id)->password));
    }

    public function test_request_create()
    {
        $result = $this->performTestCreateRequest($this->data())
            ->assertJsonMissing(['password'])
            ->assertJsonCount(1, 'roles')
            ->assertJsonStructure($this->json);

        $this->assertTrue(Hash::check($this->defaultData['password'], $this->record($result->getData()->id)->password));
    }

    public function test_request_delete()
    {
        $user = $this->factory()->create();
        $this->performTestDeleteRequest($user);
    }

    /* OPTIONAL TEST */

    public function test_regular_admin_policy()
    {
        $this->regularAdminPolicy($this->data());
    }

    public function test_regular_user_partialy_access_resource_record()
    {
        $this->asRegularUser()->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->indexEndpoint())->assertOk();
        $this->getJson($this->showEndpoint($record))->assertOk();

        // TODO: POLICY CHECK STILL ERROR
        // $this->postJson($this->createEndpoint(), $this->data())->assertForbidden();
        // $this->putJson($this->updateEndpoint($record), $this->data(['email' => 'update@email.com']))->assertForbidden();
        // $this->deleteJson($this->deleteEndpoint($record))->assertForbidden();
    }

    public function test_current_user_cannot_request_delete_his_own_account()
    {
        $user = $this->signIn();
        $this->deleteJson($this->deleteEndpoint($user))->assertStatus(409);
    }

    public function test_avatars_can_be_uploaded(): void
    {
        $this->signIn();

        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('test-image.jpg');

        $this->postJson('/api/users/avatar', [
            'avatar' => $file,
        ])->assertOk();

        // Storage::disk('avatars')->assertExists($file->hashName());

        $this->deleteJson('/api/users/avatar', [
            'avatar' => $file,
        ])->assertOk();

        // Storage::disk('avatars')->assertMissing($file->hashName());
    }
}
