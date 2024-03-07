<?php

namespace App\Core\Actions;

use App\Core\Facades\Application;
use App\Core\Repository\BaseRepository;
use App\Core\Traits\Authorizeable;
use App\Http\Requests\ActionRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Action implements JsonSerializable
{
    use Authorizeable;

    /**
     * Indicates that the action will be hidden on the index view
     */
    public bool $hideOnIndex = false;

    /**
     * Indicates that the action will be hidden on the view/update view
     */
    public bool $hideOnUpdate = false;

    /**
     * Indicates that the action does not have confirmation dialog
     */
    public bool $withoutConfirmation = false;

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    abstract public function authorizedToRun(ActionRequest $request, $model);

    /**
     * Handle method that all actions must implement
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        return [];
    }

    /**
     * Action fields method
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Resolve action fields
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveFields()
    {
        return collect($this->fields())->filter->authorizedToSee()->values();
    }

    /**
     * Run action based on the request data
     *
     *
     * @return mixed
     */
    public function run(ActionRequest $request, BaseRepository $repository)
    {
        $ids = $request->input('ids');
        $fields = $request->resolveFields();

        /**
         * Find all models and exclude any models that are not authorized to be handled in this action
         */
        $models = $this->filterForExecution(
            $this->findModelsForExecution($ids, $repository),
            $request
        );

        /**
         * All models excluded? In this case, the user is probably not authorized to run the action
         */
        if ($models->count() === 0) {
            return static::error(__('user.not_authorized'));
        } elseif ($models->count() > config('innoclapps.actions.disable_notifications_when_records_are_more_then')) {
            Application::disableNotifications();
        }

        $response = $this->handle($models, $fields);

        if (Application::notificationsDisabled()) {
            Application::enableNotifications();
        }

        if (! is_null($response)) {
            return $response;
        }

        return static::success(__('actions.run_successfully'));
    }

    /**
     * Toasted success alert
     *
     * @param  string  $message The response message
     */
    public static function success(string $message): array
    {
        return ['success' => $message];
    }

    /**
     * Toasted info alert
     *
     * @param  string  $message The response message
     */
    public static function info(string $message): array
    {
        return ['info' => $message];
    }

    /**
     * Toasted success alert
     *
     * @param  string  $message The response message
     */
    public static function error(string $message): array
    {
        return ['error' => $message];
    }

    /**
     * Filter models for exeuction
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return \Illuminate\Support\Collection
     */
    public function filterForExecution($models, ActionRequest $request)
    {
        return $models->filter(fn ($model) => $this->authorizedToRun($request, $model));
    }

    /**
     * The action human readable name
     */
    public function name(): ?string
    {
        return Str::title(Str::snake(get_called_class(), ' '));
    }

    /**
     * Get the URI key for the card.
     */
    public function uriKey(): string
    {
        return Str::kebab(class_basename(get_called_class()));
    }

    /**
     * Message shown when performing the action
     */
    public function message(): string
    {
        return __('actions.confirmation_message');
    }

    /**
     * Set the action to not have confirmation dialog
     */
    public function withoutConfirmation(): static
    {
        $this->withoutConfirmation = true;

        return $this;
    }

    /**
     * Set the action to be available only on index view
     */
    public function onlyOnIndex(): static
    {
        $this->hideOnUpdate = true;
        $this->hideOnIndex = false;

        return $this;
    }

    /**
     * Set the action to be available only on update view
     */
    public function onlyOnUpdate(): static
    {
        $this->hideOnUpdate = false;
        $this->hideOnIndex = true;

        return $this;
    }

    /**
     * Return an open new tab response from the action.
     */
    public static function openInNewTab(string $url): array
    {
        return ['openInNewTab' => $url];
    }

    /**
     * Query the models for execution
     *
     * @param  array  $ids
     * @param  \App\Core\Repository\AppRepository  $repository
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findModelsForExecution($ids, $repository)
    {
        return $repository->findMany($ids);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name(),
            'message' => $this->message(),
            'destroyable' => $this instanceof DestroyableAction,
            'withoutConfirmation' => $this->withoutConfirmation,
            'fields' => $this->resolveFields(),
            'hideOnIndex' => $this->hideOnIndex,
            'hideOnUpdate' => $this->hideOnUpdate,
            'uriKey' => $this->uriKey(),
        ];
    }
}
