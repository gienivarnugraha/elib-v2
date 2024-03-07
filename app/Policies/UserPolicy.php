<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     *
     *
     * @return bool
     */
    public function viewAny(User $currentUser)
    {
        return $currentUser->is_admin;
    }

    /**
     * Determine whether the user can view the user.
     *
     *
     * @return bool
     */
    public function view(User $currentUser, User $user)
    {
        return true;
    }

    /**
     * Determine if the given user can create users.
     *
     *
     * @return bool
     */
    public function create(User $currentUser)
    {
        // Onlysuper admins
        return $currentUser->is_admin;
    }

    /**
     * Determine whether the user can update the company.
     *
     *
     * @return bool
     */
    public function update(User $currentUser, User $user)
    {
        // Only admins
        return $currentUser->is_admin || $currentUser->id === $user->id;
    }

    /**
     * Determine whether the user can delete the company.
     *
     *
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        // Only super admins
        return false;
    }
}
