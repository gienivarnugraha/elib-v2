<?php

namespace App\Core\Contracts\Repository;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryCriteriaInterface
 */
interface RepositoryCriteriaInterface
{
    /**
     * Push Criteria for filter the query
     *
     *
     * @return static
     */
    public function pushCriteria($criteria);

    /**
     * Pop Criteria
     *
     * @param  mixed  $criteria
     * @return static
     */
    public function popCriteria($criteria);

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria();

    /**
     * Find data by criteria
     *
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria);

    /**
     * Skip Criteria
     *
     * @param  bool  $status
     * @return static
     */
    public function skipCriteria($status = true);

    /**
     * Reset all Criterias
     *
     * @return static
     */
    public function resetCriteria();
}
