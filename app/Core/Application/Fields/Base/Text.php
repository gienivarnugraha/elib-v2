<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;

class Text extends Field
{
    /**
     * This field support input group
     */
    public bool $supportsInputGroup = true;

    /**
     * Input type
     */
    public string $inputType = 'text';

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'v-input';

    /**
     * Specify type attribute for the text field
     *
     * @param  string  $type
     * @return static
     */
    public function inputType($type)
    {
        $this->inputType = $type;

        return $this;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'inputType' => $this->inputType,
        ]);
    }
}
