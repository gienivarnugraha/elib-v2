<?php

namespace App\Core\Contracts\Fields;

interface HandlesChangedMorphManyAttributes
{
    /**
     * Handle the attributes updated event
     *
     * @param  array  $new
     * @param  array  $old
     * @return void
     */
    public function morphManyAtributesUpdated($relationName, $new, $old);
}
