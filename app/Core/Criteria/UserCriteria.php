<?php

namespace App\Core\Criteria;

use App\Contracts\Repositories\UserRepository;
use App\Core\Contracts\Repository\CriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserCriteria implements CriteriaInterface
{
    /**
     * Initialze QueriesByUserCriteria class
     *
     * @param  \App\Models\User|int|null  $user
     * @param  string  $columnName
     */
    public function __construct(protected $user = null, protected $columnName = 'user_id')
    {
    }

    /**
     * Apply criteria in query repository
     *
     * @param  \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return static::applyQuery($model, $this->user, $this->columnName);
    }

    /**
     * Apply the query for the criteria
     *
     * @param  \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @param  \App\Models\User|int|null  $user
     * @param  string  $columnName
     * @return \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    public static function applyQuery($model, $user = null, $columnName = 'user_id')
    {
        return $model->where($columnName, static::determineUser($user)->getKey());
    }

    /**
     * Determine the user
     *
     * @param  mixed  $user
     * @return \App\Models\User
     */
    protected static function determineUser($user)
    {
        if (is_null($user)) {
            $user = Auth::user();
        } elseif ($user instanceof User) {
            $user = $user;
        } else {
            $user = resolve(UserRepository::class)->find($user);
        }

        return $user;
    }
}
