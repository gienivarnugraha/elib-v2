<?php

namespace App\Core\Http\View\Composers;

use App\Core\Facades\Application;
use App\Core\Facades\Menu;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppComposer
{
    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Bind data to the view.
     *
     *
     * @return void
     */
    public function compose(View $view)
    {
        Application::boot();

        $config = [];

        $config['url'] = rtrim(config('app.url'), '/');
        $config['is_secure'] = request()->secure();
        // $config['max_upload_size']  = config('core.max_size');

        $config['settings'] = [
            'locale' => app()->getLocale(),
            'fallback_locale' => config('app.fallback_locale'),
            'timezone' => config('app.timezone'),
            'time_format' => config('core.time_format'),
            'date_format' => config('core.date_format'),
            'company_name' => config('app.name'),
            'logo_light' => config('core.logo.light'),
            'logo_dark' => config('core.logo.dark'),
            // 'first_day_of_week' => settings()->get('first_day_of_week'),
        ];

        /*
                $config['currency'] = array_merge(
                  array_values(currency(Application::currency())->toArray())[0],
                  ['iso_code' => Application::currency()]
                );

             */
        $config['is_logged_in'] = Auth::check();

        if (Auth::check()) {
            $config['resources'] = Application::registeredResources();

            $config['user'] = new UserResource(Auth::user());

            $config['api_url'] = url(config('core.api_prefix'));

            $config['notifications'] = [
                'latest' => Auth::user()->latestFifteenNotifications,
                'unread_count' => count(Auth::user()->unreadNotifications),
                // Admin user edit and profile
                // $this->mergeWhen(Auth::user()->isSuperAdmin() || $this->is(Auth::user()), [
                //     'settings' => Application::notificationsInformation($this->resource),
                // ]),
            ];

            // $config['settings']['options'] = [
            //     'date_formats' => config('core.date_formats'),
            //     'time_formats' => config('core.time_formats'),
            //     'favourite_colors' => Application::favouriteColors(),
            // ];

            $config['menus'] = Menu::get();

            if (Auth::user()->isSuperAdmin()) {
                $config['is_super_admin'] = true;
                $config['secret_key_super_admin'] = 'ad16z5c8489a421dqw65e874sad';

                $config['notifications_information'] = Application::notificationsInformation();
            }

            /*
                      $config['broadcasting'] = [
                          'default'    => config('broadcasting.default'),
                          'connection' => config('broadcasting.connections.' . config('broadcasting.default')),
                      ];

                      $config['user_id']                   = Auth::id();
                      $config['notifications_information'] = Application::notificationsInformation();

                      $config['resources'] = Application::registeredResources()->mapWithKeys(function ($resource) {
                        return [$resource->name() => $resource->jsonSerialize()];
                      });

                      $config['microsoft'] = [
                        'client_id' => config('innoclapps.microsoft.client_id'),
                      ];

                      $config['google'] = [
                        'client_id' => config('innoclapps.google.client_id'),
                      ];

                      $requirements = new RequirementsChecker;

                      $config['requirements'] = [
                        'imap' => $requirements->passes('imap'),
                        'zip'  => $requirements->passes('zip'),
                      ];

                      $config['settings'] = [
                        'menu' => SettingsMenu::all(),
                      ];

                      $config['associations'] = [
                        'common' => Application::getResourcesNames(),
                      ];
                  */
        }

        $view->with('config', array_merge($config, Application::getDataProvidedToScript()));
        // $view->with('lang', get_generated_lang(app()->getLocale()));
    }
}
