<?php

namespace App\Core\Application\Table\Traits;

use App\Core\Application\Table\Columns\DateTimeColumn;
use App\Core\Application\Table\Columns\ID;
use App\Core\Application\Table\Table;
use App\Core\Criteria\OnlyTrashedCriteria;
use App\Core\Http\Request\ResourceRequest;

trait ResolveTables
{
    /**
     * Resolve the resource table class
     *
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     */
    public function resolveTable(ResourceRequest $request): Table
    {
        $repository = $this->repository();

        if ($ownCriteria = $this->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        $table = $this->table($repository, $request)->setIdentifier($this->name());

        return $table;
    }

    /**
     * Resolve the resource trashed table class
     *
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     */
    public function resolveTrashedTable(ResourceRequest $request): Table
    {
        $repository = $this->repository()->pushCriteria(OnlyTrashedCriteria::class);

        if ($ownCriteria = $this->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        $table = $this->table($repository, $request)
            ->setIdentifier($this->name().'-trashed')
            ->clearOrderBy()
            ->orderBy(
                $repository->getModel()->getDeletedAtColumn()
            );

        // OVERWRITE COLUMNS (if any defined)
        // All columns will be visible on the trashed table so the user can see all
        // the data, as well we will push the deleted at column to be visible
        $table->setColumns($this->getTableColumnsFromFields())
            ->getColumns()
            ->prepend(
                DateTimeColumn::make(
                    $repository->getModel()->getDeletedAtColumn(),
                    __('app.deleted_at')
                )
            )
            ->prepend(
                ID::make(__('app.id'), $repository->getModel()->getKeyName())->hidden()
            )
            ->each->hidden(false);

        if (method_exists($table, 'actionsForTrashedTable')) {
            $table->setActions($table->actionsForTrashedTable());
        }

        return $table;
    }
}
