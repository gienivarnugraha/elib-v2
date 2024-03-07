<?php

namespace App\Core\Application\Table;

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;

class LengthAwarePaginator extends BaseLengthAwarePaginator
{
    /**
     * @var integer
     */
    protected int $allTimeTotal = 0;

    /**
     * Set the all time total
     *
     * @param integer $total
     *
     * @return static
     */
    public function setAllTimeTotal(int $total): static
    {
        $this->allTimeTotal = $total;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'all_time_total' => $this->allTimeTotal,
        ]);
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
