<?php

namespace App\Core\Application\Charts;


use App\Core\Application\Date\Carbon;
use App\Core\Repository\BaseRepository;

abstract class Presentation extends Chart
{
    /**
     * Indicates whether the displayed values must be cast to integer
     *
     * @var boolean
     */
    public bool $onlyInteger = true;

    /**
     * Axis X Offset
     *
     * @var integer
     */
    public int $axisXOffset = 30;

    /**
     * Axis Y Offset
     *
     * @var integer
     */
    public int $axisYOffset = 20;

    /**
     * Indicates whether the cart is horizontal
     *
     * @var boolean
     */
    public bool $horizontal = false;

    /**
     * Rounding precision.
     *
     * @var integer
     */
    public int $roundingPrecision = 0;

    /**
     * Rounding mode.
     *
     * @var integer
     */
    public int $roundingMode = PHP_ROUND_HALF_UP;

    /**
     * Hold values if the result is queried by date
     *
     * @var null
     */
    public $queryByDate = null;

    /**
     * Count by days
     *
     * @param string $dateColumn
     *
     * @return static
     */
    public function byDays($dateColumn): static
    {
        $this->queryByDate = [$dateColumn, self::BY_DAYS];

        return $this;
    }

    /**
     * Count by weeks
     *
     * @param string $dateColumn
     *
     * @return static
     */
    public function byWeeks($dateColumn): static
    {
        $this->queryByDate = [$dateColumn, self::BY_WEEKS];

        return $this;
    }

    /**
     * Count by months
     *
     * @param string $dateColumn
     *
     * @return static
     */
    public function byMonths($dateColumn): static
    {
        $this->queryByDate = [$dateColumn, self::BY_MONTHS];

        return $this;
    }

    /**
     * Return a presentation result showing the segments of a count aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string $groupBy
     * @param string|null $column
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function count($request, $repository, $groupBy, $column = null, $callback = null)
    {
        return $this->aggregate($request, $repository, 'count', $column, $groupBy, $callback);
    }

    /**
     * Return a presentation result showing the segments of an average aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string|null $column
     * @param string $groupBy
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function average($request, $repository, $column, $groupBy, $callback = null)
    {
        return $this->aggregate($request, $repository, 'avg', $column, $groupBy, $callback);
    }

    /**
     * Return a presentation result showing the segments of a sum aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string|null $column
     * @param string $groupBy
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function sum($request, $repository, $column, $groupBy, $callback = null)
    {
        return $this->aggregate($request, $repository, 'sum', $column, $groupBy, $callback);
    }

    /**
     * Return a presentation result showing the segments of a max aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string|null $column
     * @param string $groupBy
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function max($request, $repository, $column, $groupBy, $callback = null)
    {
        return $this->aggregate($request, $repository, 'max', $column, $groupBy, $callback);
    }

    /**
     * Return a presentation result showing the segments of a min aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string|null $column
     * @param string $groupBy
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function min($request, $repository, $column, $groupBy, $callback = null)
    {
        return $this->aggregate($request, $repository, 'min', $column, $groupBy, $callback);
    }

    /**
     * Return a presentation result showing the segments of a aggregate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     * @param string $function
     * @param string $column
     * @param string $groupBy
     * @param \Closure $callback query callback
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    protected function aggregate($request, $repository, $function, $column, $groupBy, $callback)
    {
        $repository = $repository instanceof BaseRepository ? $repository : resolve($repository);

        $results = $repository->scopeQuery(function ($query) use ($request, $column, $function, $groupBy, $callback) {
            $wrappedColumn = $query->getQuery()->getGrammar()->wrap(
                $column ?? $query->getModel()->getQualifiedKeyName()
            );

            $wrappedGroupBy = $query->getQuery()->getGrammar()->wrap($groupBy);

            $query = $query->selectRaw("{$function}({$wrappedColumn}) as aggregate, {$wrappedGroupBy}");

            if ($this->queryByDate) {
                list($dateColumn, $unit) = $this->queryByDate;

                $startingDate = $this->getStartingDate($this->getCurrentRange($request), $unit);
                $endingDate = Carbon::asAppTimezone();
                $query->whereBetween($dateColumn, [$startingDate, $endingDate]);
            }

            return $callback ? $callback($query) : $query;
        })->groupBy($groupBy)->get();

        return $this->result($results->mapWithKeys(function ($result) use ($groupBy) {
            return $this->formatResult($result, $groupBy);
        })->all());
    }

    /**
     * Format the aggregate result for the presentation.
     *
     * @param \Illuminate\Database\Eloquent\Model $result
     * @param string $groupBy
     *
     * @return array
     */
    protected function formatResult($result, $groupBy): array
    {
        $key = $result->{last(explode('.', $groupBy))};

        return [$key => round($result->aggregate, 0)];
    }

    /**
     * Create a new presentation result.
     *
     * @param array $value
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    public function result(array $value): ChartResult
    {
        return new ChartResult(collect($value)->map(function ($number) {
            return round($number, $this->roundingPrecision, $this->roundingMode);
        })->toArray());
    }

    /**
     * The element's component.
     *
     * @var string
     */
    public function component(): string
    {
        return 'presentation-chart';
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'horizontal'  => $this->horizontal,
                'onlyInteger' => $this->onlyInteger,
                'axisYOffset' => $this->axisYOffset,
                'axisXOffset' => $this->axisXOffset,
            ]
        );
    }
}
