<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Relation\BelongsTo;
use App\Core\Facades\Application;
use Illuminate\Support\Facades\Auth;

class User extends BelongsTo
{
    public ?string $notification = null;

    public ?string $trackChangeDateColumn = null;

    /**
     * The assigneer
     *
     * @var \App\Models\User
     */
    public static $assigneer;

    /**
     * Creat new User instance field
     *
     * @param  string  $label Custom label
     * @param  string  $relationName
     * @param  string|null  $attribute
     */
    public function __construct($label = null, $relationName = null, $attribute = null)
    {
        parent::__construct(
            $relationName ?: 'user',
            Application::getUserRepository(),
            $label ?: __('user.user'),
            $attribute
        );

        // Auth check for console usage
        $this->withDefaultValue(Auth::check() ? $this->createOption(Auth::user()) : null)
            ->importRules($this->getUserImportRules());
    }

    /**
     * Provides the User instance options
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveOptions()
    {
        return $this->repository->columns([$this->valueKey, $this->labelKey, 'avatar'])
            ->orderBy($this->labelKey)
            ->get()
            ->map(fn ($user) => $this->createOption($user));
    }

    /**
     * Set the user that perform the assignee
     *
     * @param  \App\Models\User  $user
     */
    public static function setAssigneer($user)
    {
        static::$assigneer = $user;
    }

    /**
     * Send a notification when the user changes
     *
     * @param  string  $notification
     * @return static
     */
    public function notification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Set date column to track the date when the user was changed.
     *
     * @param  string  $dateColumn
     * @return static
     */
    public function trackChangeDate($dateColumn)
    {
        $this->trackChangeDateColumn = $dateColumn;

        return $this;
    }

    /**
     * Handle the resource record "creating" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
        $foreignKey = $model->{$this->belongsToRelation}()->getForeignKeyName();

        if ($this->trackChangeDateColumn && ! empty($model->{$foreignKey})) {
            $model->{$this->trackChangeDateColumn} = now();
        }
    }

    /**
     * Handle the resource record "created" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
        $this->handleUserChangedNotification($model);
    }

    /**
     * Handle the resource record "updating" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordUpdating($model)
    {
        if ($this->trackChangeDateColumn) {
            $date = false;
            $foreignKey = $model->{$this->belongsToRelation}()->getForeignKeyName();

            if (empty($model->{$foreignKey})) {
                $date = null;
            } elseif ($model->getOriginal($foreignKey) !== $model->{$foreignKey}) {
                $date = now();
            }

            if ($date !== false) {
                $model->{$this->trackChangeDateColumn} = $date;
            }
        }
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
        $this->handleUserChangedNotification($model);
    }

    /**
     * Handle the user changed notification
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function handleUserChangedNotification($model)
    {
        if (! $this->notification) {
            return;
        }

        $foreignKey = $model->{$this->belongsToRelation}()->getForeignKeyName();

        if (
            // Asssigned user not found
            ! $model->{$foreignKey} ||
            // Is update and the assigned is the same like it was before
            (! $model->wasRecentlyCreated
              && $model->getOriginal($foreignKey) === $model->{$foreignKey}) ||
            // The assigned user is the same as the logged in user
            $model->{$this->belongsToRelation} && $model->{$this->belongsToRelation}->is(Auth::user())
        ) {
            return;
        }

        $assigneer = static::$assigneer ?? Auth::user();

        // We will check if there an assigneer, if not, we won't send the notification
        // as well if the assigneer is the same like the actual user from the field
        if (
            $assigneer &&
            $model->{$this->belongsToRelation} &&
            $assigneer->isNot($model->{$this->belongsToRelation})
        ) {
            tap($this->notification, function ($notification) use ($model, $assigneer) {
                $model->{$this->belongsToRelation}->notify(
                    new $notification($model, $assigneer)
                );
            });
        }
    }

    /**
     * Resolve the field value for import
     *
     * @param  string|null  $value
     * @param  array  $row
     * @param  array  $original
     * @return array
     */
    public function resolveForImport($value, $row, $original)
    {
        if (is_string($value)) {
            // The user/owner cannot be created because requires
            // more complex information like password etc...
            // In this case, the user must be explicitly provided with name or id
            // When user not required and no user is found, by default uses the logged in user
            $value = $this->optionByLabel($value)[$this->valueKey] ?? $this->value[$this->valueKey] ?? Auth::id();
        }

        return [$this->attribute => $value];
    }

    /**
     * Create option for the front-end
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function createOption($user)
    {
        return [
            $this->valueKey => $user->{$this->valueKey},
            $this->labelKey => $user->{$this->labelKey},
            'avatar_url' => $user->avatar_url,
        ];
    }

    /**
     * Get the user import rules
     *
     * @return array
     */
    protected function getUserImportRules()
    {
        return [function ($attribute, $value, $fail) {
            if (is_null($value)) {
                return;
            }

            if (! $this->getCachedOptionsCollection()->filter(function ($user) use ($value) {
                return $user[$this->valueKey] == $value || $user[$this->labelKey] == $value;
            })->count() > 0) {
                $fail(__('validation.import.user.invalid', ['attribute' => $this->label]));
            }
        }];
    }
}
