<?php

namespace App\Policies;

use App\Models\Aircraft;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AircraftPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the user.
     *
     *
     * @return bool
     */
    public function view(User $currentUser, Aircraft $aircraft)
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
        return $currentUser->is_admin;
    }

    /**
     * Determine whether the user can update the company.
     *
     *
     * @return bool
     */
    public function update(User $currentUser, Aircraft $aircraft)
    {
        return $currentUser->is_admin;
    }

    /**
     * Determine whether the user can delete the company.
     *
     *
     * @return bool
     */
    public function delete(User $currentUser, Aircraft $aircraft)
    {
        return false;
    }
}
