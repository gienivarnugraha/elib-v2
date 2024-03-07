<?php

namespace App\Providers;

use App\Core\Application\Menu\Item as MenuItem;
use App\Core\Facades\Application;
use App\Core\Facades\Menu;
use App\Core\Providers\CoreServiceProvider as BaseCoreServiceProvider;

class CoreServiceProvider extends BaseCoreServiceProvider
{
    /**
     * Register the application repositories
     *
     * @return void
     */
    protected function registerAppRepositories()
    {
        $this->app->bind(\App\Contracts\Repositories\UserRepository::class, \App\Eloquent\UserEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\SettingRepository::class, \App\Eloquent\SettingEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\AircraftRepository::class, \App\Eloquent\AircraftEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\DocumentRepository::class, \App\Eloquent\DocumentEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\RevisionRepository::class, \App\Eloquent\RevisionEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\ManualRepository::class, \App\Eloquent\ManualEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\MediaRepository::class, \App\Eloquent\MediaEloquent::class);
        $this->app->bind(\App\Contracts\Repositories\OrderRepository::class, \App\Eloquent\OrderEloquent::class);
        // auto-generate
        // ^ dont delete this
    }

    /**
     * Register the menu items that should be displayed on the sidebar
     *
     * @return void
     */
    protected function registerCoreMenuItems()
    {
        Application::booting(function () {
            Menu::register([
                MenuItem::make('dashboard', '/', 'bx-chart')->position(5),
            ]);

            Menu::register(MenuItem::make('settings', '/settings', 'bx-cog')
                ->canSeeWhen('super-admin')
                ->position(100));
        });
    }
}
