<?php

namespace App\Resources\Document;

use App\Enums\DocumentType;
use Illuminate\Http\Request;
use App\Enums\DocumentOffice;
use Illuminate\Validation\Rule;
use App\Core\Resources\Resource;
use App\Core\Application\Table\Table;
use App\Http\Resources\DocumentResource;
use App\Core\Application\Fields\Base\Text;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Application\Fields\Base\Select;
use App\Core\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\UserRepository;
use App\Core\Application\Menu\Item as MenuItem;
use App\Contracts\Repositories\AircraftRepository;
use App\Contracts\Repositories\DocumentRepository;
use App\Core\Application\Fields\Relation\BelongsTo;
use App\Resources\Document\Cards\CanceledDocuments as CardsCanceledDocuments;
use App\Resources\Document\Cards\ClosedDocuments;
use App\Resources\Document\Cards\ClosedDocumentsCount;
use App\Resources\Document\Cards\TotalDocuments;

class Document extends Resource implements Resourceful, Tableable
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'id';

    /**
     * Get the underlying resource repository
     *
     * @return \App\Core\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(DocumentRepository::class);
    }

    /**
     * Set the criteria that should be used to fetch only own data for the user
     */
    public function ownCriteria(): ?string
    {
        return null; // OwnDocumentsCriteria::class;
    }

    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     */
    public function table($repository, Request $request): Table
    {
        return new DocumentTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DocumentResource::class;
    }

    /**
     * Get the resource rules available for create and update
     *
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'type' => [Rule::in(DocumentType::names())],
            'office' => [Rule::in(DocumentOffice::names())],
            'title' => ['required'],

        ];
    }

    /**
     * Set the resource rules available only for create
     *
     *
     * @return array
     */
    public function createRules(Request $request)
    {
        return [
            'aircraft_id' => ['required'],
            'type' => ['required'],
            'office' => ['required'],
        ];
    }

    /**
     * Set the resource rules available only for update
     *
     *
     * @return array
     */
    public function updateRules(Request $request)
    {
        return [
            'no' => ['prohibited'],
            'aircraft_id' => ['prohibited'],
            'type' => ['prohibited'],
            'office' => ['prohibited'],
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function cards(): array
    {
        return [
            (new ClosedDocuments),
            (new CardsCanceledDocuments),
            (new TotalDocuments),
            (new ClosedDocumentsCount),
        ];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        return [
            Select::make('office')
                ->withDefaultPlaceholder()
                ->options(DocumentOffice::class)
                ->excludeFromUpdate(),
            Select::make('type')
                ->withDefaultPlaceholder()
                ->options(DocumentType::class)
                ->excludeFromUpdate(),
            Text::make('subject')
                ->withDefaultPlaceholder(),
            BelongsTo::make('aircraft', AircraftRepository::class, 'aircraft', 'aircraft_id')
                ->labelKey('type')
                ->async('aircraft'),
            BelongsTo::make('assignee', UserRepository::class, 'assignee', 'assignee_id')
                ->labelKey('name')
                ->async('users'),
            Text::make('reference')
                ->withDefaultPlaceholder(),
        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make('documents', '/documents', 'bx-file-blank')
                ->inQuickCreate(),
        ];
    }
}
