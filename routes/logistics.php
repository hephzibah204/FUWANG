<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Logistics\LogisticsAuthController;
use App\Http\Controllers\PublicLogisticsController;
use App\Http\Controllers\UserLogisticsController;

Route::prefix('logistics')->name('logistics.')->group(function () {
    Route::get('/', [PublicLogisticsController::class, 'index'])->name('home');
    Route::post('/track', [PublicLogisticsController::class, 'track'])->name('track');
    Route::get('/states', [\App\Http\Controllers\LogisticsCentersController::class, 'states'])->name('states');
    Route::get('/centers', [\App\Http\Controllers\LogisticsCentersController::class, 'index'])->name('centers');
    Route::post('/pricing/quote', [\App\Http\Controllers\LogisticsPricingController::class, 'quote'])->name('pricing.quote');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [LogisticsAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LogisticsAuthController::class, 'login'])
            ->middleware('throttle:5,1');
        Route::get('/register', [LogisticsAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [LogisticsAuthController::class, 'register'])
            ->middleware('throttle:5,1');
    });

    Route::post('/sso', [LogisticsAuthController::class, 'ssoLogin'])
        ->middleware(['auth', 'throttle:10,1'])
        ->name('sso');

    Route::post('/logout', [LogisticsAuthController::class, 'logout'])->name('logout');

    Route::get('/landing', function () {
        return redirect('/logistics', 301);
    })->name('landing');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', [UserLogisticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/book', [UserLogisticsController::class, 'book'])->name('book');
        Route::post('/book', [UserLogisticsController::class, 'store'])->middleware('kyc.enforce')->name('store');
    });

    Route::prefix('agent')->name('agent.')->middleware(['feature:logistics', 'auth', 'verified', 'delivery_agent'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('logistics.agent.dashboard');
        })->name('home');

        Route::get('/dashboard', [\App\Http\Controllers\LogisticsAgent\AgentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/orders', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/accept', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'accept'])->name('orders.accept');
        Route::post('/orders/{order}/decline', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'decline'])->name('orders.decline');
        Route::post('/orders/{order}/status', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'updateStatus'])->name('orders.status');

        Route::get('/earnings', [\App\Http\Controllers\LogisticsAgent\AgentEarningsController::class, 'index'])->name('earnings.index');

        Route::post('/availability', [\App\Http\Controllers\LogisticsAgent\AgentOrdersController::class, 'updateAvailability'])->name('availability');
    });

    Route::prefix('ops')->name('ops.')->group(function () {
        Route::get('/login', [\App\Http\Controllers\LogisticsOps\StaffAuthController::class, 'showLogin'])->middleware('guest:logistics_staff')->name('login');
        Route::post('/login', [\App\Http\Controllers\LogisticsOps\StaffAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
        Route::post('/logout', [\App\Http\Controllers\LogisticsOps\StaffAuthController::class, 'logout'])->name('logout');

        Route::middleware(['feature:logistics_ops', 'auth:logistics_staff'])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\LogisticsOps\DashboardController::class, 'index'])->name('dashboard');
            Route::post('/stop-impersonation', [\App\Http\Controllers\LogisticsOps\StaffAuthController::class, 'stopImpersonation'])->name('stop_impersonation');

            Route::get('/orders', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'create'])->middleware('logistics.permission:logistics.orders.create')->name('orders.create');
            Route::post('/orders', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'store'])->middleware('logistics.permission:logistics.orders.create')->name('orders.store');
            Route::get('/orders/{order}/edit', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'edit'])->name('orders.edit');
            Route::put('/orders/{order}', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'update'])->name('orders.update');
            Route::post('/orders/{order}/status', [\App\Http\Controllers\LogisticsOps\OrdersController::class, 'updateStatus'])->middleware('logistics.permission:logistics.orders.update_status')->name('orders.status');

            Route::get('/shipments', [\App\Http\Controllers\LogisticsOps\ShipmentsController::class, 'index'])->middleware('logistics.permission:logistics.shipments.monitor')->name('shipments.index');
            Route::put('/shipments/{shipment}', [\App\Http\Controllers\LogisticsOps\ShipmentsController::class, 'update'])->middleware('logistics.permission:logistics.shipments.monitor')->name('shipments.update');

            Route::get('/agents', [\App\Http\Controllers\LogisticsOps\AgentsController::class, 'index'])->middleware('logistics.permission:logistics.agents.view')->name('agents.index');
            Route::put('/agents/{agent}', [\App\Http\Controllers\LogisticsOps\AgentsController::class, 'update'])->middleware('logistics.permission:logistics.agents.onboard')->name('agents.update');

            Route::get('/centers', [\App\Http\Controllers\LogisticsOps\CentersController::class, 'index'])->middleware('logistics.permission:logistics.centers.manage')->name('centers.index');
            Route::post('/centers', [\App\Http\Controllers\LogisticsOps\CentersController::class, 'store'])->middleware('logistics.permission:logistics.centers.manage')->name('centers.store');
            Route::put('/centers/{center}', [\App\Http\Controllers\LogisticsOps\CentersController::class, 'update'])->middleware('logistics.permission:logistics.centers.manage')->name('centers.update');
            Route::delete('/centers/{center}', [\App\Http\Controllers\LogisticsOps\CentersController::class, 'destroy'])->middleware('logistics.permission:logistics.centers.manage')->name('centers.destroy');

            Route::get('/inventory', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'index'])->middleware('logistics.permission:logistics.inventory.view')->name('inventory.index');
            Route::get('/inventory/create', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'create'])->middleware('logistics.permission:logistics.inventory.manage')->name('inventory.create');
            Route::post('/inventory', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'store'])->middleware('logistics.permission:logistics.inventory.manage')->name('inventory.store');
            Route::get('/inventory/{item}/edit', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'edit'])->middleware('logistics.permission:logistics.inventory.manage')->name('inventory.edit');
            Route::put('/inventory/{item}', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'update'])->middleware('logistics.permission:logistics.inventory.manage')->name('inventory.update');
            Route::delete('/inventory/{item}', [\App\Http\Controllers\LogisticsOps\InventoryController::class, 'destroy'])->middleware('logistics.permission:logistics.inventory.manage')->name('inventory.destroy');

            Route::get('/analytics', [\App\Http\Controllers\LogisticsOps\AnalyticsController::class, 'index'])->middleware('logistics.permission:logistics.analytics.view')->name('analytics.index');
        });
    });
});
