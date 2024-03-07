<?php

namespace App\Resources\Manual;

use Illuminate\Http\Request;
use App\Core\Resources\Resource;
use App\Core\Application\Table\Table;
use App\Http\Resources\ManualResource;
use App\Core\Application\Fields\Base\Text;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Contracts\Resources\Resourceful;
use App\Resources\Manual\Cards\TotalManuals;
use App\Core\Application\Menu\Item as MenuItem;
use App\Contracts\Repositories\ManualRepository;
use App\Contracts\Repositories\AircraftRepository;
use App\Core\Application\Fields\Base\Autocomplete;
use App\Core\Application\Fields\Base\Boolean;
use App\Core\Application\Fields\Relation\BelongsTo;

class Manual extends Resource implements Resourceful, Tableable
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
        return resolve(ManualRepository::class);
    }

    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     */
    public function table($repository, Request $request): Table
    {
        return new ManualTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return ManualResource::class;
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
        return [
            'part_number'  => ['required'],
            'type'  => ['required'],
            'subject'  => ['required'],
            'aircraft_id'  => ['required'],
            'lib_call'  => ['required'],
            'volume'  => ['required'],
            'vendor'  => ['required'],
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
        return [];
    }

    /**
     * Provides the resource available actions
     */
    public function cards(): array
    {
        return [
            (new TotalManuals)
        ];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('subject'),
            BelongsTo::make('aircraft', AircraftRepository::class, 'aircraft', 'aircraft_id')
                ->labelKey('type')
                ->async('aircraft'),
            Text::make('type'),
            Text::make('part_number'),
            Text::make('lib_call'),
            Text::make('volume'),
            Text::make('vendor'),
            Boolean::make('caplist'),
            Text::make('collector'),

        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make('manuals', '/manuals', 'bx-user')
                ->inQuickCreate(),
        ];
    }
}
