<?php

namespace App\Resources\Setting;

use App\Core\Application\Fields\Base\Select;
use App\Core\Application\Fields\Base\Password;

class SettingField
{
    /**
     * Provides the contact resource available fields
     *
     * @param  \App\Resources\Resource  $resource
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function __invoke($resource, $request)
    {
        return [
            Select::make('date_format', 'Date Formats')
                ->icon('bx-calendar', 'prepend-inner')
                ->options(config('core.date_formats'))
                ->withDefaultValue(config('core.date_format')),

            Select::make('time_format', 'Time Formats')
                ->icon('bx-time', 'prepend-inner')
                ->options(config('core.time_formats'))
                ->withDefaultValue(config('core.time_format')),

            Select::make('first_day_of_week', 'Time Formats')
                ->icon('bx-time', 'prepend-inner')
                ->options([
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
                ]),

        ];
    }
}
