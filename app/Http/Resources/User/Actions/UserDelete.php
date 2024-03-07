<?php

namespace App\Core\Resources\User\Actions;

use App\Core\Actions\ActionFields;
use App\Core\Actions\DestroyableAction;
use App\Core\Contracts\Repositories\UserRepository;
use App\Core\Fields\User;
use App\Http\Requests\ActionRequest;
use Illuminate\Support\Collection;

class UserDelete extends DestroyableAction
{
    /**
     * Handle method
     *
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        // User delete action flag
        $repository = resolve($this->repository());

        foreach ($models as $model) {
            $repository->delete($model->id, (int) $fields->user_id);
        }
    }

    /**
     * Provide the models repository class name
     *
     * @return string
     */
    public function repository()
    {
        return UserRepository::class;
    }

    /**
     * Action fields
     */
    public function fields(): array
    {
        return [
            User::make('')
                ->help(__('user.transfer_data_info'))
                ->helpDisplay('text')
                ->rules('required'),
        ];
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->isSuperAdmin();
    }

    /**
     * Action name
     */
    public function name(): string
    {
        return __('user.actions.delete');
    }
}
