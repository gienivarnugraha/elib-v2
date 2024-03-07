<?php

namespace App\Resources\Aircraft;

use App\Contracts\Repositories\AircraftRepository;
use App\Core\Application\Table\Table;
use App\Core\Application\Table\Column;
use App\Core\Application\Filters\Fields\Select;

class AircraftTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('id', 'ID')
                ->sortable(false)
                ->primary(true)
                ->component('v-presentable-column'),
            Column::make('type', 'Type'),
            Column::make('serial_num', 'Serial Number'),
            Column::make('reg_code', 'Registration'),
            Column::make('effectivity', 'Effectivity'),
            Column::make('owner', 'Owner'),
            Column::make('manuf_date', 'Manufactured'),
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
                    resolve(AircraftRepository::class)
                        ->groupBy('type')
                        ->pluck('type')
                        ->all()
                )
                ->makeLabelAsValue(),
            Select::make('owner', 'Owner')
                ->setOperator('equal')
                ->options(
                    resolve(AircraftRepository::class)
                        ->groupBy('owner')
                        ->pluck('owner')
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
