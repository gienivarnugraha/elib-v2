<?php

namespace App\Core\Application\Charts;

use InvalidArgumentException;
use App\Core\Application\Date\Carbon;
use Illuminate\Support\Facades\DB;
use App\Core\Facades\Timezone;
use App\Core\Repository\BaseRepository;

abstract class Progression extends Chart
{
    /**
     * Count results aggregate over months
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string|null $column
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function countByMonths($request, $repository, $column = null)
    {
        return $this->count($request, $repository, self::BY_MONTHS, $column);
    }

    /**
     * Count results aggregate over weeks
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string|null $column
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function countByWeeks($request, $repository, $column = null)
    {
        return $this->count($request, $repository, self::BY_WEEKS, $column);
    }

    /**
     * Count results aggregate over days
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string|null $column
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function countByDays($request, $repository, $column = null)
    {
        return $this->count($request, $repository, self::BY_DAYS, $column);
    }

    /**
     * Count results aggregate over specific time
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $unit
     * @param string|null $column
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function count($request, $repository, $unit, $column = null)
    {
        $repository = $repository instanceof BaseRepository ? $repository : resolve($repository);

        return $this->aggregate($request, $repository, $unit, 'count', $repository->getModel()->getQualifiedKeyName(), $column);
    }

    /**
     * Average results aggregate over months
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function averageByMonths($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_MONTHS, 'avg', $column, $dateColumn);
    }

    /**
     * Average results aggregate over weeks
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function averageByWeeks($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_WEEKS, 'avg', $column, $dateColumn);
    }

    /**
     * Average results aggregate over days
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function averageByDays($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_DAYS, 'avg', $column, $dateColumn);
    }

    /**
     * Average results aggregate over time
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $unit
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function average($request, $repository, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, $unit, 'avg', $column, $dateColumn);
    }

    /**
     * Sum results aggregate over months
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function sumByMonths($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_MONTHS, 'sum', $column, $dateColumn);
    }

    /**
     * Sum results aggregate over weeks
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function sumByWeeks($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_WEEKS, 'sum', $column, $dateColumn);
    }

    /**
     * Sum results aggregate over days
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function sumByDays($request, $repository, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, self::BY_DAYS, 'sum', $column, $dateColumn);
    }

    /**
     * Sum results aggregate over time
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository|string $repository
     * @param string $unit
     * @param string $column
     * @param string|null $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function sum($request, $repository, $unit, $column, $dateColumn = null)
    {
        return $this->aggregate($request, $repository, $unit, 'sum', $column, $dateColumn);
    }

    /**
     * Return a value result showing a aggregate over time
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Core\Application\Repository\BaseRepository $model
     * @param string $unit
     * @param string $function
     * @param string $column
     * @param string $dateColumn
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    protected function aggregate($request, $repository, $unit, $function, $column, $dateColumn = null)
    {
        $repository = $repository instanceof BaseRepository ? $repository : resolve($repository);

        $dateColumn = $dateColumn ?: $repository->getModel()->getCreatedAtColumn();
        $range      = $this->getCurrentRange($request);

        $startingDate = $this->getStartingDate($range, $unit);
        $endingDate   = Carbon::asAppTimezone();

        $expression = $this->determineAggragateExpression(
            $repository->getModel()->getQuery()->getConnection()->getDriverName(),
            $dateColumn,
            $unit
        );

        $column = $repository->getModel()->getQuery()->getGrammar()->wrap($column);

        $results = $repository->columns(DB::raw("{$expression} as date_result, {$function}({$column}) as aggregate"))
            ->scopeQuery(function ($query) use ($dateColumn, $startingDate, $endingDate) {
                return $query->whereBetween($dateColumn, [$startingDate, $endingDate]);
            })->groupBy(DB::raw($expression))->orderBy('date_result')->get();

        $repository->resetScope();

        $results = array_merge(
            $this->getPossibleDateResults($startingDate->copy(), $endingDate->copy(), $unit),
            $results->mapWithKeys(function ($result) use ($request, $unit) {
                return [$this->formatLabelByDate(
                    $result->date_result,
                    $unit
                ) => round($result->aggregate, 0)];
            })->all()
        );

        if (count($results) > $range) {
            array_shift($results);
        }

        return $this->result($results);
    }

    /**
     * For date for the label that will be shown on the chart
     *
     * @param \App\Core\Application\Date\Carbon|string $date
     * @param strgin $unit
     *
     * @return string
     */
    protected function formatLabelByDate($date, $unit): string
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        $dayCallback = function () use ($date) {
            $newDate = Carbon::createFromFormat('Y-m-d', $date);

            return $newDate->format('F') . ' ' . $newDate->format('j') . ', ' . $newDate->format('Y');
        };

        return match ($unit) {
            'month' => $this->formatMonthDate($date),
            'week'  => $this->formatWeekDate($date),
            'day'   => $dayCallback(),
            default => $date,
        };
    }

    /**
     * Create a new progression chart result.
     *
     * @param \array $value
     *
     * @return \App\Core\Application\Charts\ChartResult
     */
    public function result(array $value): ChartResult
    {
        return new ChartResult($value);
    }

    /**
     * Format date for the label that will be shown on the chart
     *
     * @param string $date
     *
     * @return string
     */
    protected function formatMonthDate($date): string
    {
        [$year, $month] = explode('-', $date);
        $newDate        = Carbon::createFromDate((int) $year, (int) $month, 1);

        return $newDate->format('F') . ' ' . $newDate->format('Y');
    }

    /**
     * Format the aggregate week result date into a proper string.
     *
     * @param string $result
     *
     * @return string
     */
    protected function formatWeekDate($result): string
    {
        [$year, $week] = explode('-', $result);

        $isoDate = (new \DateTime)->setISODate($year, $week)->setTime(0, 0);

        [$startingDate, $endingDate] = [
            Carbon::instance($isoDate),
            Carbon::instance($isoDate)->endOfWeek(),
        ];

        return __($startingDate->format('F')) . ' ' . $startingDate->format('j') . ' - ' .
            __($endingDate->format('F')) . ' ' . $endingDate->format('j');
    }

    /**
     * Get all possible dates for the charts
     * Continuous timeline including dates without data
     *
     * @param \App\Core\Application\Date\Carbon $startingDate
     * @param \App\Core\Application\Date\Carbon $endingDate
     * @param string $unit
     *
     * @return array
     */
    protected function getPossibleDateResults(Carbon $startingDate, Carbon $endingDate, $unit): array
    {
        $nextDate = $startingDate;
        $timezone = Timezone::current();

        $nextDate   = $startingDate->setTimezone($timezone);
        $endingDate = $endingDate->setTimezone($timezone);

        $possibleDateResults[$this->formatLabelByDate($nextDate, $unit)] = 0;

        while ($nextDate->lt($endingDate)) {
            if ($unit === self::BY_MONTHS) {
                $nextDate = $nextDate->addMonths(1);
            } elseif ($unit === self::BY_WEEKS) {
                $nextDate = $nextDate->addWeeks(1);
            } elseif ($unit === self::BY_DAYS) {
                $nextDate = $nextDate->addDays(1);
            }

            if ($nextDate->lte($endingDate)) {
                $possibleDateResults[$this->formatLabelByDate($nextDate, $unit)] = 0;
            }
        }

        return $possibleDateResults;
    }

    /**
     * Get the expressions for the query based on the given driver
     *
     * @param string $driver
     * @param string $column
     * @param string $unit
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function determineAggragateExpression($driver, $column, $unit): string
    {
        return match ($driver) {
            'mysql', 'mariadb' => $this->determineAggragateExpressionWhenMysqlOrMariaDB($unit, $column),
            'pgsql' => $this->determineAggragateExpressionWhenPostgre($unit, $column),
            default => throw new InvalidArgumentException('Some of the charts are not supported for this database driver.')
        };
    }

    /**
     * Determine the expression when the driver is mysql or mariadb
     *
     * @param string $unit
     * @param string $column
     *
     * @return string
     */
    protected function determineAggragateExpressionWhenMysqlOrMariaDB($unit, $column): string
    {
        return match ($unit) {
            'month' => "DATE_FORMAT({$column}, '%Y-%m')",
            'week'  => "DATE_FORMAT({$column}, '%x-%v')",
            'day'   => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }

    /**
     * Determine the expression when the driver is postgres
     *
     * @param string $unit
     * @param string $column
     *
     * @return string
     */
    protected function determineAggragateExpressionWhenPostgre($unit, $column): string
    {
        return match ($unit) {
            'month' => "to_char($column, 'YYYY-MM')",
            'week'  => "to_char($column, 'IYYY-IW')",
            'day'   => "to_char($column, 'YYYY-MM-DD')",
        };
    }

    /**
     * The element's component.
     *
     * @var string
     */
    public function component(): string
    {
        return 'progression-chart';
    }
}
