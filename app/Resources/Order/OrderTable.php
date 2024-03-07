<?php

namespace App\Resources\Order;

use App\Core\Application\Table\Table;
use App\Core\Application\Table\Column;
use App\Core\Application\Table\Columns\DateColumn;
use App\Core\Application\Table\Columns\ActionColumn;
use App\Core\Application\Table\Columns\BelongsToColumn;

class OrderTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            BelongsToColumn::make('media', 'name')
                ->select(['size', 'uuid'])
                ->appends(['type'])
                ->component('v-presentable-column'),
            BelongsToColumn::make('user', 'name')
                ->select(['nik', 'org', 'avatar'])
                ->appends(['avatar_url'])
                ->component('v-user-column'),
            Column::make('passcode', 'Passcode')
                ->component('v-password-column'),
            DateColumn::make('date_from', 'Valid From')->queryWhenHidden(),
            DateColumn::make('date_to', 'Valid To')->queryWhenHidden(),
            DateColumn::make('created_at', 'Created at')->queryWhenHidden(),
            ActionColumn::make('action'),
        ];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [
            'is_confirmed'
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
        // $this->with('media');

        $this->orderBy('date_from', 'desc');
    }
}
