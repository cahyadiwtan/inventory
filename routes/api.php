<?php

use App\Http\Controllers\Api\BrandApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\TaxApiController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Master Data
    Route::apiResource('categories', CategoryApiController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('brands', BrandApiController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('units', UnitApiController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('warehouses', WarehouseApiController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('taxes', TaxApiController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});
