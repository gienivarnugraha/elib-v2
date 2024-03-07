<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use App\Core\EditorImagesProcessor;

class Editor extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'editor-field';

    /**
     * Handle the resource record "created" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
        $this->runImagesProcessor($model);
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
        $this->runImagesProcessor($model);
    }

    /**
     * Handle the resource record "deleted" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleted($model)
    {
        $this->createImagesProcessor()->deleteAllViaModel(
            $model,
            $this->attribute
        );
    }

    /**
     * Run the editor images processor
     *
     * @param  $this  $model
     * @return void
     */
    protected function runImagesProcessor($model)
    {
        $this->createImagesProcessor()->processViaModel(
            $model,
            $this->attribute
        );
    }

    /**
     * Resolve the field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    public function resolve($model)
    {
        return clean(parent::resolve($model));
    }

    /**
     * Create editor images processor
     *
     * @return \App\Core\EditorImagesProcessor
     */
    protected function createImagesProcessor()
    {
        return new EditorImagesProcessor();
    }
}
