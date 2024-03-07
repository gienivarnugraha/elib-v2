<?php

namespace App\Core\Providers;

use App\Core\Facades\Application;
use App\Core\Facades\Menu;
use App\Core\Http\View\Composers\AppComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCoreRepositories();
        $this->registerAppRepositories();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCoreMenuItems();
        $this->registerCoreSettingsMenuItems();
        $this->bootSettings();
        $this->bootApplication();
        $this->bootComposer();
        $this->registerMacros();
        $this->registerGates();
        $this->handleLazyLoad();

        if (Application::isAppInstalled()) {
            // Timelineables::discover();
        }
    }

    // Avoid lazy loading violation when the calls are coming from the repositories delete, restore and forceDelete
    // methods because these methods are using foreach loops to find and delete/restore/forceDelete multiple models
    // However, this is valid only for development installation
    public function handleLazyLoad()
    {
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            if (! collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->first(function ($trace) {
                return in_array($trace['function'], ['delete', 'forceDelete', 'restore']) &&
                    isset($trace['class']) && stripos($trace['class'], 'repository') !== false;
            })) {
                throw new LazyLoadingViolationException($model, $relation);
            }
        });
    }

    /**
     * Register the gates
     *
     * @return void
     */
    public function registerGates()
    {
        Gate::before(fn ($user, $ability) => $user->isSuperAdmin() ? true : null);
        Gate::define('super-admin', fn ($user) => $user->isSuperAdmin());
    }

    /**
     * Register the core repositories
     *
     * @return void
     */
    public function registerCoreRepositories()
    {
        // $this->app->bind(
        //     \App\Core\Contracts\Repository\FilterRepository::class,
        //     \App\Core\Application\Filters\FilterRepositoryEloquent::class
        // );

        // $this->app->bind(
        //     \App\Core\Contracts\Repositories\DashboardRepository::class,
        //     \App\Core\Application\Cards\DashboardRepositoryEloquent::class
        // );

        // $this->app->bind(
        //     \App\Core\Contracts\Repositories\ImportRepository::class,
        //     \App\Core\Application\Resources\ImportRepositoryEloquent::class
        // );
    }

    /**
     * Register the application repositories
     *
     * @return void
     */
    protected function registerAppRepositories()
    {

    }

    /**
     * Register application macros
     *
     * @return void
     */
    public function registerMacros()
    {
        Arr::macro('toObject', new \App\Core\Macros\ToObject);

        // Str::macro('isJson', new \App\Core\Macros\Str\IsJson);
        // Str::macro('isBase64Encoded', new \App\Core\Macros\Str\IsBase64Encoded);
        // Str::macro('clickable', new \App\Core\Macros\Str\ClickableUrls);

        // Arr::macro('valuesAsString', new \App\Core\Macros\Arr\CastValuesAsString);

        // Request::macro('isSearching', new \App\Core\Macros\Request\IsSearching);

        // URL::macro('asAppUrl', function ($extra = '') {
        //     return rtrim(config('app.url'), '/') . ($extra ? '/' . $extra : '');
        // });
    }

    /**
     * Register the menu items that should be displayed on the sidebar
     *
     * @return void
     */
    protected function registerCoreMenuItems()
    {

    }

    /**
     * Register the settings menu items
     *
     * @return void
     */
    protected function registerCoreSettingsMenuItems()
    {
        Application::booting(function () {
            // tap(SettingsMenuItem::make(__('fields.fields'))->icon('ViewGridAdd')->order(10), function ($item) {
            //   Application::registeredResources()
            //     ->filter(fn ($resource) => $resource::$fieldsCustomizable)
            //     ->each(function ($resource) use ($item) {
            //       $item->withChild(
            //         SettingsMenuItem::make(
            //           $resource->singularLabel(),
            //           "/settings/fields/{$resource->name()}"
            //         ),
            //         'fields-' . $resource->name()
            //       );
            //     });
            //   SettingsMenu::register($item, 'fields');
            // });

            // SettingsMenu::register(
            //   SettingsMenuItem::make(__('settings.system'))->icon('Cog')->order(70)
            //     ->withChild(SettingsMenuItem::make(__('settings.tools.tools'), '/settings/tools'), 'tools')
            //     ->withChild(SettingsMenuItem::make(__('settings.translator.translator'), '/settings/translator'), 'translator')
            //     ->withChild(SettingsMenuItem::make(__('app.system_info'), '/settings/info'), 'system-info')
            //     ->withChild(SettingsMenuItem::make('Logs', '/settings/logs'), 'system-logs'),
            //   'system'
            // );
        });
    }

    /**
     * Boot the application settings
     *
     * @return void
     */
    protected function bootSettings()
    {
        if (! Application::isAppInstalled()) {
            return;
        }

        // $this->configureAllowedExtensions();
        // $this->configureBroadcasting();
        // $this->configureVoIP();
    }

    /**
     * Boot application
     *
     * The app.php is the main file and is loaded only if the user us authenticated
     *
     * @return null
     */
    protected function bootApplication()
    {
        // Model::preventLazyLoading(!$this->app->isProduction());
        Schema::defaultStringLength(191);
        JsonResource::withoutWrapping();

        Application::resourcesIn(app_path('Resources'));
        Application::resourcesIn(app_path('Core/Resources'));
        Application::notificationsIn(app_path('Notifications'));

        if (Application::isAppInstalled()) {
            // Application::resourcesIn(app_path('Resources'));
            // Application::resourcesIn(app_path('Core/Resources'));
            // Application::notificationsIn(app_path('Core/Notifications'));
            // Workflows::triggersIn(app_path('Core/Workflows/Triggers'));
            // Workflows::registerEventOnlyTriggersListeners();
        }
    }

    protected function bootComposer()
    {
        View::composer(['application', 'auth.login'], AppComposer::class);
    }
}
