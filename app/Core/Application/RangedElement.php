<?php

namespace App\Core\Application;

use JsonSerializable;
use InvalidArgumentException;
use App\Core\Application\Date\Carbon;
use Illuminate\Support\Facades\Request;

class RangedElement implements JsonSerializable
{
  /**
   * The default selected range
   *
   * @var mixed
   */
  public string|int|null $defaultRange = null;

  /**
   * The ranges available for the chart
   *
   * @var array
   */
  public array $ranges = [];

  /**
   * Unit constants
   */
  const BY_MONTHS = 'month';

  const BY_WEEKS = 'week';

  const BY_DAYS = 'day';

  /**
   * Get the element ranges
   *
   * @return array
   */
  public function ranges(): array
  {
    return $this->ranges;
  }

  /**
   * Get the current range for the given request
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return string|int|null
   */
  protected function getCurrentRange($request): string|int|null
  {
    return $request->range ?? $this->defaultRange ?? array_keys($this->ranges())[0] ?? null;
  }

  /**
   * Get the available formated ranges
   *
   * @return array
   */
  protected function getFormattedRanges(): array
  {
    return collect($this->ranges() ?? [])->map(function ($range, $key) {
      return [
        'text' => $range,
        'value' => $key
      ];
    })->values()->all();
  }

  /**
   * Determine the proper aggregate starting date.
   *
   * @param \Illuminate\Http\Request $request
   * @param string $unit
   *
   * @return \App\Innoclapps\Date\Carbon
   */
  protected function getStartingDate($range, $unit)
  {
    $now   = Carbon::asCurrentTimezone();
    $range = $range - 1;

    return match ($unit) {
      'month'   => $now->subMonths($range)->firstOfMonth()->setTime(0, 0)->inAppTimezone(),
      'week'    => $now->subWeeks($range)->startOfWeek()->setTime(0, 0)->inAppTimezone(),
      'day'     => $now->subDays($range)->setTime(0, 0)->inAppTimezone(),
      'default' => throw new InvalidArgumentException('Invalid chart unit provided.')
    };
  }

  /**
   * jsonSerialize
   *
   * @return array
   */
  public function jsonSerialize(): array
  {
    return [
      'range'  => $this->getCurrentRange(Request::instance()),
      'ranges' => $this->getFormattedRanges(),
    ];
  }
}
