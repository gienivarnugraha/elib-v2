<?php

namespace Tests\Traits;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait CreateUser
{
    /**
     * The permissions for the role
     * By default the role does not have any permissions
     *
     * @var array
     */
    protected $withPermissionsTo = [];

    /**
     * The user create attributes to merge
     *
     * @see  signIn method
     *
     * @var array
     */
    protected $userAttributes = [];

    /**
     * The user role
     *
     * @see  signIn method
     *
     * @var string
     */
    protected $role = 'super-admin';

    /**
     * Create test user
     *
     * @param  mixed  $parameters
     * @return \App\Models\User
     */
    protected function createUser(...$parameters)
    {
        $user = User::factory(...$parameters)->create($this->userAttributes);

        $role = $this->createRole($this->role);

        $user->assignRole($role);

        if (count($this->withPermissionsTo) > 0) {
            $this->giveUserPermissions($user);
        }

        return $user;
    }

    /**
     * Sign in
     *
     * @param  \App\Models\User|null  $user
     * @return \App\Models\User
     */
    protected function signIn($as = null)
    {
        $user = $as ?: $this->createUser();

        if (! $as && count($this->withPermissionsTo) > 0) {
            $this->giveUserPermissions($user);
        }

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * As regular user helper
     *
     * @return self
     */
    protected function asRegularUser()
    {
        $this->role = 'regular-user';

        return $this;
    }

    /**
     * As regular user helper
     *
     * @return self
     */
    protected function asRegularAdmin()
    {
        $this->role = 'regular-admin';

        return $this;
    }

    /**
     * As regular user helper
     *
     * @return self
     */
    protected function asSuperAdmin()
    {
        $this->role = 'super-admin';

        return $this;
    }

    /**
     * With permissions to
     *
     * @param  array  $permissions
     * @return self
     */
    protected function withPermissionsTo($permissions = [])
    {
        if (! is_array($permissions)) {
            $permissions = [$permissions];
        }

        $this->withPermissionsTo = $permissions;

        return $this;
    }

    /**
     * Set user attributes
     *
     *
     * @return self
     */
    protected function userAttrs(array $attributes)
    {
        $this->userAttributes = $attributes;

        return $this;
    }

    /**
     * Assign the provide user permissions to the given user
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    private function giveUserPermissions($user)
    {
        $user->role->givePermissionTo($this->withPermissionsTo);

        // Reset attributes in case $this->createUser() is called again
        $this->withPermissionsTo = [];
        $this->userAttributes = $this->defaultUserAttributes;
    }

    /**
     * Create new role
     *
     * @param  string|null  $name
     * @param  string  $guardName
     * @return \App\Core\Models\Role
     */
    protected function createRole($name, $guardName = 'api')
    {
        return Role::findOrCreate($name, $guardName);
    }

    /**
     * Create new permission
     *
     * @param  string|null  $name
     * @param  string  $guardName
     * @return \App\Core\Models\Permission
     */
    protected function createPermission($name, $guardName = 'api')
    {
        return Permission::findOrCreate($name, $guardName);
    }
}
