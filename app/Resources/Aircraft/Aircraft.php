<?php

namespace App\Resources\Aircraft;

use App\Contracts\Repositories\AircraftRepository;
use App\Core\Application\Fields\Base\Number;
use App\Core\Application\Fields\Base\Select;
use App\Core\Application\Fields\Base\Text;
use App\Core\Application\Menu\Item as MenuItem;
use App\Core\Application\Table\Table;
use App\Core\Contracts\Resources\Resourceful;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Resources\Resource;
use App\Http\Resources\AircraftResource;
use Illuminate\Http\Request;

class Aircraft extends Resource implements Resourceful, Tableable
{
    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'id';

    /**
     * From where the value key should be taken
     */
    public string $valueKey = 'id';

    /**
     * From where the label key should be taken
     */
    public string $labelKey = 'type';


    /**
     * Get the underlying resource repository
     *
     * @return \App\Core\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(AircraftRepository::class);
    }

    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     */
    public function table($repository, Request $request): Table
    {
        return new AircraftTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return AircraftResource::class;
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
    public function actions(): array
    {
        return [];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        return [
            Select::make('type', 'Type')
                ->withDefaultPlaceholder('Select Aircraft Type')
                ->icon('bx-time', 'prepend-inner')
                ->options(['C212', 'CN235', 'AS365', 'CN295'])
                ->makeLabelAsValue(),

            Text::make('serial_num', 'Serial Number')->withDefaultPlaceholder(),
            Text::make('reg_code', 'Registration')->withDefaultPlaceholder(),
            Text::make('effectivity', 'Effectivity')->withDefaultPlaceholder(),
            Text::make('owner', 'Owner')->withDefaultPlaceholder(),
            Number::make('manuf_date', 'Manufactured')->withDefaultPlaceholder(),
        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make('aircraft', '/aircraft', 'bxs-plane')
                ->position(15)
                ->inQuickCreate(),
        ];
    }
}
