<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Facades\Cards;
use Illuminate\Http\Request;
use App\Core\Http\Controllers\ApiController;

class CardController extends ApiController
{
  /**
   * Get cards that are intended to be shown on dashboards
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function forDashboards()
  {
    return $this->response(Cards::resolveForDashboard());
  }

  /**
   * Get the available cards for a given resource
   *
   * @param string $resourceName
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index($resourceName, Request $request)
  {
    return $this->response(Cards::resolve($resourceName));
  }

  /**
   * Get card by given uri key
   *
   * @param string $card
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function show($card)
  {
    return $this->response(Cards::registered()->first(function ($item) use ($card) {
      return $item->uriKey() === $card;
    })->authorizeOrFail());
  }
}
