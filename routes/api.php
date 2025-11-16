<?php

use App\Http\Controllers\Api\ExpositionController;
use App\Http\Controllers\ExpositionLayoutController;
use Illuminate\Support\Facades\Route;

Route::get('/expositions/{exposition}', [ExpositionController::class, 'show'])
    ->name('api.expositions.show');

// TODO: restore auth middleware once Unity client can send Sanctum tokens
Route::put('/expositions/{exposition}/layout', [ExpositionLayoutController::class, 'update'])
    ->name('api.expositions.layout');

// test route
Route::get('/expositions/{exposition}/layout', function (\App\Models\Exposition $exposition) {
    $exposition->load(['tiles', 'exhibits']);

    return response()->json([
        'exposition' => $exposition,
    ]);
});