<?php

use Illuminate\Support\Facades\Route;
use Modules\Worship\Http\Controllers\WorshipController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('worships', WorshipController::class)->names('worship');
});
