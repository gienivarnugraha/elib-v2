<?php

namespace App\Core;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ModelList
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected static $models;

    /**
     * List the application available models
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getModels()
    {
        if (! static::$models) {
            static::$models = collect((new Finder)->in([
                app_path('Models'),
                app_path('Core/Models'),
            ])->files()->name('*.php'))
                ->map(fn ($model) => app()->getNamespace().str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($model->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
                ));
        }

        return static::$models;
    }
}
