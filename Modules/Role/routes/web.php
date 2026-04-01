<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;

Route::middleware(['web','auth'])->prefix('/role')->name('role.')->group(function(){
    Route::get('/', [RoleController::class,'index'])->name('index');
});
