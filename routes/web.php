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
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'database' => 'connected', 'timestamp' => now()], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'database' => $e->getMessage(), 'timestamp' => now()], 500);
    }
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

Route::get('/fix-admin', function () {
    try {
        Illuminate\Support\Facades\Artisan::call('fix:admin');
        return nl2br(Illuminate\Support\Facades\Artisan::output());
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/clear-cache', function () {
    try {
        Illuminate\Support\Facades\Artisan::call('config:clear');
        Illuminate\Support\Facades\Artisan::call('cache:clear');
        Illuminate\Support\Facades\Artisan::call('view:clear');
        Illuminate\Support\Facades\Artisan::call('route:clear');
        Illuminate\Support\Facades\Artisan::call('filament:assets');
        return "All caches cleared and assets published! Refresh Admin Panel.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
