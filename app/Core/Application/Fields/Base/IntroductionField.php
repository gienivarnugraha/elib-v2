<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use Exception;

class IntroductionField extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'introduction-field';

    /**
     * Field title
     *
     * @var string
     */
    public $title;

    /**
     * Field message
     *
     * @var string|null
     */
    public $message;

    /**
     * Initialize new IntroductionField
     *
     * @param  string  $title
     * @param  string|null  $message
     */
    public function __construct($title, $message = null)
    {
        $this->title = $title;
        $this->message = $message;

        $this->excludeFromImport();
        $this->excludeFromExport();
        $this->excludeFromSettings();
        $this->excludeFromIndex();
    }

    /**
     * Set field title
     *
     * @param  string  $title
     * @return static
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set field message
     *
     * @param  string  $message
     * @return static
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Add custom value resolver
     *
     *
     * @return static
     */
    public function resolveUsing(callable $resolveCallback)
    {
        throw new Exception(__CLASS__.' cannot have custom resolve callback');
    }

    /**
     * Add custom display resolver
     *
     *
     * @return static
     */
    public function displayUsing(callable $displayCallback)
    {
        throw new Exception(__CLASS__.' cannot have custom display callback');
    }

    /**
     * Add custom import value resolver
     *
     *
     * @return static
     */
    public function importUsing(callable $importCallback)
    {
        throw new Exception(__CLASS__.' cannot be used in imports');
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'title' => $this->title,
            'message' => $this->message,
        ]);
    }
}
