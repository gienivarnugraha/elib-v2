<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\OrderRepository;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Core\Facades\Application;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use App\Http\Resources\MediaResource;
use Illuminate\Database\Query\Builder;
use App\Http\Resources\RevisionResource;
use Illuminate\Support\Facades\Validator;
use App\Core\Http\Controllers\ApiController;
use App\Contracts\Repositories\RevisionRepository;
use App\Http\Resources\OrderResource;

class OrderController extends ApiController
{
    /**
     * Initialize new DealStatusController instance
     */
    public function __construct(protected OrderRepository $repository)
    {
    }

    public function show(Media $media, Request $request)
    {
        Validator::make($request->all(), [
            'uuid' => [
                'required',
                Rule::exists('media', 'uuid'),
            ],
            'passcode' => 'required',
        ])->validate();

        $pdf = $media->findByUuid($request->uuid);

        if (Auth::user()->isSuperAdmin()) {
            $result = $pdf->addWatermark();
            return response()->file($result->getPath());
        }

        try {
            $pdf->validate($request->passcode);

            $result = $pdf->addWatermark();

            return response()->file($result->getPath());
        } catch (\Throwable $th) {
            return $this->response([
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function getPasscode($order)
    {
        $order->passcode = fake()->regexify('[a-zA-Z0-9]{6}');
        $order->date_from = Carbon::now()->format('Y-m-d');
        $order->date_to = Carbon::now()->addMonth()->format('Y-m-d');
        // $order->confirmed_at = Carbon::now()->format('Y-m-d');
        $order->save();
    }

    public function generate(Request $request)
    {
        $order = $this->repository->find($request->id);
        $this->getPasscode($order);

        return new OrderResource($order);
    }


    public function confirm(Request $request)
    {
        $order = $this->repository->find($request->id);

        $order->is_confirmed = $request->confirmed;
        $this->getPasscode($order);

        return new OrderResource($order);
    }
}
