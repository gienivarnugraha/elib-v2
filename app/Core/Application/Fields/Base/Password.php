<?php

namespace App\Core\Application\Fields\Base;

class Password extends Text
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'v-password';

    /**
     * Input type
     */
    public string $inputType = 'password';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->icon('bx-lock', 'prepend-inner');
    }
}
