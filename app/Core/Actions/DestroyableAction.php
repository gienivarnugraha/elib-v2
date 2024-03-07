<?php

namespace App\Core\Actions;

use Illuminate\Support\Collection;

abstract class DestroyableAction extends Action
{
    /**
     * Provide the models repository class name
     *
     * @return string
     */
    abstract public function repository();

    /**
     * Handle method
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        $repository = resolve($this->repository());

        foreach ($models as $model) {
            $repository->delete($model->id);
        }
    }

    /**
     * Action name
     */
    public function name(): string
    {
        return __('app.delete');
    }
}
