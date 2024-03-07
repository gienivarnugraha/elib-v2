<?php

namespace App\Core\Application\Fields\Traits;

use App\Core\Facades\Application;
use App\Core\Http\Request\ResourceRequest;
use Illuminate\Support\Arr;

trait HasRules
{
    /**
     * Validation rules
     */
    public array $rules = [];

    /**
     * Validation creation rules
     */
    public array $creationRules = [];

    /**
     * Validation import rules
     */
    public array $importRules = [];

    /**
     * Validation update rules
     */
    public array $updateRules = [];

    /**
     * Custom validation error messages
     */
    public array $validationMessages = [];

    /**
     * Prepare for validation callback
     *
     * @var callable|null
     */
    public $validationCallback;

    /**
     * Custom callback used to determine if the field is required.
     *
     * @var \Closure|bool
     */
    public $isRequiredCallback;

    /**
     * Set the callback used to determine if the field is required.
     *
     * Useful when you have complex required validation requirements like filled, sometimes etc..,
     * you can manually mark the field as required by passing a boolean when defining the field.
     *
     * This method will only add a "required" indicator to the UI field.
     * You must still define the related requirement rules() that should apply during validation.
     *
     * @param  \Closure|bool  $callback
     */
    public function required($callback = true): static
    {
        $this->isRequiredCallback = $callback;

        return $this;
    }

    /**
     * Check whether the field is required based on the rules
     *
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     * @return bool
     */
    public function isRequired(ResourceRequest $request)
    {
        return with($this->isRequiredCallback, function ($callback) use ($request) {
            if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
                return true;
            }

            if (! empty($this->attribute) && is_null($callback)) {
                if ($request->isCreateRequest()) {
                    $rules = $this->getCreationRules()[$this->requestAttribute()];
                } elseif ($request->isUpdateRequest()) {
                    $rules = $this->getUpdateRules()[$this->requestAttribute()];
                } elseif (Application::isImportMapping() || Application::isImportInProgress()) {
                    $rules = $this->getImportRules()[$this->requestAttribute()];
                } else {
                    $rules = $this->getRules()[$this->requestAttribute()];
                }

                return in_array('required', $rules);
            }

            return false;
        });
    }

    /**
     * Provide a callable to prepare the field for validation
     *
     * @param  callable  $callable
     */
    public function prepareForValidation($callable): static
    {
        $this->validationCallback = $callable;

        return $this;
    }

    /**
     * Set field validation rules for all requests
     *
     * @param  string|array  $rules
     */
    public function rules($rules): static
    {
        $this->rules = array_merge(
            $this->rules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules that are only applied on create request
     *
     * @param  string|array  $rules
     */
    public function creationRules($rules): static
    {
        $this->creationRules = array_merge(
            $this->creationRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules for import
     *
     * @param  string|array  $rules
     */
    public function importRules($rules): static
    {
        $this->importRules = array_merge(
            $this->importRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for import
     */
    public function getImportRules(): array
    {
        $rules = [
            $this->requestAttribute() => $this->importRules,
        ];

        // We will remove the array rule in case found
        // because the import can handle arrays via coma separated
        return collect(array_merge_recursive(
            $this->getCreationRules(),
            $rules
        ))->reject(fn ($rule) => $rule === 'array')->all();
    }

    /**
     * Set field validation rules that are only applied on update request
     *
     * @param  string|array  $rules
     */
    public function updateRules($rules): static
    {
        $this->updateRules = array_merge(
            $this->updateRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for the request
     */
    public function getRules(): array
    {
        return $this->createRulesArray($this->rules);
    }

    /**
     * Get the field validation rules for create request
     */
    public function getCreationRules(): array
    {
        $rules = $this->createRulesArray($this->creationRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Get the field validation rules for update request
     */
    public function getUpdateRules(): array
    {
        $rules = $this->createRulesArray($this->updateRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Create rules ready array
     *
     * @param  array  $rules
     */
    protected function createRulesArray($rules): array
    {
        // If the array is not list, probably the user added array validation
        // rules e.q. '*.key' => 'required', in this case, we will make sure to include them
        if (! array_is_list($rules)) {
            $groups = collect($rules)->mapToGroups(function ($rules, $wildcard) {
                // If the $wildcard is integer, this means that it's a rule for the main field attribute
                // e.q. ['array', '*.key' => 'required']
                return [is_int($wildcard) ? 'attribute' : 'wildcard' => [$wildcard => $rules]];
            })->all();

            return array_merge(
                [$this->requestAttribute() => $groups['attribute']->flatten()->all()],
                $groups['wildcard']->mapWithKeys(function ($rules) {
                    $wildcard = array_key_first($rules);

                    return [$this->requestAttribute().'.'.$wildcard => Arr::wrap($rules[$wildcard])];
                })->all()
            );
        }

        return [
            $this->requestAttribute() => $rules,
        ];
    }

    /**
     * Set field custom validation error messages
     */
    public function validationMessages(array $messages): static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get the field validation messages
     */
    public function prepareValidationMessages(): array
    {
        return collect($this->validationMessages)->mapWithKeys(function ($message, $rule) {
            return [$this->requestAttribute().'.'.$rule => $message];
        })->all();
    }
}
