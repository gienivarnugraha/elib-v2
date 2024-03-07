<?php

namespace App\Core\Resources;

use App\Core\Contracts\Presentable;
use App\Core\Criteria\RequestCriteria;
use App\Core\Facades\Format;
use Illuminate\Support\Collection;
use JsonSerializable;

class GlobalSearch implements JsonSerializable
{
    /**
     * Total results
     */
    protected int $take = 5;

    /**
     * Initialize global search for the given resources
     */
    public function __construct(protected Collection $resources)
    {
    }

    /**
     * Get the search result
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $result = new Collection([]);

        $this->resources->reject(fn ($resource) => ! $resource::searchable())
            ->each(function ($resource) use (&$result) {
                $result->push([
                    'title' => $resource->label(),
                    'data' => $this->prepareSearchQuery($resource->repository(), $resource)
                        ->all()
                        ->whereInstanceOf(Presentable::class)
                        ->map(function ($model) use ($resource) {
                            return $this->data($model, $resource);
                        }),
                ]);
            });

        return $result->reject(fn ($result) => $result['data']->isEmpty())->values();
    }

    /**
     * Prepare the search query
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     * @param  \App\Core\Resources\Resource  $resource
     * @return \App\Core\Repository\BaseRepository
     */
    protected function prepareSearchQuery($repository, $resource)
    {
        if ($ownCriteria = $resource->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        return $repository->pushCriteria(RequestCriteria::class)->limit($this->take);
    }

    /**
     * Provide the model data for the response
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \App\Core\Resources\Resource  $resource
     */
    protected function data($model, $resource): array
    {
        return [
            'path' => $model->path,
            'display_name' => $model->display_name,
            'created_at' => Format::dateTime($model->created_at),
            $model->getKeyName() => $model->getKey(),
        ];
    }

    /**
     * Serialize GlobalSearch class
     */
    public function jsonSerialize(): array
    {
        return $this->get()->all();
    }
}
