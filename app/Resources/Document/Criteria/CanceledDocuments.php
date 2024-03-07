<?php

namespace App\Resources\Document\Criteria;

use App\Core\Contracts\Repository\CriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;

class CanceledDocuments implements CriteriaInterface
{
  /**
   * Apply criteria in query repository
   *
   * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
   * @param \App\Core\Contracts\Repository\RepositoryInterface $repository
   *
   * @return mixed
   */
  public function apply($model, RepositoryInterface $repository)
  {
    return static::applyQuery($model);
  }

  /**
   * Apply the query for the criteria
   *
   * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
   *
   * @return \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
   */
  public static function applyQuery($model)
  {
    return $model->whereHas('revisions', function ($query) {
      $query->where('is_canceled', true);
    });
  }
}
