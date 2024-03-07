<?php

namespace App\Core\Application\Filters\Fields;

use App\Core\Application\Filters\Filter;

class Date extends Filter
{
    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'date';
    }

    /**
     * The WAS operator options
     *
     * @return array
     */
    public function wasOperatorOptions()
    {
        return [
            'yesterday' => __('dates.yesterday'),
            'last_week' => __('dates.last_week'),
            'last_month' => __('dates.last_month'),
            'last_quarter' => __('dates.last_quarter'),
            'last_year' => __('dates.last_year'),
        ];
    }

    /**
     * The IS operator options
     *
     * @return array
     */
    public function isOperatorOptions()
    {
        return [
            'today' => __('dates.today'),
            'next_day' => __('dates.next_day'),
            'this_week' => __('dates.this_week'),
            'next_week' => __('dates.next_week'),
            'this_month' => __('dates.this_month'),
            'next_month' => __('dates.next_month'),
            'this_quarter' => __('dates.this_quarter'),
            'next_quarter' => __('dates.next_quarter'),
            'this_year' => __('dates.this_year'),
            'next_year' => __('dates.next_year'),
            'last_7_days' => __('dates.within.last_7_days'),
            'last_14_days' => __('dates.within.last_14_days'),
            'last_30_days' => __('dates.within.last_30_days'),
            'last_60_days' => __('dates.within.last_60_days'),
            'last_90_days' => __('dates.within.last_90_days'),
            'last_365_days' => __('dates.within.last_365_days'),
        ];
    }
}
