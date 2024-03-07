<?php

namespace App\Core\Contracts\Fields;

interface TracksMorphManyModelAttributes
{
    /**
     * Get the attributes the changes should be tracked on
     */
    public function trackAttributes(): array|string;
}
