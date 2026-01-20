<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcceptInvitationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/register', [AcceptInvitationController::class, 'show'])->name('invitation.accept');
Route::post('/admin/register', [AcceptInvitationController::class, 'store'])->name('invitation.store');

// Payment Webhooks (must be excluded from CSRF)
Route::post('/webhooks/cybersource', [\App\Http\Controllers\PaymentWebhookController::class, 'handle'])->name('webhooks.cybersource');
