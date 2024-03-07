<?php

namespace App\Core\Contracts;

interface Countable
{
    /**
     * Set that the class should count
     *
     * @return self
     */
    public function count(): static;

    /**
     * Check whether the class counts
     */
    public function counts(): bool;

    /**
     * Get the count key
     */
    public function countKey(): string;
}
