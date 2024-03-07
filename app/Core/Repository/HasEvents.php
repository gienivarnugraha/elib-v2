<?php

namespace App\Core\Repository;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;

trait HasEvents
{
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [];

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string  $event
     * @param  \Closure|string  $callback
     * @return void
     */
    protected static function registerModelEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::model();

            static::$dispatcher->listen("repository.{$event}: {$name}", $callback);
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @param  bool  $halt
     * @return mixed
     */
    protected function fireModelEvent($event, $model, $halt = true)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if ($result === false) {
            return false;
        }

        return ! empty($result) ? $result : static::$dispatcher->{$method}(
            "repository.{$event}: ".static::model(),
            [$model, $this]
        );
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param  string  $event
     * @param  string  $method
     * @return mixed|null
     */
    protected function fireCustomModelEvent($event, $method)
    {
        if (! isset($this->dispatchesEvents[$event])) {
            return;
        }

        $result = static::$dispatcher->{$method}(new $this->dispatchesEvents[$event]($this));

        if (! is_null($result)) {
            return $result;
        }
    }

    /**
     * Filter the model event results.
     *
     * @param  mixed  $result
     * @return mixed
     */
    protected function filterModelEventResults($result)
    {
        if (is_array($result)) {
            $result = array_filter($result, function ($response) {
                return ! is_null($response);
            });
        }

        return $result;
    }

    /**
     * Register an updating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function updating($callback)
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register an updated model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function updated($callback)
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a creating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function creating($callback)
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function created($callback)
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deleting($callback)
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function deleted($callback)
    {
        static::registerModelEvent('deleted', $callback);
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Execute a callback without firing any model events for any model type.
     *
     * @return mixed
     */
    public static function withoutEvents(callable $callback)
    {
        $dispatcher = static::getEventDispatcher();

        if ($dispatcher) {
            static::setEventDispatcher(new NullDispatcher($dispatcher));
        }

        try {
            return $callback();
        } finally {
            if ($dispatcher) {
                static::setEventDispatcher($dispatcher);
            }
        }
    }
}
