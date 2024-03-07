<?php

namespace App\Core\Application\Cards;


use Illuminate\Support\Collection;
use App\Core\Application\Cards\Card;

class Manager
{
    /**
     * All registered resources cards
     *
     * @var array
     */
    protected array $cards = [];

    /**
     * Register resource cards
     *
     * @param string|array $resource resource name e.q. contacts|companies
     * @param mixed         @provider
     *
     * @return static
     */
    public function register(string|array $resource, mixed $provider): static
    {
        $name = $resource;

        if (is_array($name)) {
            $name = $resource['name'];
        }

        if (isset($this->cards[$name])) {
            $this->cards[$name]['providers'][] = $provider;
        } else {
            $this->cards[$name] = [
                'as'        => $resource['as'] ?? $name,
                'providers' => [$provider],
            ];
        }

        return $this;
    }

    /**
     * Resolves cards for a given resource
     *
     * @param string $resourceName
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolve(string $resourceName): Collection
    {
        return $this->forResource($resourceName)->filter->authorizedToSee()
            ->reject(fn ($card) => $card->onlyOnDashboard === true)->values();
    }

    /**
     * Resolve cards for dashboard
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveForDashboard(): Collection
    {
        return $this->registered()->filter->authorizedToSee()
            ->reject(fn ($card) => $card->onlyOnIndex === true)
            ->values();
    }

    /**
     * Get all registered cards for a given resource
     *
     * @param string $resourceName
     *
     * @return \Illuminate\Support\Collection
     */
    public function forResource(string $resourceName): Collection
    {
        return $this->load($this->cards[$resourceName]);
    }

    /**
     * Get all registerd application cards
     *
     * @return \Illuminate\Support\Collection
     */
    public function registered(): Collection
    {
        return with(new Collection, function ($cards) {
            foreach ($this->cards as $resourceName => $providers) {
                $cards = $cards->merge($this->forResource($resourceName));
            }

            return $cards;
        });
    }

    /**
     * Load the provided cards.
     *
     * @param array $data
     *
     * @return \Illuminate\Support\Collection
     */
    protected function load(array $data): Collection
    {
        $cards     = new Collection;
        $providers = $data['providers'];

        foreach ($providers as $provider) {
            if ($provider instanceof Card) {
                $provider = [$provider];
            }

            if (is_array($provider)) {
                $cards = $cards->merge($provider);
            } elseif (is_callable($provider)) {
                $cards = $cards->merge(call_user_func($provider));
            }
        }

        return $cards;
    }
}
