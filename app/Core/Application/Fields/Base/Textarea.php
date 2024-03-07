<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;

class Textarea extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'textarea-field';

    /**
     * Textarea rows attribute
     *
     * @param  mixed  $rows
     */
    public function rows($rows): static
    {
        $this->withMeta(['attributes' => ['rows' => $rows]]);

        return $this;
    }
}
