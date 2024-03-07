<?php

return [

  'api_prefix' => 'api',

  'default_password' => '12345',

  /*
    |--------------------------------------------------------------------------
    | Application Date Format
    |--------------------------------------------------------------------------
    |
    | Application date format, the value is used when performing formats for to
    | local date via the available formatters.
    |
    */

  'date_format' => 'F j, Y',

  /*
      |--------------------------------------------------------------------------
      | Application Time Format
      |--------------------------------------------------------------------------
      |
      | Application time format, the value is used when performing formats for to
      | local datetime via the available formatters.
      |
      */

  'time_format' => 'H:i:s',

  /*
      |--------------------------------------------------------------------------
      | Application Currency
      |--------------------------------------------------------------------------
      |
      | The application currency, is used on a specific features e.q. form groups
      |
      */
  'currency' => 'IDR',

  /*
    |--------------------------------------------------------------------------
    | Application favourite colors
    |--------------------------------------------------------------------------
    |
    */
  'colors' => explode(',', env(
    'COMMON_COLORS',
    '#374151,#DC2626,#F59E0B,#10B981,#2563EB,#4F46E5,#7C3AED,#EC4899,#F3F4F6'
  )),

  /*
    |--------------------------------------------------------------------------
    | Application defaults config
    |--------------------------------------------------------------------------
    | Here you can specify defaults configurations that the application
    | uses when configuring specific option e.q. creating a follow up task
    | automatically uses the configured hour and minutes.
    |
    */
  'defaults' => [
    'hour' => env('PREFERRED_DEFAULT_HOUR', 8),
    'minutes' => env('PREFERRED_DEFAULT_MINUTES', 0),
    'reminder_minutes' => env('PREFERRED_DEFAULT_REMINDER_MINUTES', 30),
  ],

  /*
      |--------------------------------------------------------------------------
      | Application logo config
      |--------------------------------------------------------------------------
      |
      */
  'logo' => [
    'light' => env('LOGO_LIGHT_URL'),
    'dark' => env('LOGO_DARK_URL'),
  ],

  /*
      |--------------------------------------------------------------------------
      | Application Allowed Date Formats
      |--------------------------------------------------------------------------
      |
      | The application date format that the users are able to use.
      |
      */
  'date_formats' => [
    'd-m-Y',
    'd/m/Y',
    'm-d-Y',
    'm.d.Y',
    'm/d/Y',
    'Y-m-d',
    'd.m.Y',
    'F j, Y',
    'j F, Y',
    'D, F j, Y',
    'l, F j, Y',
  ],

  /*
      |--------------------------------------------------------------------------
      | Application Allowed Time Formats
      |--------------------------------------------------------------------------
      |
      | The application time format that the users are able to use.
      |
      */
  'time_formats' => [
    'H:i',
    'h:i A',
    'h:i a',
  ],

  /*
      |--------------------------------------------------------------------------
      | User invitation config
      |--------------------------------------------------------------------------
      |
      */
  'invitation' => [
    'expires_after' => env('USER_INVITATION_EXPIRES_AFTER', 3), // in days
  ],

  /*
    |--------------------------------------------------------------------------
    | Application favicon
    |--------------------------------------------------------------------------
    | Here you may enable favicon to be included, but first you must generate
    | the favicons via https://realfavicongenerator.net/ and upload the .zip file
    | contents in /public/favicons.
    |
    | More info: https://www.concordcrm.com/docs/favicon
    |
    */
  'favicon_enabled' => env('ENABLE_FAVICON', false),

];
