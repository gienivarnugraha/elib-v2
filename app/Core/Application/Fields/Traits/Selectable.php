<?php

namespace App\Core\Application\Fields\Traits;

trait Selectable
{
    /**
     * Set async URL for searching
     *
     * @param  string  $asyncUrl
     */
    public function async($asyncUrl): static
    {
        $this->withMeta(['asyncUrl' => $asyncUrl]);

        // Automatically add placeholder "Type to search..." on async fields
        $this->withMeta(['placeholder' => 'Type to search...']);

        return $this;
    }
}
