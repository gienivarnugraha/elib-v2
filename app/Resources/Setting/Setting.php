<?php

namespace App\Resources\Setting;

use App\Contracts\Repositories\SettingRepository;
use App\Core\Contracts\Resources\Resourceful;
use App\Core\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Setting extends Resource implements Resourceful
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
        return resolve(SettingRepository::class);
    }

    
    /**
     * Get the internal name of the resource.
     */

    /**
     * Get the resource rules available for create and update
     *
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            // 'locale'            => ['nullable', Rule::in(Translation::availableLocales())],
            // 'timezone'          => ['required', 'string', new ValidTimezoneCheckRule],
            'time_format' => ['required', 'string', Rule::in(config('core.time_formats'))],
            'date_format' => ['required', 'string', Rule::in(config('core.date_formats'))],
            'first_day_of_week' => ['required', 'in:1,6,0', 'numeric'],
        ];
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(Request $request): array
    {
        return (new SettingField)($this, $request);
    }
}
