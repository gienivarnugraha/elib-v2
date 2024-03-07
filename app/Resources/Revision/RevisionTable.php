<?php

namespace App\Resources\Revision;

use App\Core\Application\Table\Table;

class RevisionTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [

        ];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [];
    }

    /**
     * Set appends
     */
    protected function appends(): array
    {
        return [];
    }

    /**
     * Boot table
     *
     * @return null
     */
    public function boot(): void
    {
        $this->orderBy('name');
    }
}
