<?php

namespace App\Core;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    /**
     * List files in a given directory which as subclass of a given class name
     *
     * @param  string  $className
     * @param  string  $directory
     * @return array
     */
    public static function listClassFilesOfSubclass($className, $directory)
    {
        $namespace = app()->getNamespace();

        $classes = [];

        foreach ((new Finder)->in($directory)->files() as $class) {
            $class = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($class->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($class, $className) &&
                ! (new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
