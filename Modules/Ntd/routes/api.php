<?php

use Illuminate\Support\Facades\Route;
use Modules\Ntd\Http\Controllers\Api\NtdController;

// Route::middleware('auth:sanctum')
//     ->controller(NtdController::class)
//     ->prefix('ntd')
//     ->group(function () {
//         Route::get('/', 'index');
//     });

Route::prefix('ntd')
    ->controller(NtdController::class)
    ->group(function () {
        Route::get('/', 'index');
    });