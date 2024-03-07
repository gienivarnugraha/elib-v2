<?php

namespace App\Core\Traits;

use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use ReflectionMethod;

trait ProvidesModelAuthorizations
{
    /**
     * Get all defined authorizations for the model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $without Exclude abilities from authorization
     * @return array|null
     */
    public function getAuthorizations($model, $without = [])
    {
        if ($policy = policy($model)) {
            return collect((new ReflectionClass($policy))
                ->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn ($method) => in_array($method->name, $without))
                ->mapWithKeys(fn ($method) => [$method->name => Gate::allows($method->name, $model)])
                ->all();
        }
    }
}
