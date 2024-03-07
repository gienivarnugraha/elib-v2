<?php

namespace App\Core\Application\Fields\Relation;

use App\Core\Application\Fields\Optionable;

class HasOne extends Optionable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'v-select';

    /**
     * Relation name
     *
     * @var string
     */
    public $hasOneRelation;

    /**
     * @var string|null
     */
    protected $jsonResource;

    /**
     * The relation repository
     *
     * @var \App\Core\Repository\BaseRepository
     */
    protected $repository;

    /**
     * Create new instance of BelongsTo field
     *
     * @param  string  $name
     * @param  \App\Core\Repository\BaseRepository  $repository
     * @param  string  $label Label
     */
    public function __construct($relation, $attribute, $label)
    {
        parent::__construct($attribute, $label);

        $this->hasOneRelation = $relation;
    }

    /**
     * Set the JSON resource class for the BelongsTo relation
     *
     * @param  string  $resourceClass
     */
    public function setJsonResource($resourceClass)
    {
        $this->jsonResource = $resourceClass;

        return $this;
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        if (is_callable($this->displayCallback)) {
            return parent::resolveForDisplay($model);
        }

        return $model->{$this->hasOneRelation}->{$this->attribute} ?? null;
    }

    /**
     * Resolve the value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if (! $this->shouldResolveForJson($model)) {
            // Only return the foreign key
            return [$this->attribute => $model->{$this->attribute}];
        }

        return with($this->jsonResource, function ($jsonResource) use ($model) {
            return [
                $this->hasOneRelation => new $jsonResource($model->{$this->hasOneRelation}),
                $this->attribute => $this->resolve($model),
            ];
        });
    }

    /**
     * Check whether the fields values should be resolved for JSON resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function shouldResolveForJson($model)
    {
        return $model->relationLoaded($this->hasOneRelation) && $this->jsonResource;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'hasOneRelation' => $this->hasOneRelation,
        ]);
    }
}
