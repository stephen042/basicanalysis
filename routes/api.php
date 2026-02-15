<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\User\TradingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/create-account', [ApiAuthController::class, 'register']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Trading API Routes
Route::middleware('auth:sanctum')->prefix('trading')->group(function () {
    // Real-time Data
    Route::get('asset-price/{asset}', [TradingController::class, 'getAssetPrice']);
    Route::get('assets', [TradingController::class, 'getAssets']);
    
    // Trade Operations
    Route::post('place-trade', [TradingController::class, 'placeTrade']);
    Route::post('close-trade/{trade}', [TradingController::class, 'closeTrade']);
    Route::post('cancel-trade/{trade}', [TradingController::class, 'cancelTrade']);
    
    // Trade Information
    Route::get('active-trades', [TradingController::class, 'activeTrades']);
    Route::get('trade-history', [TradingController::class, 'tradeHistory']);
    Route::get('trade/{trade}', [TradingController::class, 'getTrade']);
    
    // Balance Management
    Route::get('balance', [TradingController::class, 'balance']);
    Route::post('deposit', [TradingController::class, 'deposit']);
    Route::post('withdraw', [TradingController::class, 'withdraw']);
    
    // Demo Mode
    Route::post('toggle-demo', [TradingController::class, 'toggleDemoMode']);
    Route::get('demo-status', [TradingController::class, 'getDemoStatus']);
});