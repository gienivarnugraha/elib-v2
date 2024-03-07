<?php

namespace App\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Core\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserRepository;

class UserAvatarController extends ApiController
{
    /**
     * Upload user avatar
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Eloquent\UserEloquent $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UserRepository $repository)
    {
        $request->validate([
            'avatar' => 'required|image|max:1024',
        ]);

        $user = $repository->storeAvatar($request->user(), $request->file('avatar'));

        return $this->response(new UserResource($repository->withResponseRelations()->find($user->id)));
    }

    /**
     * Delete the user avatar
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Eloquent\UserEloquent $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, UserRepository $repository)
    {
        $user = $repository->removeAvatarImage($request->user())->update(['avatar' => null], $request->user()->id);

        return $this->response(new UserResource($repository->withResponseRelations()->find($user->id)));
    }
}
