<?php

// Admin routes goes here

// For example:
// AdminModule::register( \Arbory\Base\Http\Controllers\Admin\UsersController::class );

Admin::modules()->register(\App\Http\Controllers\Admin\ReservationsController::class, function() {
    Route::get('/', App\Http\Controllers\Admin\ReservationsController::class . '@index')->name('index');
    Route::get('/invoice', App\Http\Controllers\Admin\ReservationsController::class . '@invoice')->name('invoice');
});
Admin::modules()->register( App\Http\Controllers\Admin\ItemsController::class );