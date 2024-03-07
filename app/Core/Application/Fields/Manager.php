<?php

namespace App\Core\Application\Fields;

class Manager
{
    /**
     * Hold all groups and fields
     */
    protected array $fields = [];

    /**
     * Loaded fields cache
     */
    protected static array $loaded = [];

    /**
     * Register fields with group
     *
     * @param  string  $group
     * @param  mixed  $provider
     * @return static
     */
    public function group($group, $provider)
    {
        static::flushCache();

        if (! isset($this->fields[$group])) {
            $this->fields[$group] = [];
        }

        $this->fields[$group][] = $provider;

        return $this;
    }

    /**
     * Add fields to the given group
     *
     * @param  string  $group
     * @param  mixed  $provider
     * @return static
     */
    public function add($group, $provider)
    {
        return $this->group($group, $provider);
    }

    /**
     * Replace the group fields with the given fields
     *
     * @param  string  $group
     * @param  mixed  $provider
     * @return static
     */
    public function replace($group, $provider)
    {
        $this->fields[$group] = [];

        return $this->group($group, $provider);
    }

    /**
     * Resolves fields for the given group and view
     *
     * @param  string  $view create|update
     * @return \App\Core\Fields\Collection
     */
    public function resolve(string $group, string $view)
    {
        return $this->{'resolve'.ucfirst($view).'Fields'}($group);
    }

    /**
     * Resolves fields for the given group and view for display
     *
     * @param  string  $view create|update
     * @return \App\Core\Fields\Collection
     */
    public function resolveForDisplay(string $group, string $view)
    {
        return $this->{'resolve'.ucfirst($view).'FieldsForDisplay'}($group);
    }

    /**
     * Resolve the create fields for display
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveCreateFieldsForDisplay(string $group)
    {
        return $this->resolveCreateFields($group)
            ->reject(fn ($field) => $field->showOnCreation === false)
            ->values();
    }

    /**
     * Resolve the update fields for display
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveUpdateFieldsForDisplay(string $group)
    {
        return $this->resolveUpdateFields($group)
            ->reject(fn ($field) => $field->showOnUpdate === false)
            ->values();
    }

    /**
     * Resolve the create fields for the given group
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveCreateFields(string $group)
    {
        return $this->resolveAndAuthorize($group, 'create')
            ->filter->isApplicableForCreation()->values();
    }

    /**
     * Resolve and authorize the fields for the given group
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveAndAuthorize(string $group, string $view = null)
    {
        return $this->inGroup($group, $view)->filter->authorizedToSee();
    }

    /**
     * Resolve the update fields for the given group
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveUpdateFields(string $group)
    {
        return $this->resolveAndAuthorize($group, 'update')
            ->filter->isApplicableForUpdate()->values();
    }

    /**
     * Get all fields in specific group
     *
     *
     * @return \App\Core\Fields\Collection
     */
    public function inGroup(string $group, string $view = null)
    {
        if (isset(static::$loaded[$cacheKey = (string) $group.$view])) {
            return static::$loaded[$cacheKey];
        }

        $callback = function ($field, $index) {
            /**
             * Add field order if there is no customized order
             * This helps to sort them properly by default the way they are defined
             */
            $field->order ??= $index + 1;

            return $field;
        };

        return static::$loaded[$cacheKey] = $this->load($group)->map($callback)
            ->sortBy('order')
            ->values();
    }

    /**
     * Purge the customized fields cache
     *
     * @return static
     */
    public static function flushCache()
    {
        static::$loaded = [];
    }

    /**
     * Loaded the provided group fields
     *
     * @param  string  $group
     * @return \App\Core\Fields\FieldsCollection
     */
    protected function load($group)
    {
        $fields = new FieldsCollection();

        foreach ($this->fields[$group] ?? [] as $provider) {
            if ($provider instanceof Field) {
                $provider = [$provider];
            }

            if (is_array($provider)) {
                $fields = $fields->merge($provider);
            } elseif (is_callable($provider)) { // callable, closure, __invoke
                $fields = $fields->merge(call_user_func($provider));
            }
        }

        return $fields;
    }
}
