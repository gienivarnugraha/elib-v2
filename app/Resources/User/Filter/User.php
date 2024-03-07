<?php

namespace App\Resources\User\Filter;

use App\Contracts\Repositories\UserRepository;
use App\Core\Application\Filters\Fields\Select;
use Illuminate\Support\Facades\Auth;

class User extends Select
{
    /**
     * Initialize User class
     *
     * @param  string|null  $label
     * @param  string|null  $field
     */
    public function __construct($label = null, $field = null)
    {
        parent::__construct($field ?? 'user_id', $label ?? __('user.user'));

        $this->setOperator('equal');

        $this->withNullOperators()
            ->valueKey('id')
            ->labelKey('name')
            ->options(
                resolve(UserRepository::class)
                    ->orderBy('name')
                    ->all()
                    ->map(function ($user) {
                        $isLoggedInUser = $user->is(Auth::user());

                        return [
                            'id' => $user->id,
                            'name' => !$isLoggedInUser ? $user->name : 'me', //__('filters.me'),
                        ];
                    })
            );
    }
}
