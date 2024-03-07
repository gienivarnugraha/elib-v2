<?php

namespace App\Resources\Order;

use Illuminate\Http\Request;
use App\Core\Resources\Resource;
use App\Resources\Order\OrderTable;
use App\Core\Application\Table\Table;
use App\Http\Resources\OrderResource;
use App\Core\Application\Fields\Base\Text;
use App\Core\Contracts\Resources\Tableable;
use App\Core\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\OrderRepository;
use App\Core\Application\Menu\Item as MenuItem;

class Order extends Resource implements Resourceful, Tableable
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
        return resolve(OrderRepository::class);
    }

    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     */
    public function table($repository, Request $request): Table
    {
        return new OrderTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return OrderResource::class;
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
            Text::make('date_from'),
            Text::make('date_to'),
        ];
    }

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make('orders', '/orders', 'bx-user')
                ->inQuickCreate(),
        ];
    }
}
