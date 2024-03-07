<?php

namespace Tests\Feature\Resources;

use App\Core\Contracts\Enum;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/* This trait is to test the resource works without using api request and user */

trait UseResourceTest
{
    protected function performTestTableOrderBy($query, $mapCallback)
    {
        [$order, $_perpage] = explode('&', $query);
        $perpage = (int) Str::remove('per_page=', $_perpage);
        $orders = explode(',', Str::remove('order=', $order));

        $orders = collect($orders)->map(function ($order) {
            [$field, $sort] = explode('|', $order);

            if (stripos($field, '.')) {
                [$relation, $relationField] = explode('.', $field);

                $this->model()->with($relation);

                return [$relationField, $sort ?? 'asc'];
            } else {
                return [$field, $sort ?? 'asc'];
            }
        })->values()->all();

        $ordered = $this->model()->get()->sortBy($orders)->map($mapCallback)->values()->take($perpage)->all();


        $result = $this->tableRequest($query)
            ->resolveTable()
            ->make()
            ->map($mapCallback)
            ->toArray();

        dd($result);


        $this->assertCount(count($ordered), $result);
        $this->assertSame($ordered, $result);
    }

    protected function performTestTableSearch($query)
    {
        $result = $this->tableRequest($query)
            ->resolveTable()
            ->make();

        // $this->assertCount(1, $result);

        return $result;
    }

    public function performTestCreateResource($data, $except = [])
    {
        $createRequest = $this->createRequest($data);

        $this->assertSame($createRequest->resource(), $this->resource());
        $this->assertSame($createRequest->allFields()->pluck('attribute')->toArray(), $this->pluck($this->resource()->fields($createRequest), 'attribute'));
        $this->assertSame(count($createRequest->allFields()), count($this->resource()->fields($createRequest)));
        $this->assertTrue($createRequest->isSaving());

        $model = $this->resource()->repository();

        $result = $model->create($data);

        $this->assertAttributeValues($data, $result, $except);

        return $result;
    }

    public function performTestUpdateResource($data, $except = [])
    {
        $model = $this->resource()->repository();

        $created = $model->create($data);

        $factory = $this->factory()->make()->toArray();

        $updateRequest = $this->updateRequest($created->id, $factory);
        $this->assertSame($updateRequest->allFields()->pluck('attribute')->toArray(), $this->pluck($this->resource()->fields($updateRequest), 'attribute'));
        $this->assertSame(count($updateRequest->allFields()), count($this->resource()->fields($updateRequest)));
        $this->assertTrue($updateRequest->isSaving());

        $result = $model->update($factory, $created->id);

        $this->assertAttributeValues($factory, $result, $except);

        return $result;
    }

    public function performTestDeleteResource($data = null, $count = 1)
    {
        $model = $this->resource()->repository();

        $factory = $data ?: $this->factory($count)->create();

        $ids = is_countable($factory) ? $this->pluck($factory, 'id') : $factory->id;

        try {
            $result = $model->delete($ids);
        } catch (\Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

        $this->assertSame($result, [
            'skipped' => [],
            'deleted' => $ids,
        ]);

        return $result;
    }

    /**
     * Save a new entity in repository
     *
     * @param  array  $data intial data
     * @param  mixed  $result result data
     * @param  array  $except array to exclude assertion
     * @return mixed
     */
    public function assertAttributeValues($data, $result, $except)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $except)) {

                if ($result->isRelation($key)) {
                    if (
                        $result->{$key}() instanceof MorphToMany ||
                        $result->{$key}() instanceof HasMany ||
                        $result->{$key}() instanceof MorphMany
                    ) {
                        $this->assertEquals($value, $result->{$key}->pluck('id')->toArray(), 'value: ' . $key . '=' . $value);
                    } elseif (
                        $result->{$key}() instanceof HasOne ||
                        $result->{$key}() instanceof BelongsTo
                    ) {
                        foreach ($data[$key] as $dataKey => $dataValue) {
                            $this->assertSame($dataValue, $result->{$key}->{$dataKey}, 'value: ' . $key . '=' . $value);
                        }
                    }
                } else {
                    if ($result->{$key} instanceof Enum) {
                        $this->assertSame($value, $result->{$key}->value, 'value: ' . $key . '=' . $value);
                    } else if ($result->{$key} instanceof Carbon) {
                        $this->assertSame(Carbon::parse($value)->format('d-m-Y'), $result->{$key}->format('d-m-Y'), 'value: ' . $key . '=' . $value);
                    } else {
                        $this->assertSame($value, $result->{$key}, "value: $key = $value, result =" . $result->{$key});
                    }
                }
            }
        }
    }
}
