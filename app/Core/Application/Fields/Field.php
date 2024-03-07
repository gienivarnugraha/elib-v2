<?php

namespace App\Core\Application\Fields;

use App\Core\Application\Fields\Base\Checkbox;
use App\Core\Application\Fields\Base\MultiSelect;
use App\Core\Application\Fields\Relation\HasMany;
use App\Core\Application\Fields\Traits\DisplaysOnIndex;
use App\Core\Application\Fields\Traits\HasIcon;
use App\Core\Application\Fields\Traits\HasModelEvents;
use App\Core\Application\Fields\Traits\HasRules;
use App\Core\Application\Fields\Traits\ResolvesValue;
use App\Core\Facades\Application;
use App\Core\Http\Request\ResourceRequest;
use App\Core\Traits\Makeable;
use Closure;
use Illuminate\Support\Str;
use JsonSerializable;

class Field extends FieldElement implements JsonSerializable
{
    use DisplaysOnIndex,
        HasIcon,
        HasModelEvents,
        HasRules,
        Makeable,
        ResolvesValue;

    /**
     * Default value
     *
     * @var mixed
     */
    public $value;

    /**
     * Field attribute / column name
     *
     * @var string
     */
    public $attribute;

    /**
     * Custom field request attribute
     *
     * @var string|null
     */
    public $requestAttribute;

    /**
     * Field label
     *
     * @var string
     */
    public $label;

    /**
     * Help text
     */
    public ?string $helpText = null;

    /**
     * Indicates how the help text is displayed, as icon or text
     */
    public string $helpTextDisplay = 'icon';

    /**
     * Whether the field is collapsed. E.q. view all fields
     */
    public bool $collapsed = false;

    /**
     * Emit change event when field value changed
     */
    public ?string $emitChangeEvent = null;

    /**
     * Is field read only
     *
     * @var bool|callable
     */
    public $readOnly = false;

    /**
     * Is the field hidden
     */
    public bool $displayNone = false;

    /**
     * Indicates whether the column value should be always included in the
     * JSON Resource for front-end
     */
    public bool $alwaysInJsonResource = false;

    /**
     * Field order
     */
    public ?int $order;

    /**
     * Field order
     */
    public ?string $placeholder = null;

    /**
     * Field column class
     */
    public string|Closure|null $colClass = null;

    /**
     * Field toggle indicator
     */
    public bool $toggleable = false;

    /**
     * Custom attributes provider for create/update
     *
     * @var callable|null
     */
    public $saveUsing;

    /**
     * Field component
     *
     * @var null|string
     */
    public $component = null;

    /**
     * Initialize new Field instance class
     *
     * @param  string  $attribute field attribute
     * @param  string|null  $label field label
     */
    public function __construct($attribute, $label = null)
    {
        $this->attribute = $attribute;

        $this->label = $label;

        $this->boot();
    }

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
    }

    public function setOrder(int $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set field attribute
     *
     * @param  string  $attribute
     */
    public function withDefaultPlaceholder($placeholder = null): static
    {
        $this->placeholder = $placeholder ??= 'Enter ' . $this->label;

        return $this;
    }

    /**
     * Set field attribute
     *
     * @param  string  $attribute
     */
    public function attribute($attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set field label
     *
     * @param  string  $label
     */
    public function label($label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * get field label
     *
     * @param  string  $label
     */
    public function getLabel(): string
    {
        return $this->label ?? Str::of($this->attribute)->snake()->replace('_', ' ')->title();
    }

    /**
     * Set the field column class
     *
     * @param  string  $class
     */
    public function colClass(string|Closure|null $class): static
    {
        $this->colClass = $class;

        return $this;
    }

    /**
     * Mark the field as toggleable
     */
    public function toggleable(bool $value = true): static
    {
        $this->toggleable = $value;

        return $this;
    }

    /**
     * Get the field column class
     *
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     * @return string|null
     */
    public function getColClass(ResourceRequest $request)
    {
        return with($this->colClass, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set default value on creation forms
     *
     * @param  mixed  $value
     */
    public function withDefaultValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the field default value
     *
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     */
    public function defaultValue(ResourceRequest $request): mixed
    {
        return with($this->value, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set collapsible field
     */
    public function collapsed(bool $bool = true): static
    {
        $this->collapsed = $bool;

        return $this;
    }

    /**
     * Set field help text
     */
    public function help(?string $text): static
    {
        $this->helpText = $text;

        return $this;
    }

    /**
     * Set the field display of the help text
     *
     * @param  string  $display icon|text
     */
    public function helpDisplay(string $display): static
    {
        $this->helpTextDisplay = $display;

        return $this;
    }

    /**
     * Add read only statement
     */
    public function readOnly(bool|callable $value): static
    {
        $this->readOnly = $value;

        return $this;
    }

    /**
     * Determine whether the field is read only
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return with($this->readOnly, function ($callback) {
            return $callback === true || (is_callable($callback) && call_user_func($callback));
        });
    }

    /**
     * Hides the field from the document
     */
    public function displayNone(bool $value = true): static
    {
        $this->displayNone = $value;

        return $this;
    }

    /**
     * Get the component name for the field.
     */
    public function component($val): static
    {
        $this->component = $val;

        return $this;
    }

    /**
     * Indicates that the field value should be included in the JSON resource
     * when the user is not authorized to view the model/record
     */
    public function showValueWhenUnauthorizedToView(): static
    {
        $this->alwaysInJsonResource = true;

        return $this;
    }

    /**
     * Indicates whether to emit change event when value is changed
     *
     * @param  string  $eventName
     */
    public function emitChangeEvent($eventName = null): static
    {
        $this->emitChangeEvent = $eventName ?? 'field-' . $this->attribute . '-value-changed';

        return $this;
    }

    /**
     * Get the field request attribute
     *
     * @return string
     */
    public function requestAttribute()
    {
        return $this->requestAttribute ?? $this->attribute;
    }

    /**
     * Create the field attributes for storage for the given request
     *
     * @param  string  $requestAttribute
     */
    public function storageAttributes(ResourceRequest $request, $requestAttribute): array|callable
    {
        if (is_callable($this->saveUsing)) {
            return call_user_func_array($this->saveUsing, [
                $request,
                $requestAttribute,
                $this->attributeFromRequest($request, $requestAttribute),
                $this,
            ]);
        }

        return [
            $this->attribute => $this->attributeFromRequest($request, $requestAttribute),
        ];
    }

    /**
     * Get the field value for the given request
     *
     * @param  string  $requestAttribute
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute): mixed
    {
        return $request->exists($requestAttribute) ? $request[$requestAttribute] : null;
    }

    /**
     * Add custom attributes provider callback when creating/updating
     */
    public function saveUsing(callable $callable): static
    {
        $this->saveUsing = $callable;

        return $this;
    }

    /**
     * Check whether the field is optionable
     */
    public function isOptionable(): bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the field is multi optionable
     */
    public function isMultiOptionable(): bool
    {
        return $this instanceof HasMany || $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Serialize for front end
     */
    public function jsonSerialize(): array
    {
        // Determine if the field is required and then clear import status when mapping
        $isRequired = $this->isRequired(resolve(ResourceRequest::class));

        if (Application::isImportMapping()) {
            Application::setImportStatus(false);
        }

        return array_merge([
            'component' => $this->component,
            'attribute' => $this->attribute,
            'placeholder' => $this->placeholder,
            'label' => $this->getLabel(),
            'helpText' => $this->helpText,
            'helpTextDisplay' => $this->helpTextDisplay,
            'readonly' => $this->isReadOnly(),
            'collapsed' => $this->collapsed,
            'icon' => $this->icon,
            'iconPlacement' => $this->iconPlacement,
            // 'show' => $this->showOnIndex || $this->showOnCreation || $this->showOnUpdate,
            // 'showOnIndex'           => $this->showOnIndex,
            // 'showOnCreation'        => $this->showOnCreation,
            // 'showOnUpdate'          => $this->showOnUpdate,
            // 'applicableForIndex'    => $this->isApplicableForIndex(),
            // 'applicableForCreation' => $this->isApplicableForCreation(),
            // 'applicableForUpdate'   => $this->isApplicableForUpdate(),
            'toggleable' => $this->toggleable,
            'displayNone' => $this->displayNone,
            'emitChangeEvent' => $this->emitChangeEvent,
            'colClass' => $this->getColClass(resolve(ResourceRequest::class)),
            'value' => $this->defaultValue(resolve(ResourceRequest::class)),
            'isRequired' => $isRequired,
        ], $this->meta());
    }
}
