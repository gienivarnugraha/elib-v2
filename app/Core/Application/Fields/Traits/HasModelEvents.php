<?php

namespace App\Core\Application\Fields\Traits;

trait HasModelEvents
{
    /**
     * Handle the resource record "creating" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
        //
    }

    /**
     * Handle the resource record "created" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
        //
    }

    /**
     * Handle the resource record "updating" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdating($model)
    {
        //
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
        //
    }

    /**
     * Handle the resource record "deleting" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleting($model)
    {
        //
    }

    /**
     * Handle the resource record "deleted" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleted($model)
    {
        //
    }
}
