<?php

namespace App\Resources\Revision;

use Illuminate\Http\Request;
use App\Core\Resources\Resource;
use App\Core\Application\Table\Table;
use App\Http\Resources\RevisionResource;
use App\Resources\Revision\RevisionTable;
use App\Core\Application\Fields\Base\Text;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Contracts\Resources\Resourceful;
use App\Core\Application\Menu\Item as MenuItem;
use App\Contracts\Repositories\RevisionRepository;
use App\Resources\Revision\Cards\TotalRevisions;

class Revision extends Resource implements Resourceful
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
        return resolve(RevisionRepository::class);
    }


    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return RevisionResource::class;
    }

    /**
     * Get the resource rules available for create and update
     *
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for create
     *
     *
     * @return array
     */
    public function createRules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for update
     *
     *
     * @return array
     */
    public function updateRules(Request $request)
    {
        return [];
    }

    /**
     * Provides the resource available actions
     */
    public function cards(): array
    {
        return [
            (new TotalRevisions)
        ];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        return [];
    }
}
