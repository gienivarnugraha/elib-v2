<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Core\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserRepository;
use App\Http\Resources\UserResource;

class OwnedController extends ApiController
{
  public function __construct(protected UserRepository $repository)
  {
  }

  public function get()
  {
    $related = $this->repository->find(Auth::id());

    $related->load('documents.revisions.media');
    $related->load('revisions.media');

    return $this->response(new UserResource($related));
  }
}
