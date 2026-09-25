<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\TourController;

$installerEnabled = filter_var(env('INSTALLER_ENABLED', false), FILTER_VALIDATE_BOOL) && app()->environment(['local', 'testing']);
if ($installerEnabled) {
    Route::prefix('install')->name('install.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('index');
        Route::post('/database', [\App\Http\Controllers\InstallController::class, 'setupDatabase'])->name('database');
        Route::get('/admin', [\App\Http\Controllers\InstallController::class, 'adminSetup'])->name('admin');
        Route::post('/admin', [\App\Http\Controllers\InstallController::class, 'storeAdmin'])->name('admin.store');
        Route::get('/complete', [\App\Http\Controllers\InstallController::class, 'complete'])->name('complete');
    });
}

if (app()->environment(['local', 'testing'])) {
    Route::get('/__e2e/payment-harness', function () {
        return view('e2e.payment-harness');
    })->name('e2e.payment_harness');
}

// Debug routes removed for production

// Temporary static landing: set TEMPORARY_STATIC_HOME=false in .env to restore the Laravel home.
$__staticLanding = public_path('index.html');
if (is_file($__staticLanding) && filter_var(env('TEMPORARY_STATIC_HOME', false), FILTER_VALIDATE_BOOL)) {
    Route::get('/', function () use ($__staticLanding) {
        return response()->file($__staticLanding, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    })->name('home');
} else {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->middleware(['ab:home_hero', 'track.view:home'])->name('home');
}
unset($__staticLanding);

Route::get('/services/price-list', [\App\Http\Controllers\PriceListController::class, 'index'])->name('services.price_list');

Route::get('/blog', [BlogController::class, 'index'])->middleware('track.view:blog_index')->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->middleware('track.view:blog_show')->name('blog.show');
Route::get('/pages/{slug}', [PageController::class, 'show'])->middleware('track.view:page_show')->name('pages.show');
Route::get('/sitemap.xml', [FeedController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/feed.xml', [FeedController::class, 'feed'])->name('seo.feed');

Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',   [LoginController::class, 'login'])->middleware('throttle:5,1'); // C-1 brute-force protection

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register',[App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Pre-login public Enrollment Agent Landing & Registration routes
Route::get('/agents', [App\Http\Controllers\AgentLandingController::class, 'index'])->middleware('track.view:agent_landing')->name('agent.landing');
Route::get('/agent/register', [App\Http\Controllers\Agent\AgentRegistrationController::class, 'showForm'])->name('agent.register');
Route::get('/agent/search-preapproved', [App\Http\Controllers\Agent\AgentRegistrationController::class, 'searchPreApproved'])->middleware('throttle:15,1')->name('agent.search_preapproved');
Route::post('/agent/send-claim-otp', [App\Http\Controllers\Agent\AgentRegistrationController::class, 'sendClaimOtp'])->middleware('throttle:5,1')->name('agent.send_claim_otp');
Route::post('/agent/register', [App\Http\Controllers\Agent\AgentRegistrationController::class, 'store'])->name('agent.register.submit');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/2fa/challenge', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');
Route::post('/2fa/challenge', [TwoFactorChallengeController::class, 'verify'])->middleware('throttle:6,1')->name('2fa.verify');
Route::post('/2fa/cancel', [TwoFactorChallengeController::class, 'cancel'])->name('2fa.cancel');
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');

Route::post('/ab/event', [\App\Http\Controllers\AbEventController::class, 'store'])->name('ab.event');
Route::post('/webhooks/verifyme/address', [App\Http\Controllers\Service\VerificationController::class, 'handleAddressWebhook'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->name('webhooks.verifyme.address');

Route::get('/explore', [\App\Http\Controllers\PublicServiceController::class, 'index'])->middleware('track.view:explore_index')->name('public.services.index');
Route::get('/explore/auctions', function () {
    return redirect('/auction', 301);
})->middleware('feature:auctions')->name('public.auctions.index_legacy');
Route::get('/explore/auctions/{lotCode}', function (string $lotCode) {
    return redirect('/auction/' . $lotCode, 301);
})->middleware('feature:auctions')->name('public.auctions.show_legacy');

// Logistics
Route::get('/explore/logistics', function () {
    return redirect('/logistics', 301);
})->middleware('feature:logistics')->name('public.logistics.index_legacy');
Route::post('/explore/logistics/track', function () {
    return redirect('/logistics/track', 307);
})->middleware('feature:logistics')->name('public.logistics.track_legacy');

require __DIR__ . '/logistics.php';
require __DIR__ . '/auctions.php';

if (app()->environment('local')) {
    Route::get('/debug/auctions-summary', function () {
        return response()->json([
            'lots_total' => \App\Models\AuctionLot::count(),
            'lots_scheduled' => \App\Models\AuctionLot::where('status', 'scheduled')->count(),
            'lots_live' => \App\Models\AuctionLot::where('status', 'live')->count(),
            'lots_ended' => \App\Models\AuctionLot::where('status', 'ended')->count(),
            'lots_cancelled' => \App\Models\AuctionLot::where('status', 'cancelled')->count(),
            'sellers_total' => \App\Models\AuctionSeller::count(),
            'bids_total' => \App\Models\AuctionBid::count(),
        ]);
    })->name('debug.auctions.summary');
}

Route::get('/explore/{slug}', [\App\Http\Controllers\PublicServiceController::class, 'show'])
    ->middleware(['ab:service_landing', 'track.view:service_landing'])
    ->name('public.services.show');

Route::get('/email/unsubscribe/{user}/{scope}', [\App\Http\Controllers\EmailPreferenceController::class, 'unsubscribe'])
    ->middleware('signed')
    ->name('email.unsubscribe');

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware('onboarding:dashboard')->name('dashboard');

    // NIN Enrollment Agent Routes
    Route::prefix('agent')->name('agent.')->group(function () {
        // Post-Login Agency KYC Onboarding Portal
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [App\Http\Controllers\Agent\AgentOnboardingController::class, 'showPortal'])->name('index');
            Route::post('/upload-docs', [App\Http\Controllers\Agent\AgentOnboardingController::class, 'uploadDocs'])->name('upload_docs');
            Route::post('/accept-compliance', [App\Http\Controllers\Agent\AgentOnboardingController::class, 'acceptCompliance'])->name('accept_compliance');
        });

        Route::middleware([App\Http\Middleware\EnsureApprovedAgent::class])->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Agent\AgentDashboardController::class, 'index'])->name('dashboard');
            Route::post('/switch-mode', [App\Http\Controllers\Agent\AgentDashboardController::class, 'switchMode'])->name('switch_mode');

            // Agent Issue Resolution Hub
            Route::prefix('issues')->name('issues.')->group(function () {
                Route::get('/', [App\Http\Controllers\Agent\AgentIssueController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Agent\AgentIssueController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Agent\AgentIssueController::class, 'store'])->name('store');
                Route::get('/{id}', [App\Http\Controllers\Agent\AgentIssueController::class, 'show'])->name('show');
                Route::post('/{id}/reply', [App\Http\Controllers\Agent\AgentIssueController::class, 'reply'])->name('reply');
            });
        });
    });

    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');

    // Profile & Security
    Route::get('/profile',  [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/security', [App\Http\Controllers\ProfileController::class, 'security'])->name('profile.security');
    Route::post('/profile/2fa/enable', [App\Http\Controllers\ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [App\Http\Controllers\ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
    Route::get('/profile/activity', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('profile.activity');

    Route::prefix('account/kyc')->name('account.kyc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Account\KycVerificationController::class, 'index'])->name('index');
        Route::middleware('feature:nin_verification')->group(function () {
            Route::get('/nin', [\App\Http\Controllers\Account\KycVerificationController::class, 'ninForm'])->name('nin');
            Route::post('/nin', [\App\Http\Controllers\Account\KycVerificationController::class, 'ninSubmit'])->middleware('kyc.enforce')->name('nin.submit');
        });
        Route::middleware('feature:bvn_verification')->group(function () {
            Route::get('/bvn', [\App\Http\Controllers\Account\KycVerificationController::class, 'bvnForm'])->name('bvn');
            Route::post('/bvn', [\App\Http\Controllers\Account\KycVerificationController::class, 'bvnSubmit'])->middleware('kyc.enforce')->name('bvn.submit');
        });
    });

    // Fuwa.NG AI Chat
    Route::post('/ai/chat', [App\Http\Controllers\AiController::class, 'chat'])->name('ai.chat');

    // Support Tickets
    Route::get('/tickets', [App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [App\Http\Controllers\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [App\Http\Controllers\TicketController::class, 'reply'])->name('tickets.reply');

    Route::get('/realtime/auctions/stream', [App\Http\Controllers\AuctionRealtimeController::class, 'stream'])
        ->name('realtime.auctions.stream');

    // Transaction History
    Route::get('/history', [App\Http\Controllers\TransactionController::class, 'index'])->name('history');
    Route::get('/history/json', [App\Http\Controllers\TransactionController::class, 'json'])->name('history.json');
    Route::get('/history/{transactionId}', [App\Http\Controllers\TransactionController::class, 'show'])->name('history.show');

    // Developer Portal
    Route::get('/developer', [App\Http\Controllers\DeveloperPortalController::class, 'index'])->name('developer.portal');
    Route::get('/developer/docs', [App\Http\Controllers\DeveloperPortalController::class, 'docs'])->name('developer.docs');
    Route::post('/developer/tokens', [App\Http\Controllers\DeveloperPortalController::class, 'createToken'])->name('developer.tokens.create');
    Route::post('/developer/tokens/{id}/revoke', [App\Http\Controllers\DeveloperPortalController::class, 'revokeToken'])->name('developer.tokens.revoke');
    Route::get('/developer/openapi/v1', [App\Http\Controllers\DeveloperPortalController::class, 'openapiV1'])->name('developer.openapi.v1');
    Route::get('/developer/postman/v1', [App\Http\Controllers\DeveloperPortalController::class, 'postmanV1'])->name('developer.postman.v1');

    // Provider Catalog (dynamic UI)
    Route::get('/services/providers/{providerId}/types', [App\Http\Controllers\Service\ProviderCatalogController::class, 'types'])
        ->name('services.providers.types');

    // Manual Funding
    Route::get('/manual-funding/bank', [App\Http\Controllers\FundingController::class, 'bankDetails'])->name('funding.bank');
    Route::post('/manual-funding/submit', [App\Http\Controllers\FundingController::class, 'submitRequest'])->name('funding.submit');

    Route::get('/wallet/fund', [App\Http\Controllers\FundingController::class, 'fundPage'])->name('wallet.fund');
    
    // Payment Gateways
    Route::get('/payment/gateways', [App\Http\Controllers\FundingController::class, 'getActiveGateways'])->name('payment.gateways');
    Route::post('/payment/validate-config', [App\Http\Controllers\FundingController::class, 'validateProviderConfig'])->name('payment.validate_config');
    Route::post('/payment/verify/paystack', [App\Http\Controllers\PaymentVerificationController::class, 'verifyPaystack'])->name('payment.verify.paystack');
    Route::post('/payment/verify/flutterwave', [App\Http\Controllers\PaymentVerificationController::class, 'verifyFlutterwave'])->name('payment.verify.flutterwave');
    Route::post('/payment/verify/monnify', [App\Http\Controllers\PaymentVerificationController::class, 'verifyMonnify'])->name('payment.verify.monnify');
    Route::post('/payment/intents', [App\Http\Controllers\PaymentIntentController::class, 'create'])->middleware('kyc.enforce')->name('payment.intents.create');
    Route::get('/payment/intents', function () {
        return redirect()->route('wallet.fund');
    });
    Route::get('/payment/intents/{reference}', [App\Http\Controllers\PaymentIntentController::class, 'show'])->name('payment.intents.show');
    Route::post('/payment/palmpay/reserve', [App\Http\Controllers\FundingController::class, 'reservePalmpay'])->name('payment.palmpay.reserve');
    Route::post('/payment/auto-funding/ensure', [App\Http\Controllers\FundingController::class, 'ensureAutoFundingAccounts'])->name('payment.auto_funding.ensure');
    Route::post('/payment/auto-funding/regenerate', [App\Http\Controllers\FundingController::class, 'regenerateAutoFundingAccounts'])->name('payment.auto_funding.regenerate');
    Route::get('/payment/virtual-accounts', [App\Http\Controllers\FundingController::class, 'listVirtualAccounts'])->name('payment.virtual_accounts.list');
    Route::match(['GET', 'POST'], '/palmpay.php', [App\Http\Controllers\FundingController::class, 'reservePalmpay'])->name('legacy.palmpay.reserve');

    // â”€â”€ NIN Verification â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:nin_verification')->group(function () {
        Route::get('/services/nin-suite', [App\Http\Controllers\Service\NINController::class, 'suiteIndex'])->name('services.nin.suite');
        Route::get('/services/nin',    [App\Http\Controllers\Service\NINController::class, 'index'])->name('services.nin');
        Route::post('/services/nin/verify', [App\Http\Controllers\Service\NINController::class, 'verify'])->middleware('kyc.enforce')->name('services.nin.verify');
        Route::post('/services/nin-verify/verify', [App\Http\Controllers\Service\NINController::class, 'verify'])->middleware('kyc.enforce')->name('services.nin_verify.verify');
        Route::post('/services/nin/validation/status', [App\Http\Controllers\Service\NINController::class, 'validationStatus'])->middleware('kyc.enforce')->name('services.nin.validation.status');
        
        // NIN Modification
        Route::get('/services/nin/modification', [App\Http\Controllers\Service\NINModificationController::class, 'index'])->name('services.nin.modification');
        Route::post('/services/nin/modification/consent', [App\Http\Controllers\Service\NINModificationController::class, 'acceptConsent'])->name('services.nin.modification.consent');
        Route::post('/services/nin/modification/submit', [App\Http\Controllers\Service\NINModificationController::class, 'submit'])->middleware('kyc.enforce')->name('services.nin.modification.submit');
    });

    // â”€â”€ Identity & Verification Hub â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:identity_verification')->group(function () {
        Route::get('/services/drivers-license', [App\Http\Controllers\Service\DriversLicenseController::class, 'index'])->name('services.drivers_license');
        Route::post('/services/drivers-license/verify', [App\Http\Controllers\Service\DriversLicenseController::class, 'verify'])->middleware('kyc.enforce')->name('services.drivers_license.verify');

        Route::get('/services/biometric', [App\Http\Controllers\Service\BiometricController::class, 'index'])->name('services.biometric_verify');
        Route::post('/services/biometric/verify', [App\Http\Controllers\Service\BiometricController::class, 'verify'])->middleware('kyc.enforce')->name('services.biometric_verify.verify');

        Route::get('/services/stamp-duty', [App\Http\Controllers\Service\VerificationController::class, 'stampDutyIndex'])->name('services.stamp_duty');
        Route::post('/services/stamp-duty/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyStampDuty'])->middleware('kyc.enforce')->name('services.stamp_duty.verify');

        Route::get('/services/plate-number', [App\Http\Controllers\Service\VerificationController::class, 'plateNumberIndex'])->name('services.plate_number');
        Route::post('/services/plate-number/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyPlateNumber'])->middleware('kyc.enforce')->name('services.plate_number.verify');

        Route::get('/services/cac-verify', [App\Http\Controllers\Service\CacController::class, 'index'])->name('services.cac_verify');
        Route::post('/services/cac-verify/verify', [App\Http\Controllers\Service\CacController::class, 'verify'])->middleware('kyc.enforce')->name('services.cac_verify.verify');

        Route::get('/services/tin-verify', [App\Http\Controllers\Service\TinController::class, 'index'])->name('services.tin_verify');
        Route::post('/services/tin-verify/verify', [App\Http\Controllers\Service\TinController::class, 'verify'])->middleware('kyc.enforce')->name('services.tin_verify.verify');

        Route::get('/services/nin-face', [App\Http\Controllers\Service\BiometricController::class, 'index'])->name('services.nin_face');
        Route::post('/services/nin-face/verify', [App\Http\Controllers\Service\BiometricController::class, 'verifyNinFace'])->middleware('kyc.enforce')->name('services.nin_face.verify');

        Route::get('/services/credit-bureau', [App\Http\Controllers\Service\VerificationController::class, 'creditBureauIndex'])->name('services.credit_bureau');
        Route::post('/services/credit-bureau/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyCreditBureau'])->middleware('kyc.enforce')->name('services.credit_bureau.verify');

        Route::get('/services/passport', [App\Http\Controllers\Service\PassportController::class, 'index'])->name('services.passport');
        Route::post('/services/passport/verify', [App\Http\Controllers\Service\PassportController::class, 'verify'])->middleware('kyc.enforce')->name('services.passport.verify');

        Route::get('/services/validation', [App\Http\Controllers\Service\VerificationController::class, 'validationIndex'])->name('services.validation');
        Route::post('/services/validation/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyValidation'])->middleware('kyc.enforce')->name('services.validation.verify');

        Route::get('/services/clearance', [App\Http\Controllers\Service\VerificationController::class, 'clearanceIndex'])->name('services.clearance');
        Route::post('/services/clearance/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyClearance'])->middleware('kyc.enforce')->name('services.clearance.verify');

        Route::get('/services/personalization', [App\Http\Controllers\Service\VerificationController::class, 'personalizationIndex'])->name('services.personalization');
        Route::post('/services/personalization/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyPersonalization'])->middleware('kyc.enforce')->name('services.personalization.verify');

        Route::get('/services/voters-card', [App\Http\Controllers\Service\VerificationController::class, 'votersCardIndex'])->name('services.voters_card');
        Route::post('/services/voters-card/verify', [App\Http\Controllers\Service\VerificationController::class, 'verifyVotersCard'])->middleware('kyc.enforce')->name('services.voters_card.verify');

        Route::get('/services/address-verify', [App\Http\Controllers\Service\VerificationController::class, 'addressIndex'])->name('services.address_verify');
        Route::get('/services/address-verify/all', [App\Http\Controllers\Service\VerificationController::class, 'getAllAddressVerifications'])->name('services.address_verify.all');
        Route::post('/services/address-verify/submit', [App\Http\Controllers\Service\VerificationController::class, 'submitAddressVerification'])->middleware('kyc.enforce')->name('services.address_verify.submit');
        Route::get('/services/address-verify/details/{id}', [App\Http\Controllers\Service\VerificationController::class, 'viewAddressDetails'])->name('services.address_verify.details');
        Route::delete('/services/address-verify/cancel/{id}', [App\Http\Controllers\Service\VerificationController::class, 'cancelAddressVerification'])->name('services.address_verify.cancel');
        Route::post('/services/address-verify/marketplace', [App\Http\Controllers\Service\VerificationController::class, 'fetchAddressByIdentity'])->name('services.address_verify.marketplace');
        Route::get('/services/identity/report/{id}', [App\Http\Controllers\Service\PdfReportController::class, 'verificationReport'])->name('services.identity.report');
            Route::get('/services/identity/nin/slip/{id}/{type}', [App\Http\Controllers\Service\PdfReportController::class, 'ninSlip'])->name('services.nin.slip');
        Route::get('/services/verification/report/{id}', [App\Http\Controllers\Service\PdfReportController::class, 'verificationReport'])->name('services.verification.report');
    });

    // â”€â”€ Legal Hub â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:legal_services')->group(function () {
            Route::get('/services/legal', [App\Http\Controllers\Service\LegalPlatformController::class, 'index'])->name('services.legal');
        Route::get('/services/legal-hub', [App\Http\Controllers\Service\LegalHubController::class, 'index'])->name('services.legal-hub');
        Route::post('/services/legal-hub/draft', [App\Http\Controllers\Service\LegalHubController::class, 'generateDraft'])->middleware('kyc.enforce')->name('services.legal-hub.draft');
        Route::post('/services/legal-hub/finalize', [App\Http\Controllers\Service\LegalHubController::class, 'finalize'])->middleware('kyc.enforce')->name('services.legal-hub.finalize');
    });

    // â”€â”€ BVN Verification â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:bvn_verification')->group(function () {
        Route::get('/services/bvn',    [App\Http\Controllers\Service\BVNController::class, 'index'])->name('services.bvn');
        Route::post('/services/bvn/verify', [App\Http\Controllers\Service\BVNController::class, 'verify'])->middleware('kyc.enforce')->name('services.bvn.verify');
    });

    // â”€â”€ VTU Services (Airtime, Data, Cable, Electricity, Education) â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:vtu_services')->group(function () {
        Route::get('/services/vtu',            [App\Http\Controllers\Service\VTUController::class, 'hubIndex'])->name('services.vtu.hub');
        Route::get('/services/vtu/providers/{serviceType}', [App\Http\Controllers\Service\VTUController::class, 'providers'])->name('services.vtu.providers');

        Route::get('/services/vtu/airtime',     [App\Http\Controllers\Service\VTUController::class, 'airtimeIndex'])->name('services.vtu.airtime');
        Route::post('/services/vtu/airtime/buy', [App\Http\Controllers\Service\VTUController::class, 'buyAirtime'])->middleware('kyc.enforce')->name('services.vtu.airtime.buy');

        Route::get('/services/vtu/data',        [App\Http\Controllers\Service\VTUController::class, 'dataIndex'])->name('services.vtu.data');
        Route::post('/services/vtu/data/buy',    [App\Http\Controllers\Service\VTUController::class, 'buyData'])->middleware('kyc.enforce')->name('services.vtu.data.buy');

        Route::get('/services/vtu/cable',       [App\Http\Controllers\Service\VTUController::class, 'cableIndex'])->name('services.vtu.cable');
        Route::post('/services/vtu/cable/buy',   [App\Http\Controllers\Service\VTUController::class, 'buyCable'])->middleware('kyc.enforce')->name('services.vtu.cable.buy');

        Route::get('/services/vtu/electricity', [App\Http\Controllers\Service\VTUController::class, 'electricityIndex'])->name('services.vtu.electricity');
        Route::post('/services/vtu/electricity/validate', [App\Http\Controllers\Service\VTUController::class, 'validateElectricity'])->name('services.vtu.electricity.validate');
        Route::post('/services/vtu/electricity/buy', [App\Http\Controllers\Service\VTUController::class, 'buyElectricity'])->middleware('kyc.enforce')->name('services.vtu.electricity.buy');

        Route::get('/services/vtu/airtime-to-cash', [App\Http\Controllers\Service\VTUController::class, 'airtimeToCashIndex'])->name('services.vtu.airtime_to_cash');
        Route::post('/services/vtu/airtime-to-cash/submit', [App\Http\Controllers\Service\VTUController::class, 'submitAirtimeToCash'])->middleware('kyc.enforce')->name('services.vtu.airtime_to_cash.submit');

        Route::get('/services/vtu/internet', [App\Http\Controllers\Service\VTUController::class, 'internetIndex'])->name('services.vtu.internet');
        Route::post('/services/vtu/internet/buy', [App\Http\Controllers\Service\VTUController::class, 'buyInternet'])->middleware('kyc.enforce')->name('services.vtu.internet.buy');

        Route::get('/services/vtu/betting', [App\Http\Controllers\Service\VTUController::class, 'bettingIndex'])->name('services.vtu.betting');
        Route::post('/services/vtu/betting/fund', [App\Http\Controllers\Service\VTUController::class, 'fundBetting'])->middleware('kyc.enforce')->name('services.vtu.betting.fund');

        Route::get('/services/vtu/epins', [App\Http\Controllers\Service\VTUController::class, 'epinIndex'])->name('services.vtu.epin');
        Route::post('/services/vtu/epins/buy', [App\Http\Controllers\Service\VTUController::class, 'buyEpin'])->middleware('kyc.enforce')->name('services.vtu.epin.buy');

        Route::get('/services/vtu/recharge-printing', [App\Http\Controllers\Service\VTUController::class, 'rechargePrintingIndex'])->name('services.vtu.recharge_printing');
        Route::post('/services/vtu/recharge-printing/generate', [App\Http\Controllers\Service\VTUController::class, 'generateRechargePins'])->middleware('kyc.enforce')->name('services.vtu.recharge_printing.generate');
        Route::get('/services/vtu/recharge-printing/query', [App\Http\Controllers\Service\VTUController::class, 'queryRechargeOrder'])->name('services.vtu.recharge_printing.query');
        Route::get('/services/vtu/recharge-printing/data-plans', [App\Http\Controllers\Service\VTUController::class, 'fetchDatabundlePlans'])->name('services.vtu.recharge_printing.data_plans');

        // Education Hub under VTU Hub
        Route::get('/services/vtu/education/waec',       [App\Http\Controllers\Service\VTUController::class, 'waecIndex'])->name('services.education.waec');
        Route::post('/services/vtu/education/waec/buy',  [App\Http\Controllers\Service\VTUController::class, 'buyWaecPin'])->middleware('kyc.enforce')->name('services.education.waec.buy');
        
        Route::get('/services/vtu/education/waec-registration',       [App\Http\Controllers\Service\VTUController::class, 'waecRegistrationIndex'])->name('services.education.waec_registration');
        Route::post('/services/vtu/education/waec-registration/buy',  [App\Http\Controllers\Service\VTUController::class, 'buyWaecRegistrationPin'])->middleware('kyc.enforce')->name('services.education.waec_registration.buy');

        Route::get('/services/vtu/education/neco',       [App\Http\Controllers\Service\VTUController::class, 'necoIndex'])->name('services.education.neco');
        Route::post('/services/vtu/education/neco/buy',  [App\Http\Controllers\Service\VTUController::class, 'buyNecoPin'])->middleware('kyc.enforce')->name('services.education.neco.buy');

        Route::get('/services/vtu/education/nabteb',     [App\Http\Controllers\Service\VTUController::class, 'nabtebIndex'])->name('services.education.nabteb');
        Route::post('/services/vtu/education/nabteb/buy', [App\Http\Controllers\Service\VTUController::class, 'buyNabtebPin'])->middleware('kyc.enforce')->name('services.education.nabteb.buy');

        Route::get('/services/vtu/education/jamb',       [App\Http\Controllers\Service\VTUController::class, 'jambIndex'])->name('services.education.jamb');
        Route::post('/services/vtu/education/jamb/buy',  [App\Http\Controllers\Service\VTUController::class, 'buyJambPin'])->middleware('kyc.enforce')->name('services.education.jamb.buy');

        // (M-9) Duplicate airtime/data routes removed â€” canonical routes are inside `feature:vtu_services` group above
    });

    // â”€â”€ Insurance â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::middleware('feature:insurance_services')->group(function () {
        Route::get('/services/insurance/motor',        [App\Http\Controllers\Service\InsuranceController::class, 'motorIndex'])->name('services.insurance.motor');
        Route::get('/services/insurance/motor/options', [App\Http\Controllers\Service\InsuranceController::class, 'getMotorOptions'])->name('services.insurance.motor.options');
        Route::post('/services/insurance/motor/buy',   [App\Http\Controllers\Service\InsuranceController::class, 'buyMotorInsurance'])->middleware('kyc.enforce')->name('services.insurance.motor.buy');
    });

    // â”€â”€ Fuwa.NG Extended Services â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    Route::prefix('services')->name('services.')->group(function () {

        // Agency Banking
        Route::middleware('feature:agency_banking')->group(function () {
            Route::get('/agency-banking',        [App\Http\Controllers\NexusServiceController::class, 'agencyBanking'])->name('agency');
            Route::post('/agency-banking/request',[App\Http\Controllers\NexusServiceController::class, 'agencyRequest'])->middleware('kyc.enforce')->name('agency.request');
        });

        // Auctions
        Route::middleware('feature:auctions')->group(function () {
            Route::get('/auctions/dashboard', [App\Http\Controllers\UserAuctionController::class, 'dashboard'])->name('auctions.dashboard');
            Route::post('/auctions/bid', [App\Http\Controllers\UserAuctionController::class, 'placeBid'])->middleware('kyc.enforce')->name('auctions.bid');
            Route::post('/auctions/watchlist/add', [App\Http\Controllers\UserAuctionController::class, 'addToWatchlist'])->name('auctions.watchlist.add');
            Route::post('/auctions/watchlist/remove', [App\Http\Controllers\UserAuctionController::class, 'removeFromWatchlist'])->name('auctions.watchlist.remove');
        });

        // Notary Services
        Route::middleware('feature:notary_services')->group(function () {
            Route::get('/notary',                [App\Http\Controllers\NexusServiceController::class, 'notary'])->name('notary');
            Route::post('/notary/submit',        [App\Http\Controllers\NexusServiceController::class, 'notarySubmit'])->middleware('kyc.enforce')->name('notary.submit');
            Route::post('/notary/pay',           [App\Http\Controllers\NexusServiceController::class, 'notaryPay'])->middleware('kyc.enforce')->name('notary.pay');
        });

        // Post Office & Logistics
        Route::middleware('feature:post_office')->group(function () {
            Route::get('/logistics/dashboard', [App\Http\Controllers\UserLogisticsController::class, 'dashboard'])->name('user.logistics.dashboard');
            Route::get('/logistics/book',      [App\Http\Controllers\UserLogisticsController::class, 'book'])->name('user.logistics.book');
            Route::post('/logistics/book',     [App\Http\Controllers\UserLogisticsController::class, 'store'])->middleware('kyc.enforce')->name('user.logistics.store');
        });

        // Ticketing
        Route::middleware('feature:ticketing')->group(function () {
            Route::get('/ticketing',             [App\Http\Controllers\NexusServiceController::class, 'ticketing'])->name('ticketing');
            Route::post('/ticketing/buy',        [App\Http\Controllers\NexusServiceController::class, 'buyTicket'])->middleware('kyc.enforce')->name('ticketing.buy');
        });

        // Virtual Cards
        Route::middleware('feature:virtual_cards')->group(function () {
            Route::get('/virtual-card',          [App\Http\Controllers\NexusServiceController::class, 'virtualCard'])->name('virtual_card');
            Route::post('/virtual-card/create',  [App\Http\Controllers\NexusServiceController::class, 'createVirtualCard'])->middleware('kyc.enforce')->name('virtual_card.create');
            Route::post('/virtual-card/fund',    [App\Http\Controllers\NexusServiceController::class, 'fundVirtualCard'])->middleware('kyc.enforce')->name('virtual_card.fund');
        });

        // FX Exchange
        Route::middleware('feature:fx_exchange')->group(function () {
            Route::get('/fx',                    [App\Http\Controllers\NexusServiceController::class, 'fx'])->name('fx');
            Route::post('/fx/exchange',          [App\Http\Controllers\NexusServiceController::class, 'exchangeCurrency'])->middleware('kyc.enforce')->name('fx.exchange');
            Route::get('/fx/rates',              [App\Http\Controllers\NexusServiceController::class, 'fxRates'])->name('fx.rates');
        });

        // Invoicing
        Route::middleware('feature:invoicing')->group(function () {
            Route::get('/invoicing',             [App\Http\Controllers\NexusServiceController::class, 'invoicing'])->name('invoicing');
            Route::post('/invoicing/create',     [App\Http\Controllers\NexusServiceController::class, 'createInvoice'])->middleware('kyc.enforce')->name('invoicing.create');
        });

    });

    // Top-level Virtual Card aliases
    Route::middleware('feature:virtual_cards')->group(function () {
        Route::get('/virtual-card',          [App\Http\Controllers\NexusServiceController::class, 'virtualCard'])->name('virtual_card');
        Route::post('/virtual-card/create',  [App\Http\Controllers\NexusServiceController::class, 'createVirtualCard'])->middleware('kyc.enforce')->name('virtual_card.create');
        Route::post('/virtual-card/fund',    [App\Http\Controllers\NexusServiceController::class, 'fundVirtualCard'])->middleware('kyc.enforce')->name('virtual_card.fund');
    });

    Route::post('/tour/complete', [TourController::class, 'complete'])->name('tour.complete');

}); // â”€â”€ End of user auth middleware group â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// â”€â”€ Admin â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// IMPORTANT (C-3): Admin prefix is OUTSIDE the user auth middleware.
// Admin login must be reachable by unauthenticated visitors.
Route::prefix(config('app.admin_path', 'admin'))->name('admin.')->group(function () {
    Route::get('/login',  [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout',[App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');

        Route::middleware(['auth:admin', 'admin.audit'])->group(function () {
            // Admin Profile
            Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

            // 2FA Setup
            Route::get('/security/2fa', [App\Http\Controllers\Admin\TwoFactorSetupController::class, 'index'])->name('settings.security.2fa.index');
            Route::post('/security/2fa/enable', [App\Http\Controllers\Admin\TwoFactorSetupController::class, 'enable'])->name('settings.security.2fa.enable');
            Route::post('/security/2fa/disable', [App\Http\Controllers\Admin\TwoFactorSetupController::class, 'disable'])->name('settings.security.2fa.disable');
            Route::get('/dashboard',                   [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/chatbot/metrics',             [App\Http\Controllers\Admin\ChatbotAdminController::class, 'dashboardMetrics'])
                ->middleware('super_admin')
                ->name('chatbot.metrics');

            // Sub-Admin Management
            Route::middleware('permission:manage_admins')->group(function () {
                Route::resource('admins', AdminManagementController::class)->except(['show']);
            });
            Route::middleware('permission:manage_roles')->group(function () {
                Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['show']);
            });

            Route::middleware('permission:manage_blog')->group(function () {
                Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)->except(['show']);
            });

            Route::middleware('permission:manage_pages')->group(function () {
                Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
            });

            // Logistics RBAC Management
            Route::middleware('super_admin')->group(function () {
                Route::resource('logistics-staff', \App\Http\Controllers\Admin\LogisticsStaffManagementController::class)->except(['show']);
                Route::post('logistics-staff/{logisticsStaff}/impersonate', [\App\Http\Controllers\Admin\LogisticsStaffManagementController::class, 'impersonate'])
                    ->name('logistics-staff.impersonate');
                Route::resource('referral-tiers', \App\Http\Controllers\Admin\ReferralTierController::class)->except(['show']);
            });

            Route::get('/services',                    [App\Http\Controllers\Admin\ServiceManagementController::class, 'index'])->name('services.index');
            Route::post('/services/toggles/{feature}', [App\Http\Controllers\Admin\ServiceManagementController::class, 'setToggle'])->name('services.toggles.set');

            Route::get('/sandbox',                     [App\Http\Controllers\Admin\AdminSandboxController::class, 'index'])->name('sandbox.index');
            Route::get('/sandbox/nin',                 [App\Http\Controllers\Admin\AdminSandboxController::class, 'nin'])->name('sandbox.nin');
            Route::post('/sandbox/nin/verify',         [App\Http\Controllers\Admin\AdminSandboxController::class, 'verifyNin'])->name('sandbox.nin.verify');
            Route::get('/sandbox/bvn',                 [App\Http\Controllers\Admin\AdminSandboxController::class, 'bvn'])->name('sandbox.bvn');
            Route::post('/sandbox/bvn/verify',         [App\Http\Controllers\Admin\AdminSandboxController::class, 'verifyBvn'])->name('sandbox.bvn.verify');
            Route::get('/sandbox/services',            [App\Http\Controllers\Admin\AdminSandboxServicesController::class, 'index'])->name('sandbox.services.index');
            Route::get('/sandbox/services/{service}',  [App\Http\Controllers\Admin\AdminSandboxServicesController::class, 'show'])->name('sandbox.services.show');
            Route::post('/sandbox/services/{service}/run', [App\Http\Controllers\Admin\AdminSandboxServicesController::class, 'run'])->name('sandbox.services.run');

            // User Management
            Route::get('/users',                       [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users.index');
            Route::get('/users/{id}',                  [App\Http\Controllers\Admin\AdminController::class, 'showUser'])->whereNumber('id')->name('users.show');
            Route::get('/users/history/{email}',       [App\Http\Controllers\Admin\AdminController::class, 'userHistory'])->name('users.history');

            Route::middleware('super_admin')->group(function () {
                Route::post('/users/{id}/status',          [App\Http\Controllers\Admin\AdminController::class, 'updateUserStatus'])->whereNumber('id')->name('users.status');
                Route::post('/users/{id}/reset-password',  [App\Http\Controllers\Admin\AdminController::class, 'resetUserPassword'])->whereNumber('id')->name('users.reset_password');
                Route::post('/users/fund',                 [App\Http\Controllers\Admin\AdminController::class, 'fundUser'])->name('users.fund');
                Route::post('/users/deduct',               [App\Http\Controllers\Admin\AdminController::class, 'deductUser'])->name('users.deduct');
                Route::post('/users/refund',               [App\Http\Controllers\Admin\AdminController::class, 'refundUser'])->name('users.refund');
                Route::get('/users/{id}/refundable-transactions', [App\Http\Controllers\Admin\AdminController::class, 'refundableTransactions'])->whereNumber('id')->name('users.refundable_transactions');
            });

            // Support Tickets
            Route::get('/tickets',                     [App\Http\Controllers\Admin\AdminTicketController::class, 'index'])->name('tickets');
            Route::get('/tickets/{id}',                [App\Http\Controllers\Admin\AdminTicketController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{id}/reply',         [App\Http\Controllers\Admin\AdminTicketController::class, 'reply'])->name('tickets.reply');
            Route::post('/tickets/{id}/close',         [App\Http\Controllers\Admin\AdminTicketController::class, 'close'])->name('tickets.close');

            // â”€â”€ NIN Modification Management â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            Route::get('/verifications/nin-modifications', [App\Http\Controllers\Admin\NINModificationAdminController::class, 'index'])->name('verifications.nin_modifications.index');
            Route::get('/verifications/nin-modifications/{id}', [App\Http\Controllers\Admin\NINModificationAdminController::class, 'show'])->name('verifications.nin_modifications.show');
            Route::post('/verifications/nin-modifications/{id}/update', [App\Http\Controllers\Admin\NINModificationAdminController::class, 'updateStatus'])->name('verifications.nin_modifications.update');

            // â”€â”€ Broadcast Messaging â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            Route::get('/broadcasts',                  [App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcasts.index');
            Route::get('/broadcasts/create',           [App\Http\Controllers\Admin\BroadcastController::class, 'create'])->name('broadcasts.create');
            Route::post('/broadcasts',                 [App\Http\Controllers\Admin\BroadcastController::class, 'store'])->name('broadcasts.store');
            Route::post('/broadcasts/{broadcast}/send', [App\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('broadcasts.send');
            Route::delete('/broadcasts/{broadcast}',    [App\Http\Controllers\Admin\BroadcastController::class, 'destroy'])->name('broadcasts.destroy');

            Route::get('/direct-messages', [App\Http\Controllers\Admin\DirectMessageController::class, 'index'])->name('direct_messages.index');
            Route::get('/direct-messages/create', [App\Http\Controllers\Admin\DirectMessageController::class, 'create'])->name('direct_messages.create');
            Route::post('/direct-messages', [App\Http\Controllers\Admin\DirectMessageController::class, 'store'])->name('direct_messages.store');
            Route::post('/direct-messages/{directMessage}/send', [App\Http\Controllers\Admin\DirectMessageController::class, 'send'])->name('direct_messages.send');

            Route::get('/email-campaigns', [App\Http\Controllers\Admin\EmailCampaignController::class, 'index'])->name('email_campaigns.index');
            Route::get('/email-campaigns/create', [App\Http\Controllers\Admin\EmailCampaignController::class, 'create'])->name('email_campaigns.create');
            Route::post('/email-campaigns', [App\Http\Controllers\Admin\EmailCampaignController::class, 'store'])->name('email_campaigns.store');
            Route::post('/email-campaigns/{emailCampaign}/send', [App\Http\Controllers\Admin\EmailCampaignController::class, 'send'])->name('email_campaigns.send');
            Route::get('/email-campaigns/{emailCampaign}/recipients', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'emailRecipients'])->name('email_campaigns.recipients');
            Route::get('/email-campaigns/{emailCampaign}/recipients/export', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'emailRecipientsExport'])->name('email_campaigns.recipients.export');
            Route::post('/email-campaigns/{emailCampaign}/retry-failed', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'retryEmailFailed'])->name('email_campaigns.retry_failed');

            Route::get('/sms-campaigns', [App\Http\Controllers\Admin\SmsCampaignController::class, 'index'])->name('sms_campaigns.index');
            Route::get('/sms-campaigns/create', [App\Http\Controllers\Admin\SmsCampaignController::class, 'create'])->name('sms_campaigns.create');
            Route::post('/sms-campaigns', [App\Http\Controllers\Admin\SmsCampaignController::class, 'store'])->name('sms_campaigns.store');
            Route::post('/sms-campaigns/{smsCampaign}/send', [App\Http\Controllers\Admin\SmsCampaignController::class, 'send'])->name('sms_campaigns.send');
            Route::get('/sms-campaigns/{smsCampaign}/recipients', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'smsRecipients'])->name('sms_campaigns.recipients');
            Route::get('/sms-campaigns/{smsCampaign}/recipients/export', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'smsRecipientsExport'])->name('sms_campaigns.recipients.export');
            Route::post('/sms-campaigns/{smsCampaign}/retry-failed', [App\Http\Controllers\Admin\CampaignRecipientsController::class, 'retrySmsFailed'])->name('sms_campaigns.retry_failed');

            // â”€â”€ Verification Vault â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            Route::get('/transactions',                [App\Http\Controllers\Admin\AdminTransactionController::class, 'index'])->name('transactions.index');
            Route::get('/verifications',               [App\Http\Controllers\Admin\AdminVerificationController::class, 'index'])->name('verifications.index');
            Route::get('/verifications/{id}',          [App\Http\Controllers\Admin\AdminVerificationController::class, 'show'])->name('verifications.show');
            Route::get('/verifications/{id}/report',   [App\Http\Controllers\Admin\AdminVerificationController::class, 'report'])->name('verifications.report');

            // External Integrations & Feature Toggles
            Route::get('/features',                    [App\Http\Controllers\Admin\FeatureToggleController::class, 'index'])->name('features.index');
            Route::post('/features',                   [App\Http\Controllers\Admin\FeatureToggleController::class, 'store'])->name('features.store');
            Route::put('/features/{id}',               [App\Http\Controllers\Admin\FeatureToggleController::class, 'update'])->name('features.update');
            Route::delete('/features/{id}',            [App\Http\Controllers\Admin\FeatureToggleController::class, 'destroy'])->name('features.destroy');

            Route::get('/custom-apis',                 [App\Http\Controllers\Admin\CustomApiController::class, 'index'])->name('custom_apis.index');
            Route::post('/custom-apis',                [App\Http\Controllers\Admin\CustomApiController::class, 'store'])->name('custom_apis.store');
            Route::post('/custom-apis/templates/{template}', [App\Http\Controllers\Admin\CustomApiController::class, 'storeTemplate'])->name('custom_apis.templates.store');
            Route::put('/custom-apis/{id}',            [App\Http\Controllers\Admin\CustomApiController::class, 'update'])->name('custom_apis.update');
            Route::delete('/custom-apis/{id}',         [App\Http\Controllers\Admin\CustomApiController::class, 'destroy'])->name('custom_apis.destroy');
            Route::post('/custom-apis/{id}/types',     [App\Http\Controllers\Admin\CustomApiController::class, 'storeVerificationType'])->name('custom_apis.types.store');
            Route::delete('/custom-apis/{id}/types/{typeId}', [App\Http\Controllers\Admin\CustomApiController::class, 'destroyVerificationType'])->name('custom_apis.types.destroy');

            Route::get('/legal/catalog',               [App\Http\Controllers\Admin\LegalCatalogAdminController::class, 'index'])->name('legal_catalog.index');
            Route::post('/legal/catalog',              [App\Http\Controllers\Admin\LegalCatalogAdminController::class, 'store'])->name('legal_catalog.store');
            Route::put('/legal/catalog/{notarySetting}', [App\Http\Controllers\Admin\LegalCatalogAdminController::class, 'update'])->name('legal_catalog.update');

            Route::get('/audit-logs',                  [App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit_logs.index');
            Route::get('/queue',                       [App\Http\Controllers\Admin\QueueMonitorController::class, 'index'])->name('queue.index');

            // API Applications
            Route::get('/api-applications',            [App\Http\Controllers\Admin\AdminController::class, 'apiApplications'])->name('api_applications.index');
            Route::post('/api-applications/{id}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateApiStatus'])->name('api_applications.update_status');
            Route::middleware('admin.security')->group(function () {
                Route::get('/developer-api', [App\Http\Controllers\Admin\DeveloperApiAdminController::class, 'index'])->name('developer_api.index');
                Route::post('/developer-api/pricing', [App\Http\Controllers\Admin\DeveloperApiAdminController::class, 'updatePricing'])->name('developer_api.pricing');
                Route::post('/developer-api/settings', [App\Http\Controllers\Admin\DeveloperApiAdminController::class, 'updateSettings'])->name('developer_api.settings');
                Route::post('/developer-api/docs', [App\Http\Controllers\Admin\DeveloperApiAdminController::class, 'updateDocs'])->name('developer_api.docs');
                Route::post('/developer-api/endpoints', [App\Http\Controllers\Admin\DeveloperApiAdminController::class, 'updateEndpoints'])->name('developer_api.endpoints');
            });

            // Self-Funding (Super Admin Only)
            Route::middleware('super_admin')->group(function () {
                Route::get('/self-funding', [App\Http\Controllers\Admin\SelfFundingController::class, 'index'])->name('self_funding.index');
                Route::post('/self-funding', [App\Http\Controllers\Admin\SelfFundingController::class, 'fund'])->name('self_funding.fund');
            });

            // Auctions
            Route::get('/auctions', [App\Http\Controllers\Admin\AuctionController::class, 'index'])->name('auctions.index');
            Route::get('/auctions/create', [App\Http\Controllers\Admin\AuctionController::class, 'create'])->name('auctions.create');
            Route::post('/auctions', [App\Http\Controllers\Admin\AuctionController::class, 'store'])->name('auctions.store');
            Route::get('/auctions/{lot}/edit', [App\Http\Controllers\Admin\AuctionController::class, 'edit'])->name('auctions.edit');
            Route::put('/auctions/{lot}', [App\Http\Controllers\Admin\AuctionController::class, 'update'])->name('auctions.update');
            Route::delete('/auctions/{lot}', [App\Http\Controllers\Admin\AuctionController::class, 'destroy'])->name('auctions.destroy');
            Route::post('/auctions/{id}/restore', [App\Http\Controllers\Admin\AuctionController::class, 'restore'])->name('auctions.restore');

            Route::get('/auctions/{lot}/bids', [App\Http\Controllers\Admin\AuctionController::class, 'bids'])->name('auctions.bids');
            Route::put('/auctions/bids/{bid}', [App\Http\Controllers\Admin\AuctionController::class, 'updateBid'])->name('auctions.bids.update');
            Route::delete('/auctions/bids/{bid}', [App\Http\Controllers\Admin\AuctionController::class, 'destroyBid'])->name('auctions.bids.destroy');
            Route::post('/auctions/bids/{id}/restore', [App\Http\Controllers\Admin\AuctionController::class, 'restoreBid'])->name('auctions.bids.restore');

            Route::get('/auctions/sellers', [App\Http\Controllers\Admin\AuctionController::class, 'sellers'])->name('auctions.sellers');
            Route::post('/auctions/sellers', [App\Http\Controllers\Admin\AuctionController::class, 'storeSeller'])->name('auctions.sellers.store');
            Route::put('/auctions/sellers/{seller}', [App\Http\Controllers\Admin\AuctionController::class, 'updateSeller'])->name('auctions.sellers.update');
            Route::delete('/auctions/sellers/{seller}', [App\Http\Controllers\Admin\AuctionController::class, 'destroySeller'])->name('auctions.sellers.destroy');
            Route::post('/auctions/sellers/{id}/restore', [App\Http\Controllers\Admin\AuctionController::class, 'restoreSeller'])->name('auctions.sellers.restore');

            // Operations
            Route::get('/ops/invoices',                [App\Http\Controllers\Admin\AdminOperationsController::class, 'invoices'])->name('operations.invoices');
            Route::post('/ops/invoices/{id}/status',   [App\Http\Controllers\Admin\AdminOperationsController::class, 'updateInvoiceStatus'])->name('operations.invoices.status');
            Route::get('/ops/logistics',               [App\Http\Controllers\Admin\AdminOperationsController::class, 'logistics'])->name('operations.logistics');
            Route::post('/ops/logistics/{id}/status',  [App\Http\Controllers\Admin\AdminOperationsController::class, 'updateLogisticsStatus'])->name('operations.logistics.status');
            Route::middleware('permission:manage_notary')->group(function () {
                Route::get('/ops/notary',                  [App\Http\Controllers\Admin\AdminOperationsController::class, 'notary'])->name('operations.notary');
                Route::post('/ops/notary/{id}/status',     [App\Http\Controllers\Admin\AdminOperationsController::class, 'updateNotaryStatus'])->name('operations.notary.status');
            });

            // Settings
            Route::get('/settings',                    [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/notification',      [App\Http\Controllers\Admin\SettingsController::class, 'updateNotification'])->name('settings.notification');
            Route::post('/settings/pricing',           [App\Http\Controllers\Admin\SettingsController::class, 'updatePricing'])->name('settings.pricing');
            Route::post('/settings/manual-funding',    [App\Http\Controllers\Admin\SettingsController::class, 'updateManualFunding'])->name('settings.manual_funding');
            Route::post('/settings/api-settings',      [App\Http\Controllers\Admin\SettingsController::class, 'updateApiSettings'])->name('settings.api_settings');
            Route::post('/settings/api-keys',          [App\Http\Controllers\Admin\SettingsController::class, 'updateApiKeys'])->name('settings.api_keys');
            Route::post('/settings/notary-docs',       [App\Http\Controllers\Admin\SettingsController::class, 'updateNotaryDocs'])->name('settings.notary_docs');
            Route::post('/settings/branding',          [App\Http\Controllers\Admin\SettingsController::class, 'updateBranding'])->name('settings.branding');
            Route::post('/settings/system-pricing',    [App\Http\Controllers\Admin\SettingsController::class, 'updateSystemPricing'])->name('settings.system_pricing');
            Route::post('/settings/theme',             [App\Http\Controllers\Admin\SettingsController::class, 'updateTheme'])->name('settings.theme');
            Route::post('/settings/admin-security',    [App\Http\Controllers\Admin\SettingsController::class, 'updateAdminSecurity'])
                ->middleware('super_admin')
                ->name('settings.admin_security');
            Route::post('/settings/features',          [App\Http\Controllers\Admin\SettingsController::class, 'updateFeatureToggles'])->name('settings.features');
            // Authorization matches $canManageSecurity on settings page (not only is_super_admin flag).
            Route::post('/settings/gateways/toggle', [App\Http\Controllers\Admin\SettingsController::class, 'toggleGateway'])
                ->name('settings.gateways.toggle');
            Route::post('/settings/referrals',         [App\Http\Controllers\Admin\SettingsController::class, 'updateReferralSettings'])->name('settings.referrals');
            Route::post('settings/auction',           [App\Http\Controllers\Admin\SettingsController::class, 'updateAuctionSettings'])->name('settings.auction');
            Route::get('settings/whatsapp-widget',    [App\Http\Controllers\Admin\WhatsAppWidgetController::class, 'index'])->name('settings.whatsapp_widget');
            Route::post('settings/whatsapp-widget',   [App\Http\Controllers\Admin\WhatsAppWidgetController::class, 'update'])->name('settings.whatsapp_widget.update');
            Route::post('/media/upload',               [App\Http\Controllers\Admin\MediaController::class, 'upload'])->name('media.upload');

            Route::get('settings/advanced', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'index'])->name('advanced_settings.index');
            Route::put('settings/advanced', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'update'])->name('advanced_settings.update');

            Route::post('/settings/security/verifyme/ips', [App\Http\Controllers\Admin\SettingsController::class, 'updateVerifymeWebhookIps'])
                ->middleware('admin.security')
                ->name('settings.security.verifyme_ips');
            Route::post('/settings/security/verifyme/secret/generate', [App\Http\Controllers\Admin\SettingsController::class, 'generateVerifymeWebhookSecret'])
                ->middleware('admin.security')
                ->name('settings.security.verifyme_secret.generate');
            Route::post('/settings/security/verifyme/secret', [App\Http\Controllers\Admin\SettingsController::class, 'updateVerifymeWebhookSecret'])
                ->middleware('admin.security')
                ->name('settings.security.verifyme_secret');

            // NIN Enrollment Agents Management
            Route::prefix('agents')->name('agents.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\AdminAgentController::class, 'index'])->name('index');
                Route::get('/leaderboard', [App\Http\Controllers\Admin\AdminAgentController::class, 'leaderboard'])->name('leaderboard');
                Route::post('/leaderboard/publish', [App\Http\Controllers\Admin\AdminAgentController::class, 'publishLeaderboard'])->name('leaderboard.publish');
                Route::get('/upload-preapproved', [App\Http\Controllers\Admin\AdminAgentController::class, 'showUploadPreApproved'])->name('upload_preapproved');
                Route::post('/upload-preapproved', [App\Http\Controllers\Admin\AdminAgentController::class, 'processUploadPreApproved'])->name('upload_preapproved.process');
                Route::get('/{id}', [App\Http\Controllers\Admin\AdminAgentController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [App\Http\Controllers\Admin\AdminAgentController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [App\Http\Controllers\Admin\AdminAgentController::class, 'reject'])->name('reject');
                Route::post('/{id}/suspend', [App\Http\Controllers\Admin\AdminAgentController::class, 'suspend'])->name('suspend');
                Route::post('/{id}/reactivate', [App\Http\Controllers\Admin\AdminAgentController::class, 'reactivate'])->name('reactivate');

                // Agent Issues Management
                Route::prefix('issues')->name('issues.')->group(function () {
                    Route::get('/', [App\Http\Controllers\Admin\AdminAgentIssueController::class, 'index'])->name('index');
                    Route::get('/{id}', [App\Http\Controllers\Admin\AdminAgentIssueController::class, 'show'])->name('show');
                    Route::post('/{id}/reply', [App\Http\Controllers\Admin\AdminAgentIssueController::class, 'reply'])->name('reply');
                    Route::post('/{id}/status', [App\Http\Controllers\Admin\AdminAgentIssueController::class, 'updateStatus'])->name('status');
                });
            });

            // Parcel Agents Admin
            Route::get('/parcels/agents', [\App\Http\Controllers\Admin\ParcelAgentAdminController::class, 'index'])->name('parcels.agents.index');
            Route::put('/parcels/agents/{agent}/status', [\App\Http\Controllers\Admin\ParcelAgentAdminController::class, 'updateStatus'])->name('parcels.agents.status');
            Route::get('/parcels/audit', [\App\Http\Controllers\Admin\ParcelAgentAdminController::class, 'audit'])->name('parcels.audit.index');
        });
    });
// ── End of admin group ───────────────────────────────────


