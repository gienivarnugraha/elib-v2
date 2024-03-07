<?php

namespace App\Resources\Document;

use App\Enums\DocumentType;
use App\Core\Application\Table\Table;
use App\Core\Application\Table\Column;
use App\Core\Application\Filters\Fields\Select;
use App\Resources\User\Filter\User as UserFilter;
use App\Resources\Document\Filters\OwnDocumentFilter;
use App\Core\Application\Table\Columns\BelongsToColumn;
use App\Core\Application\Filters\Fields\Text as TextFilter;
use App\Enums\DocumentOffice;

class DocumentTable extends Table
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('no')->primary(true)->component('v-presentable-column'),
            Column::make('office'),
            Column::make('type'),
            BelongsToColumn::make('assignee', 'name')
                ->select(['nik', 'org', 'avatar'])
                ->appends(['avatar_url'])
                ->component('v-user-column'),
            BelongsToColumn::make('aircraft', 'type')
                ->select(['serial_num', 'reg_code'])
                ->component('v-presentable-column'),
            Column::make('subject'),
            Column::make('reference'),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined filters
     */
    public function filters(): array
    {
        return [
            OwnDocumentFilter::make(),
            UserFilter::make('User', 'assignee_id'),
            Select::make('office', 'Office')
                ->setOperator('equal')
                ->options(DocumentOffice::class),
            Select::make('type', 'Type')
                ->setOperator('equal')
                ->options(DocumentType::class),
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
        $this->orderBy('no');
        //$this->load('revisions')
    }
}
