<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Optionable;

class Radio extends Optionable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'radio-field';

    /**
     * Indicates that the radio field will be inline
     */
    public function inline(): static
    {
        $this->withMeta(['inline' => true]);

        return $this;
    }
}
