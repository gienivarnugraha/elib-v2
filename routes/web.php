<?php

use App\Core\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes(['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]);

Route::get('/{vue}', ApplicationController::class)->where('vue', '[\/\w\.-]*')->name('vue');
