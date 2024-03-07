<?php

namespace App\Core\Application\Fields;

use App\Core\Traits\Authorizeable;
use App\Core\Traits\Metable;

class FieldElement
{
    use Authorizeable, Metable;

    public bool $showOnIndex = true;

    /**
     * @var bool|callable
     */
    public $applicableForIndex = true;

    public bool $showOnCreation = true;

    /**
     * @var bool|callable
     */
    public $applicableForCreation = true;

    public bool $showOnUpdate = true;

    /**
     * @var bool|callable
     */
    public $applicableForUpdate = true;

    public bool|string $excludeFromSettings = false;

    /**
     * Indicates whether to exclude the field from import
     */
    public bool $excludeFromImport = false;

    /**
     * Indicates whether the field should be included in sample data
     */
    public bool $excludeFromImportSample = false;

    /**
     * Indicates whether to exclude the field from export
     */
    public bool $excludeFromExport = false;

    /**
     * Set that the field by default should be hidden on index view
     */
    public function hideFromIndex(): static
    {
        $this->showOnIndex = false;

        return $this;
    }

    /**
     * The field is only for index and cannot be used on other views
     */
    public function strictlyForIndex(): static
    {
        $this->excludeFromSettings = true;
        $this->applicableForIndex = true;
        $this->applicableForCreation = false;
        $this->applicableForUpdate = false;

        return $this;
    }

    /**
     * Set that the field by default should be hidden on create view
     */
    public function hideWhenCreating(): static
    {
        $this->showOnCreation = false;

        return $this;
    }

    /**
     * The field is only for creation and cannot be used on other views
     */
    public function strictlyForCreation(): static
    {
        $this->applicableForCreation = true;
        $this->applicableForUpdate = false;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * Set that the field by default should be hidden on update view
     */
    public function hideWhenUpdating(): static
    {
        $this->showOnUpdate = false;

        return $this;
    }

    /**
     * The field is only for update and cannot be used on other views
     */
    public function strictlyForUpdate(): static
    {
        $this->applicableForCreation = false;
        $this->applicableForUpdate = true;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * The field is only for import and cannot be used on other views
     */
    public function strictlyForImport(): static
    {
        $this->applicableForCreation = false;
        $this->applicableForUpdate = false;
        $this->applicableForIndex = false;

        $this->excludeFromExport = true;
        $this->excludeFromSettings = true;
        $this->excludeFromImport = false;

        return $this;
    }

    /**
     * The field is only for forms and cannot be used on other views
     */
    public function strictlyForForms(): static
    {
        $this->applicableForCreation = true;
        $this->applicableForUpdate = true;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * The field is only usable on views different then forms
     */
    public function exceptOnForms(bool|callable $applicable = false): static
    {
        $this->applicableForUpdate = $applicable;
        $this->applicableForCreation = $applicable;
        $this->applicableForIndex = true;
        $this->excludeFromImport = true;
        $this->excludeFromSettings = true;

        return $this;
    }

    /**
     * Exclude the field from index
     */
    public function excludeFromIndex(bool|callable $applicable = false): static
    {
        $this->applicableForIndex = $applicable;

        return $this;
    }

    /**
     * The field is only for index and create and cannot be used on update
     */
    public function excludeFromUpdate(bool|callable $applicable = false): static
    {
        $this->applicableForUpdate = $applicable;

        return $this;
    }

    /**
     * The field is only for index and update and cannot be used on create
     */
    public function excludeFromCreate(bool|callable $applicable = false): static
    {
        $this->applicableForCreation = $applicable;

        return $this;
    }

    /**
     * Indicates that this field should be excluded from the settings
     */
    public function excludeFromSettings(string|bool $view = true): static
    {
        $this->excludeFromSettings = $view;

        return $this;
    }

    /**
     * Set that the field should be excluded from export
     */
    public function excludeFromExport(): static
    {
        $this->excludeFromExport = true;

        return $this;
    }

    /**
     * Set that the field should be excluded from import
     */
    public function excludeFromImport(): static
    {
        $this->excludeFromImport = true;

        return $this;
    }

    /**
     * Set that the field should should not be included in sample data
     */
    public function excludeFromImportSample(): static
    {
        $this->excludeFromImportSample = true;

        return $this;
    }

    /**
     * Indicates that the field by default should be hidden on all views.
     */
    public function hidden(): static
    {
        $this->hideWhenUpdating();
        $this->hideWhenCreating();
        $this->hideFromIndex();

        return $this;
    }

    /**
     * Determine if the field is applicable for creation view
     *
     * @return bool
     */
    public function isApplicableForCreation()
    {
        return with($this->applicableForCreation, function ($callback) {
            return $callback === true || (is_callable($callback) && call_user_func($callback));
        });
    }

    /**
     * Determine if the field is applicable for update view
     *
     * @return bool
     */
    public function isApplicableForUpdate()
    {
        return with($this->applicableForUpdate, function ($callback) {
            return $callback === true || (is_callable($callback) && call_user_func($callback));
        });
    }

    /**
     * Determine if the field is applicable for index view
     *
     * @return bool
     */
    public function isApplicableForIndex()
    {
        return with($this->applicableForIndex, function ($callback) {
            return $callback === true || (is_callable($callback) && call_user_func($callback));
        });
    }
}
