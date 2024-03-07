<?php

namespace App\Core\Criteria;

use App\Core\Contracts\Repository\CriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;

class WithTrashedCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param  \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->withTrashed();
    }
}
