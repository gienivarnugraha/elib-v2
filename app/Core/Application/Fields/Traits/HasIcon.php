<?php

namespace App\Core\Application\Fields\Traits;

trait HasIcon
{
    /**
     * A custom icon to be incorporated in input group
     */
    public ?string $icon = null;

    /**
     * A custom icon to be incorporated in input group
     */
    public ?string $iconPlacement = null;

    /**
     * Custom input group icon
     *
     * @param  string  $icon icon name
     * @param  bool  $append whether to append or prepend the icon
     */
    public function icon(string $icon, ?string $placement): static
    {
        $this->icon = $icon;
        $this->iconPlacement = $placement;

        return $this;
    }
}
