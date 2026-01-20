<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcceptInvitationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/register', [AcceptInvitationController::class, 'show'])->name('invitation.accept');
Route::post('/admin/register', [AcceptInvitationController::class, 'store'])->name('invitation.store');

// Payment Webhooks (must be excluded from CSRF)
// Payment Webhooks (must be excluded from CSRF)
Route::post('/webhooks/cybersource', [\App\Http\Controllers\PaymentWebhookController::class, 'handle'])->name('webhooks.cybersource');

// Debug Routes
Route::get('/health', function () {
    return 'OK: ' . now();
});

Route::get('/debug-env', function () {
    return [
        'app_name' => config('app.name'),
        'app_url' => config('app.url'),
        'db_host' => config('database.connections.pgsql.host'),
        'app_key_set' => !empty(config('app.key')),
        'storage_writable' => is_writable(storage_path()),
        'log_writable' => is_writable(storage_path('logs')),
    ];
});
