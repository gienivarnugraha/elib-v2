<?php

namespace App\Core\Application\Date;

use App\Core\Contracts\Localizeable;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Timezone implements Arrayable, JsonSerializable
{
    /**
     * @param  int  $timestamp
     * @param  string  $timezone
     * @param  string  $format
     */
    public function convertFromUTC($timestamp, $timezone = null, $format = 'Y-m-d H:i:s'): string
    {
        $timezone ??= $this->current();

        $date = new DateTime($timestamp, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));

        return $date->format($format);
    }

    /**
     * @param  int  $timestamp
     * @param  string  $timezone
     * @param  string  $format
     */
    public function convertToUTC($timestamp, $timezone = null, $format = 'Y-m-d H:i:s'): string
    {
        $timezone ??= $this->current();

        $date = new DateTime($timestamp, new DateTimeZone($timezone));
        $date->setTimezone(new DateTimeZone('UTC'));

        return $date->format($format);
    }

    /**
     * Alias to convertToUTC
     *
     * @param  mixed  $params
     */
    public function toUTC(...$params): string
    {
        return $this->convertToUTC(...$params);
    }

    /**
     * Alias to convertFromUTC
     *
     * @param mixed
     */
    public function fromUTC(...$params): string
    {
        return $this->convertFromUTC(...$params);
    }

    /**
     * Get the current timezone for the appliaction
     */
    public static function current(Localizeable $user = null): string
    {
        $user ??= auth()->user();

        // In case using the logged in user, check the interface
        throw_if(
            $user && ! $user instanceof Localizeable,
            new Exception('The user must be instance of '.Localizeable::class)
        );

        return $user ? $user->getUserTimezone() : config('app.timezone');
    }

    /**
     * Get all timezones
     */
    public function all(): array
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Timezones to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }
}
