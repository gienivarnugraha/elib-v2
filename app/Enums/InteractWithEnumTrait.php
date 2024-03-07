<?php

namespace App\Enums;

trait InteractWithEnumTrait
{
    /**
     * Find enum by given name
     *
     *
     * @return static|null
     */
    public static function find(string $name): ?self
    {
        return array_values(array_filter(static::cases(), function ($status) use ($name) {
            return $status->name == $name;
        }))[0] ?? null;
    }

    /**
     * Get a random enum instance
     */
    public static function random(): self
    {
        return static::find(static::names()[array_rand(static::names())]);
    }

    /**
     * Get all the enum names
     */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    /**
     * Get all the enum names
     */
    public static function value(string $name): string
    {
        $case = static::tryFrom($name);

        return is_null($case) ? null : $case->value;
    }
}
