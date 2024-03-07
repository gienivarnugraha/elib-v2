<?php

namespace App\Core\Application\Filters;

interface CountableRelation
{
    /**
     * Indicates that the filter will count the values of the relation
     *
     * @param  string|null  $relationName
     * @return \App\Innoclapps\Filters\Filter
     */
    public function countableRelation($relationName = null);

    /**
     * Get the countable relation name
     *
     * @return string|null
     */
    public function getCountableRelation();
}
