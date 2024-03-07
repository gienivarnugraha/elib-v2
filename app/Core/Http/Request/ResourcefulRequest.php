<?php

namespace App\Core\Http\Request;

use App\Core\Application\Fields\FieldsCollection;
use App\Core\Contracts\Resources\Resourceful;
use Illuminate\Support\Str;

class ResourcefulRequest extends ResourceRequest
{
    /**
     * Get the class of the resource being requested.
     *
     * @return mixed
     */
    public function resource()
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Resourceful, 404);
        });
    }

    /**
     * Get the resource authorized fields for the request
     *
     * @return \App\Core\Application\Fields\FieldsCollection
     */
    public function authorizedFields()
    {
        if (! $this->isSaving()) {
            return new FieldsCollection;
        }

        return $this->fields()->filter(function ($field) {
            return ! $field->isReadOnly();
        });
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->setAuthorizedAttributes();

        $this->runValidationCallbacks($this->getValidatorInstance());
    }

    /**
     * Run the fields validation callbacks
     *
     * @return static
     */
    public function runValidationCallbacks($validator)
    {
        $original = $this->all();

        return with([], function ($data) use ($validator, $original) {
            foreach ($this->fieldsForValidationCallback() as $field) {
                $data[$field->requestAttribute()] = call_user_func_array(
                    $field->validationCallback,
                    [$this->{$field->requestAttribute()}, $this, $validator, $original]
                );
            }

            return $this->merge($data);
        });
    }

    /**
     * Get the fields applicable for validation callback
     *
     * @return \App\Core\Application\Fields\FieldsCollection
     */
    protected function fieldsForValidationCallback()
    {
        return $this->authorizedFields()->reject(function ($field) {
            return is_null($field->validationCallback) || $this->missing($field->requestAttribute());
        });
    }

    /**
     * Set the authorized attributes for the request
     *
     * @return void
     */
    protected function setAuthorizedAttributes()
    {
        // We will get all available fields for the current
        // request and based on the fields authorizations we will set
        // the authorized attributes, useful for example, field is not authorized to be seen
        // but it's removed from the fields method and in this case, if we don't check this here
        // this attribute will be automatically allowed as it does not exists in the authorized fields section
        // for this reason, we check this from all the available fields
        $fields = $this->allFields();

        $this->replace(collect($this->all())->filter(function ($value, $attribute) use ($fields) {
            return with($fields->findByRequestAttribute($attribute), function ($field) {
                return $field ? ($field->authorizedToSee() && ! $field->isReadOnly()) : true;
            });
        })->all());
    }

    /**
     * Get the associteables attributes but without any custom fields
     *
     * @return array
     */
    public function associateables()
    {
        $fields = $this->authorizedFields();
        $associations = $this->resource()->availableAssociations();

        return collect($this->all())->filter(function ($value, $attribute) use ($associations, $fields) {
            // First, we will check if the attribute name is the special attribute "associations"
            if ($attribute === 'associations') {
                return true;
            }

            // Next, we will check if the attribute exists as available associateable
            // resource for the current resource, if exists, we will check if the resource is associateable
            // This helps to provide the associations on resources without fields defined
            $resource = $associations->first(function ($resource) use ($attribute) {
                return $resource->associateableName() === $attribute;
            });

            // If resource is found from the attribute and this resource
            // is associateble, we will return true for the filter
            if ($resource && $resource->isAssociateable()) {
                return true;
            }

            // Next, we will check if the attribute exists as field in the
            // authorized fields collection for the request
            $field = $fields->findByRequestAttribute($attribute);

            // Finally, we will check if it's a field and is multioptionable field
            return $field && $field->isMultiOptionable() && ! $field->isCustomField();
        })->all();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->authorizedFields()->mapWithKeys(function ($field) {
            return [$field->requestAttribute() => Str::lower(strip_tags($field->label))];
        })->all();
    }

    /**
     * Get the error messages for the current resource request
     *
     * @return array
     */
    public function messages()
    {
        return array_merge($this->authorizedFields()->map(function ($field) {
            return $field->prepareValidationMessages();
        })->filter()->collapse()->all(), $this->messagesFromResource());
    }

    /**
     * Get the error messages that are defined from the resource class
     *
     * @return void
     */
    public function messagesFromResource()
    {
        return $this->resource()->validationMessages();
    }

    /**
     * Get all the available fields for the request
     *
     * @return \App\Core\Application\Fields\FieldsCollection
     */
    public function allFields()
    {
        if (! $this->isSaving()) {
            return new FieldsCollection;
        }

        return $this->resource()->setModel(
            $this->resourceId() ? $this->record() : null
        )->getFields();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (! $this->isSaving()) {
            return [];
        }

        $rules = array_merge_recursive(
            $this->resource()->rules($this),
            $this->isCreateRequest() ?
                $this->resource()->createRules($this) :
                $this->resource()->updateRules($this),
            $this->authorizedFields()->mapWithKeys(function ($field) {
                return $this->isCreateRequest() ? $field->getCreationRules() : $field->getUpdateRules();
            })->all()
        );

        return array_map(function ($rule) {
            return $rule = array_unique($rule);
        }, $rules);

    }

    /**
     * Check whether is saving
     *
     * @return bool
     */
    public function isSaving()
    {
        return ($this->isMethod('POST') && $this->route()->getActionMethod() === 'store') ||
            ($this->isMethod('PUT') && $this->route()->getActionMethod() === 'update');
    }
}
