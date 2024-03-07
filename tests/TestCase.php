<?php

namespace Tests;

use App\Core\Core;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Tests\Traits\CreateApplication;
use Tests\Traits\CreateUser;

abstract class TestCase extends BaseTestCase
{
    use CreateApplication, CreateUser, RefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @see \Illuminate\Foundation\Testing\LazilyRefreshDatabase
     *
     * @return void
     */
    public function refreshDatabase()
    {
        $database = $this->app->make('db');

        $database->beforeExecuting(function () {
            if (RefreshDatabaseState::$lazilyRefreshed) {
                return;
            }

            RefreshDatabaseState::$lazilyRefreshed = true;

            $this->baseRefreshDatabase();
            // $this->artisan('migrate', ['--path' => 'tests/Migrations']);
        });

        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$lazilyRefreshed = false;
        });
    }

    /**
     * Setup the tests
     */
    protected function setUp(): void
    {
        Core::$resources = new Collection;

        parent::setUp();

        $this->registerTestResources();

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * Tear down the tests
     */
    protected function tearDown(): void
    {
        Core::setImportStatus(false);
        // FieldsManager::flushCache();
        // \Spatie\Once\Cache::getInstance()->flush();
        // app(CustomFieldRepository::class)->flushCache();

        parent::tearDown();
    }

    /**
     * Register the tests resources
     *
     * @return void
     */
    protected function registerTestResources()
    {
        Core::resources([
            // EventResource::class,
            // CalendarResource::class,
        ]);
    }
}
