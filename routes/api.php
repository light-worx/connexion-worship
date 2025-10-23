<?php

use Illuminate\Support\Facades\Route;
use Modules\Worship\Http\Controllers\WorshipController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('worships', WorshipController::class)->names('worship');
});
