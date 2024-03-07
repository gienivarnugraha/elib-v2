<?php

namespace App\Resources\User;

use App\Core\Application\Table\Column;
use App\Core\Application\Table\Columns\BooleanColumn;
use App\Core\Application\Table\Columns\DateTimeColumn;
use App\Core\Application\Table\Table;
use App\Resources\Setting\SettingColumn;

class UserTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('avatar', '')->component('v-avatar-column'),

            Column::make('name', __('user.name')),

            Column::make('email', __('user.email'))->queryWhenHidden(),

            DateTimeColumn::make('created_at', __('app.created_at'))->queryWhenHidden(),

            SettingColumn::make('settings', 'time_format', 'Time Format'),

            SettingColumn::make('settings', 'date_format', 'Date Format'),
        ];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [
            'avatar',
        ];
    }

    /**
     * Set appends
     */
    protected function appends(): array
    {
        return [
            'avatar_url', 'is_admin',
        ];
    }

    /**
     * Boot table
     *
     * @return null
     */
    public function boot(): void
    {
        $this->orderBy('name');
        $this->with(['settings']);
    }
}
