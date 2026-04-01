<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

Route::middleware(['web','auth'])->prefix('/auth')->name('auth.')->group(function(){
    Route::get('/', [AuthController::class,'index'])->name('index');
});

