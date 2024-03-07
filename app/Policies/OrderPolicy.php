<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
    public function view(User $currentUser, Order $order)
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
    public function update(User $currentUser, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the company.
     *
     *
     * @return bool
     */
    public function delete(User $currentUser, Order $order)
    {
        return false;
    }
}
