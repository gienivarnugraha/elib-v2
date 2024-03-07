<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AircraftController;
use App\Core\Http\Controllers\Api\CardController;
use App\Core\Http\Controllers\Api\FieldController;
use App\Core\Http\Controllers\Api\TableController;
use App\Core\Http\Controllers\Api\UserAvatarController;
use App\Http\Controllers\Api\RevisionController;
use App\Core\Http\Controllers\Api\ResourcefulController;
use App\Core\Http\Controllers\Api\GlobalSearchController;
use App\Core\Http\Controllers\Api\NotificationController;
use App\Core\Http\Controllers\Api\ResourceSearchController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OwnedController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {


    Route::get('/owned', [OwnedController::class, 'get']);

    Route::post('/orders/show', [OrderController::class, 'show']);
    Route::post('/orders/generate', [OrderController::class, 'generate']);
    Route::post('/orders/confirm', [OrderController::class, 'confirm']);


    // Cards controller
    Route::get('/cards', [CardController::class, 'forDashboards']);
    Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::get('/{resource}/cards/', [CardController::class, 'index']);

    // Revisions
    Route::get('/revisions/{type}/{id}', [RevisionController::class, 'get']);
    Route::post('/revisions/{id}/upload', [RevisionController::class, 'upload']);
    Route::put('/revisions/{id}/close', [RevisionController::class, 'close']);
    Route::put('/revisions/{id}/cancel', [RevisionController::class, 'cancel']);

    // The {user} is not yet used.
    Route::post('/users/avatar', [UserAvatarController::class, 'store']);
    Route::delete('/users/avatar', [UserAvatarController::class, 'delete']);

    // Search
    Route::get('/search', [GlobalSearchController::class, 'handle']);

    // Tables
    Route::get('/{resource}/table', [TableController::class, 'index']);
    Route::get('/{resource}/table/settings', [TableController::class, 'settings']);

    // Fields
    Route::get('/{resource}/{resourceId}/update-fields', [FieldController::class, 'update']);
    Route::get('/{resource}/create-fields', [FieldController::class, 'create']);

    // Notifications routes
    Route::apiResource('notifications', NotificationController::class)->except(['store', 'update']);
    Route::put('/notifications', [NotificationController::class, 'update']);

    // Resources Search
    // Route::get('/{resource}/{resourceId}/{associated}', AssociationsController::class);
    Route::get('/{resource}/search', [ResourceSearchController::class, 'handle']);

    // Resources
    Route::get('/{resource}', [ResourcefulController::class, 'index']);
    Route::get('/{resource}/{resourceId}', [ResourcefulController::class, 'show']);
    Route::post('/{resource}', [ResourcefulController::class, 'store']);
    Route::put('/{resource}/{resourceId}', [ResourcefulController::class, 'update']);
    Route::delete('/{resource}/{resourceId}', [ResourcefulController::class, 'destroy']);
});
