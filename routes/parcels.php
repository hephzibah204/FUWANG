<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parcels\AgentDashboardController;
use App\Http\Controllers\Parcels\DropOffController;
use App\Http\Controllers\Parcels\PickupController;

use App\Http\Controllers\Parcels\AgentOnboardingController;

Route::prefix('parcels-agent')->name('parcels.')->middleware(['web', 'auth', 'parcel.agent'])->group(function () {
    
    Route::get('/register', [AgentOnboardingController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AgentOnboardingController::class, 'submitRegistration'])->name('register.submit');

    Route::get('/dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['throttle:60,1'])->group(function() {
        Route::get('/dropoff/customer', [DropOffController::class, 'showCustomerDropOff'])->name('dropoff.customer');
        Route::post('/dropoff/customer', [DropOffController::class, 'processCustomerDropOff'])->name('dropoff.customer.process');

        Route::get('/dropoff/driver', [DropOffController::class, 'showDriverDropOff'])->name('dropoff.driver');
        Route::post('/dropoff/driver', [DropOffController::class, 'processDriverDropOff'])->name('dropoff.driver.process');

        Route::get('/pickup/customer', [PickupController::class, 'showCustomerPickup'])->name('pickup.customer');
        Route::post('/pickup/customer', [PickupController::class, 'processCustomerPickup'])->name('pickup.customer.process');
        Route::post('/pickup/customer/reject', [PickupController::class, 'processCustomerReject'])->name('pickup.customer.reject');

        Route::get('/pickup/driver', [PickupController::class, 'showDriverPickup'])->name('pickup.driver');
        Route::post('/pickup/driver', [PickupController::class, 'processDriverPickup'])->name('pickup.driver.process');
    });

});
