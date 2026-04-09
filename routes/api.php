<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('whatsapp-widget')->group(function () {
    Route::get('/config', [\App\Http\Controllers\Api\WhatsAppWidgetController::class, 'config']);
    Route::post('/click', [\App\Http\Controllers\Api\WhatsAppWidgetController::class, 'trackClick'])->middleware('throttle:10,1');
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [\App\Http\Controllers\Api\AuthController::class, 'createToken'])->middleware('throttle:5,1');

    // Chatbot API Endpoints
    Route::prefix('chatbot')->middleware('throttle:60,1')->group(function () {
        Route::post('/chat', [\App\Http\Controllers\Api\ChatbotController::class, 'chat']);
        Route::post('/feedback', [\App\Http\Controllers\Api\ChatbotController::class, 'feedback']);
        Route::post('/handoff', [\App\Http\Controllers\Api\ChatbotController::class, 'handoff']);
    });

    // WhatsApp Widget Endpoints
    Route::prefix('whatsapp-widget')->group(function () {
        Route::get('/config', [\App\Http\Controllers\Api\WhatsAppWidgetController::class, 'config']);
        Route::post('/click', [\App\Http\Controllers\Api\WhatsAppWidgetController::class, 'trackClick'])->middleware('throttle:10,1');
    });

    Route::middleware(['api.token', 'api.ratelimit'])->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::delete('/auth/token', [\App\Http\Controllers\Api\AuthController::class, 'revokeCurrent']);

        Route::post('/verifications/nin', [\App\Http\Controllers\Api\VerificationController::class, 'verifyNin'])->middleware('kyc.enforce');
        Route::post('/verifications/bvn', [\App\Http\Controllers\Api\VerificationController::class, 'verifyBvn'])->middleware('kyc.enforce');
        Route::get('/verifications/{id}', [\App\Http\Controllers\Api\VerificationController::class, 'getResult']);

        Route::prefix('vuvaa')->middleware('kyc.enforce')->group(function () {
            Route::post('/create_user', [\App\Http\Controllers\Api\VuvaaController::class, 'createUser']);
            Route::post('/login', [\App\Http\Controllers\Api\VuvaaController::class, 'login']);
            Route::post('/verify_nin', [\App\Http\Controllers\Api\VuvaaController::class, 'verifyNin']);
            Route::post('/in_person_verification', [\App\Http\Controllers\Api\VuvaaController::class, 'inPersonVerification']);
            Route::post('/share_code', [\App\Http\Controllers\Api\VuvaaController::class, 'shareCode']);
            Route::post('/requery', [\App\Http\Controllers\Api\VuvaaController::class, 'requery']);
            Route::post('/wallet', [\App\Http\Controllers\Api\VuvaaController::class, 'wallet']);
            Route::post('/transaction_history', [\App\Http\Controllers\Api\VuvaaController::class, 'transactionHistory']);
            Route::post('/reasons', [\App\Http\Controllers\Api\VuvaaController::class, 'reasons']);
        });

        Route::get('/legal/catalog', [\App\Http\Controllers\Api\LegalCatalogController::class, 'index']);
        Route::get('/legal/catalog/{documentType}', [\App\Http\Controllers\Api\LegalCatalogController::class, 'show']);
        Route::get('/legal/pricing/{documentType}', [\App\Http\Controllers\Api\LegalCatalogController::class, 'pricing']);

        Route::prefix('vtu')->middleware('kyc.enforce')->group(function () {
            Route::post('/airtime', [\App\Http\Controllers\Api\VtuApiController::class, 'airtime']);
            Route::post('/data', [\App\Http\Controllers\Api\VtuApiController::class, 'data']);
            Route::post('/cable', [\App\Http\Controllers\Api\VtuApiController::class, 'cable']);
            Route::post('/electricity', [\App\Http\Controllers\Api\VtuApiController::class, 'electricity']);
        });
    });
});
