<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    /**
     * List current user notifications
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->response(
            $request->user()->notifications()->paginate(15)
        );
    }

    /**
     * Retrieve current user notification
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        return $this->response(
            $request->user()->notifications()->findOrFail($id)
        );
    }

    /**
     * Set all notifications for current user as read
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->user()->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->response('', 204);
    }

    /**
     * Delete current user notification
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $request->user()->notifications()
            ->findOrFail($id)
            ->delete();

        return $this->response('', 204);
    }
}
