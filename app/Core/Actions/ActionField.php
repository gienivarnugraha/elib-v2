<?php

namespace App\Core\Actions;

class ActionFields
{
    /**
     * Create new instance of action request fields
     */
    public function __construct(protected array $fields)
    {
    }

    /**
     * Get field
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }
}
