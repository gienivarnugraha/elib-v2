<?php

namespace App\Core\Contracts\Repository;

/**
 * Interface CriteriaInterface
 */
interface CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
