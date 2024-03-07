<?php

namespace App\Policies;

use App\Models\Revision;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RevisionPolicy
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
        return false;
    }

    /**
     * Determine whether the user can view the user.
     *
     *
     * @return bool
     */
    public function view(User $currentUser, Revision $revision)
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
        return true;
    }

    /**
     * Determine whether the user can update the company.
     *
     *
     * @return bool
     */
    public function update(User $currentUser, Revision $revision)
    {
        return $currentUser->id === $revision->user_id || $currentUser->is_admin;
    }

    /**
     * Determine whether the user can delete the company.
     *
     *
     * @return bool
     */
    public function delete(User $currentUser, Revision $revision)
    {
        return false;
    }
}
