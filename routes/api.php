<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Settings\NotificationSettingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Rotas de mutação (POST, PUT, PATCH, DELETE).
| Autenticação via Bearer token (Sanctum) — obtido em POST /auth/token/session.
*/

// ─── Public ────────────────────────────────────────────────────────────────
Route::prefix('/')->group(function () {
    Route::get('notifications/vapid-key', [NotificationController::class, 'getVapidPublicKey']);
    Route::post('auth/token', [TokenController::class, 'issue']);
});

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::delete('auth/token', [TokenController::class, 'revoke']);

    // ─── Tasks ───────────────────────────────────────────────────────────────
    Route::prefix('tasks')->middleware('verified')->group(function () {
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
        Route::post('{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('{task}/toggle', [TaskController::class, 'toggleComplete'])->name('tasks.toggle');
    });

    // ─── Settings ────────────────────────────────────────────────────────────
    Route::prefix('settings')->middleware('verified')->group(function () {
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('password', [PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');
        Route::put('notifications', [NotificationSettingController::class, 'update'])
            ->name('notification-settings.update');
    });

    // ─── Push notifications ──────────────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::post('subscribe', [NotificationController::class, 'subscribe']);
        Route::post('unsubscribe', [NotificationController::class, 'unsubscribe']);
        Route::get('subscriptions', [NotificationController::class, 'getSubscriptions']);
        Route::post('test', [NotificationController::class, 'sendTestNotification']);
        Route::post('send-to-user', [NotificationController::class, 'sendToUser']);
        Route::post('send-to-all', [NotificationController::class, 'sendToAll']);
    });

    // ─── Admin ───────────────────────────────────────────────────────────────
    Route::middleware(['verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::patch('{user}', [UserController::class, 'update'])->name('update');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('{user}/send-password-reset', [UserController::class, 'sendPasswordReset'])
                ->name('send-password-reset');
            Route::post('{user}/reset-password', [UserController::class, 'resetPassword'])
                ->name('reset-password');
            Route::post('{user}/disable-two-factor', [UserController::class, 'disableTwoFactor'])
                ->name('disable-two-factor');
            Route::post('{user}/toggle-active', [UserController::class, 'toggleActive'])
                ->name('toggle-active');
        });
    });
});
