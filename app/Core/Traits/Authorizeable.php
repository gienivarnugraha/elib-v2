<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.0.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2022 KONKORD DIGITAL
 */

namespace App\Core\Traits;

use Closure;
use Illuminate\Support\Facades\Auth;

trait Authorizeable
{
    /**
     * Hold the canSee method closure
     *
     * @var \Closure
     */
    public $canSeeClosure = null;

    /**
     * Hold the canSeeWhen method data
     *
     * @var array
     */
    public $canSeeWhenArrayClosure = null;

    /**
     * canSee method to perform checks on specific class
     *
     *
     * @return static
     */
    public function canSee(Closure $callable)
    {
        $this->canSeeClosure = $callable;

        return $this;
    }

    /**
     * canSeeWhen, the same signature like user()->can()
     *
     * @param  string  $ability the ability
     * @param  array  $arguments
     * @return static
     */
    public function canSeeWhen($ability, $arguments = [])
    {
        $this->canSeeWhenArrayClosure = [$ability, $arguments];

        return $this;
    }

    /**
     * Authorize or fail
     *
     * @param  string  $message The failure message
     * @return mixed
     */
    public function authorizeOrFail($message = null)
    {
        if (! $this->authorizedToSee()) {
            abort(403, $message ?? 'You are not authorized to perform this action.');
        }

        return $this;
    }

    /**
     * Check whether the user can see a specific item
     *
     * @return bool
     */
    public function authorizedToSee()
    {
        if (! $this->hasAuthorization()) {
            return true;
        }

        if ($this->canSeeWhenArrayClosure) {
            if (! Auth::user()) {
                return false;
            }

            return Auth::user()->can(...$this->canSeeWhenArrayClosure);
        }

        return call_user_func($this->canSeeClosure, request());
    }

    /**
     * Check whether on specific class/item is added authorization via canSee and canSeeWhen
     *
     * @return bool
     */
    public function hasAuthorization()
    {
        return $this->canSeeClosure || $this->canSeeWhenArrayClosure;
    }
}
