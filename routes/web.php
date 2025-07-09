<?php

use App\Http\Controllers\Gmail\GmailAuthController;
use App\Livewire\FullPage\ConnectGmail;
use App\Livewire\FullPage\Mails;
use Illuminate\Support\Facades\Route;

Route::get('', ConnectGmail::class)->name('home');

Route::prefix('gmail')->group(function () {
    Route::get('allow-access', [GmailAuthController::class, 'allowAccess'])->name('GmailAuthController.allowAccess');
    Route::get('get-token', [GmailAuthController::class, 'getAuthToken'])->name('GmailAuthController.getAuthToken');
    Route::get('{gmailAccountId}/mails', Mails::class)->name('mails');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
