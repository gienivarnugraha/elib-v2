<?php

namespace Tests\Feature\Resources;

use App\Core\Application\Filters\Fields\Select;
use App\Core\Criteria\FilterCriteria;
use App\Resources\Document\OwnedCriteria;

trait UseFilterTest
{
    /**
     *  @param  array  $rules = [
     *           'type' => 'select',
     *           'attribute' => 'assignee_id',
     *           'operator' => 'equal',
     *           'value' => 1,
     *       ]
     * @param  array  $filters = [
     *           Select::make('assignee_id', 'By User'),
     *       ]
     *
     * */
    public function getFilterByFields($rules, $filters)
    {
        return $this->repository()
            ->pushCriteria(new FilterCriteria($this->request(['rules' => $rules]), collect($filters)))
            ->all();
    }
    /*
        public function getOwned($foreignKey)
        {
            return $this->repository()
                ->pushCriteria(new OwnedCriteria($foreignKey))
                ->all();
        } */
}
