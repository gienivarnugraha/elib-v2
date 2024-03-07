<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->userSeed();

        User::factory(5)->create()->each(function ($user) {
            \App\Models\Aircraft::factory(5)->create()->each(function ($aircraft) use ($user) {
                \App\Models\Document::factory(2)->for($user, 'assignee')->for($aircraft)->hasRevisions(['user_id' => $user->id])->create();
                \App\Models\Manual::factory(2)->for($aircraft)->hasRevisions(['user_id' => $user->id])->create();
            });
        });
    }

    public function userSeed()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'view any', 'guard_name' => 'api']);
        Permission::create(['name' => 'view owned', 'guard_name' => 'api']);

        $superadminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'api']);
        $superadminRole->givePermissionTo('view any');

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo('view any');

        $regularRole = Role::create(['name' => 'user', 'guard_name' => 'api']);
        $regularRole->givePermissionTo('view owned');

        $superadmin = User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
        ]);

        $admin = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
        ]);

        $regular = User::factory()->create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
        ]);

        Setting::factory()->for($superadmin)->create();
        Setting::factory()->for($admin)->create();
        Setting::factory()->for($regular)->create();

        $superadmin->assignRole($superadminRole);
        $admin->assignRole($adminRole);
        $regular->assignRole($regularRole);
    }
}
