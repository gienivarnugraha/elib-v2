<?php

namespace App\Core\Resources;

use App\Core\Contracts\Resources\ResourcefulRequestHandler;
use App\Core\Criteria\OnlyTrashedCriteria;
use App\Core\Criteria\RequestCriteria;
use App\Core\Criteria\WithTrashedCriteria;
use App\Core\Http\Request\ResourcefulRequest;
use App\Core\Repository\AppRepository;
use App\Core\Resources\Traits\AssociatesResources;

class ResourcefulHandler implements ResourcefulRequestHandler
{
    use AssociatesResources;

    /**
     * Indicates whether the trashed records should be queried
     */
    protected bool $withTrashed = false;

    /**
     * Indicates whether the only trashed records should be queried
     */
    protected bool $onlyTrashed = false;

    /**
     * Initialize the resourceful handeler
     *
     * @param  \App\Core\Resources\Http\ResourcefulRequest  $request
     */
    public function __construct(protected ResourcefulRequest $request, protected AppRepository $repository)
    {
    }

    /**
     * Handle the resource index action
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $repository = $this->request->resource()->indexQuery(
            $this->repository->orderBy(
                $this->request->resource()::$orderBy,
                $this->request->resource()::$orderByDir
            )
        );

        return $this->withSoftDeleteCriteria($repository)
            ->pushCriteria(RequestCriteria::class)
            ->paginate($this->getPerPage());
    }

    /**
     * Handle the resource store action
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store()
    {
        return $this->handleAssociatedResources(
            $this->repository->create(
                $this->request->all()
            )
        );
    }

    /**
     * Handle the resource show action
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function show($id)
    {
        $repository = $this->request->resource()->repository();

        return $this->request->resource()->displayQuery(
            $this->withSoftDeleteCriteria($repository)
        )->find($id);
    }

    /**
     * Handle the resource update action
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id)
    {
        return $this->handleAssociatedResources(
            $this->withSoftDeleteCriteria($this->repository)->update(
                $this->request->all(),
                $id
            )
        );
    }

    /**
     * Handle the resource destroy action
     *
     * @param  int  $id
     * @return string
     */
    public function destroy($id)
    {
        $this->withSoftDeleteCriteria($this->repository)->delete($id);

        return '';
    }

    /**
     * Force delete the resource record
     *
     * @param  int  $id
     * @return string
     */
    public function forceDelete($id)
    {
        $this->withSoftDeleteCriteria($this->repository)->forceDelete($id);

        return '';
    }

    /**
     * Restore the soft deleted resource record
     *
     * @param  int  $id
     * @return string
     */
    public function restore($id)
    {
        $this->withSoftDeleteCriteria($this->repository)->restore($id);

        return '';
    }

    /**
     * Query the resource with trashed record
     *
     * @return static
     */
    public function withTrashed()
    {
        $this->withTrashed = true;

        return $this;
    }

    /**
     * Query the resource with only trashed record
     *
     * @return static
     */
    public function onlyTrashed()
    {
        $this->onlyTrashed = true;

        return $this;
    }

    /**
     * Apply the soft deletes criteria to the given repository
     *
     * @param  \App\Core\Repository\AppRepository  $repository
     * @return \App\Core\Repository\AppRepository
     */
    protected function withSoftDeleteCriteria($repository)
    {
        if ($this->withTrashed) {
            $repository->pushCriteria(WithTrashedCriteria::class);
        } elseif ($this->onlyTrashed) {
            $repository->pushCriteria(OnlyTrashedCriteria::class);
        }

        return $repository;
    }

    /**
     * Get the number of models to return per page.
     *
     * @return null|int
     */
    protected function getPerPage()
    {
        return $this->request->input('per_page');
    }
}
