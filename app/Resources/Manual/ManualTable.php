<?php

namespace App\Resources\Manual;

use App\Contracts\Repositories\ManualRepository;
use App\Core\Application\Table\Table;
use App\Core\Application\Table\Column;
use App\Core\Application\Filters\Fields\Select;
use App\Core\Application\Table\Columns\BooleanColumn;
use App\Core\Application\Table\Columns\BelongsToColumn;

class ManualTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('part_number')->primary(true)->component('v-presentable-column'),
            Column::make('type'),
            Column::make('subject'),
            BelongsToColumn::make('aircraft', 'type')
                ->select(['serial_num', 'reg_code'])
                ->primary(true)
                ->component('v-presentable-column'),
            Column::make('lib_call'),
            Column::make('volume'),
            Column::make('vendor'),
            BooleanColumn::make('caplist'),
            Column::make('collector'),
        ];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [
            Select::make('type', 'Type')
                ->setOperator('equal')
                ->options(
                    resolve(ManualRepository::class)
                        ->groupBy('type')
                        ->pluck('type')
                        ->all()
                )
                ->makeLabelAsValue(),
        ];
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
