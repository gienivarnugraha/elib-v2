<?php

namespace App\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Core\Resources\Resource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Table\Table;
use App\Core\Application\Fields\Base\Text;
use App\Core\Application\Fields\Base\Email;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\UserRepository;
use App\Core\Application\Fields\Base\Password;
use App\Core\Application\Menu\Item as MenuItem;
use App\Core\Application\Fields\Relation\HasOne;

class User extends Resource implements Resourceful, Tableable
{
    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * Get the underlying resource repository
     *
     * @return \App\Core\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(UserRepository::class);
    }

    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     */
    public function table($repository, Request $request): Table
    {
        return new UserTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return UserResource::class;
    }

    /**
     * Get the resource rules available for create and update
     *
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => ['string', 'max:191'],
            'password' => [$request->route('resourceId') ? 'nullable' : 'required', 'confirmed', 'min:6'],
            'email' => ['email', 'max:191',], // 'unique:users,email'
            'avatar' => ['nullable', 'mimes:jpg,bmp,png,jpeg']
            // 'time_format' => ['required', 'string', Rule::in(config('core.time_formats'))],
            // 'date_format' => ['required', 'string', Rule::in(config('core.date_formats'))],
        ];
    }

    /**
     * Set the resource rules available only for create
     *
     *
     * @return array
     */
    public function createRules(Request $request)
    {
        return [
            'name' => ['required'],
            'email' => ['required'],
        ];
    }

    /**
     * Set the resource rules available only for update
     *
     *
     * @return array
     */
    public function updateRules(Request $request)
    {
        return [
            'name' => ['nullable'],
            'email' => ['nullable'],
        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make('users', '/users', 'bx-user')
                ->position(15)
                ->badge(10)
                ->inQuickCreate(),
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(): array
    {
        return [
            // (new Actions\UserDelete)->canSeeWhen('is-super-admin'),
        ];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        $isCurrentUser = $request->user()->id === Auth::id();

        return [
            Email::make('email', 'Email')
                ->icon('bx-envelope', 'prepend-inner')
                ->inputType('email'),

            Text::make('name', 'Name')
                ->icon('bx-user', 'prepend-inner'),

            HasOne::make('settings', 'date_format', 'Date Formats')
                ->icon('bx-calendar', 'prepend-inner')
                ->options(config('core.date_formats'))
                ->makeLabelAsValue(),

            HasOne::make('settings', 'time_format', 'Time Formats')
                ->icon('bx-time', 'prepend-inner')
                ->options(config('core.time_formats'))
                ->makeLabelAsValue(),

            Password::make('password', 'Password')
                ->icon('bx-lock', 'prepend-inner')
                ->strictlyForUpdate($isCurrentUser),

            Password::make('password_confirmation', 'Password Confirmation')
                ->icon('bx-lock', 'prepend-inner')
                ->strictlyForUpdate($isCurrentUser),
        ];
    }
}
