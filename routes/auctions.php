<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicAuctionController;
use App\Http\Controllers\UserAuctionController;
use App\Http\Controllers\Auth\Auctions\AuctionsAuthController;

Route::prefix('auction')->group(function () {
    Route::get('/', [PublicAuctionController::class, 'index'])
        ->middleware('track.view:public_auctions_index')
        ->name('public.auctions.index');

    Route::get('/{lotCode}', [PublicAuctionController::class, 'show'])
        ->middleware('track.view:public_auction_detail')
        ->name('public.auctions.show');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuctionsAuthController::class, 'showLoginForm'])->name('auction.login');
        Route::post('/login', [AuctionsAuthController::class, 'login'])->middleware('throttle:5,1');
        Route::get('/register', [AuctionsAuthController::class, 'showRegisterForm'])->name('auction.register');
        Route::post('/register', [AuctionsAuthController::class, 'register']);
    });

    Route::post('/logout', [AuctionsAuthController::class, 'logout'])->name('auction.logout');

    Route::middleware(['auth', 'verified', 'feature:auctions'])->group(function () {
        Route::get('/dashboard', [UserAuctionController::class, 'dashboard'])->name('auction.dashboard');
        Route::post('/bid', [UserAuctionController::class, 'placeBid'])->middleware('kyc.enforce')->name('auction.bid');
        Route::post('/watchlist/add', [UserAuctionController::class, 'addToWatchlist'])->name('auction.watchlist.add');
        Route::post('/watchlist/remove', [UserAuctionController::class, 'removeFromWatchlist'])->name('auction.watchlist.remove');
    });

    Route::prefix('admin')->name('auction.admin.')->group(function () {
        Route::get('/login', function () {
            return redirect()->route('admin.login');
        })->name('login');

        Route::middleware(['auth:admin', 'admin.audit'])->group(function () {
            Route::get('/', function () {
                return redirect()->route('admin.auctions.index');
            })->name('dashboard');
        });
    });
});

