<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Settings\NotificationSettingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// ─── Public ────────────────────────────────────────────────────────────────

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Emite Bearer token para a sessão atual (precisa de contexto de sessão web)
Route::post('auth/token/session', [TokenController::class, 'issueForSession'])
    ->middleware(['auth'])
    ->name('auth.token.session');

// ─── Dashboard ───────────────────────────────────────────────────────────────

Route::get('dashboard', function () {
    return Inertia::render('dashboard/Index');
})->middleware(['auth', 'verified'])->name('dashboard');

// ─── Tasks ───────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
});

// ─── Settings ────────────────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');
    Route::get('settings/appearance', fn () => Inertia::render('settings/Appearance'))->name('appearance.edit');
    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');
    Route::get('settings/notifications', [NotificationSettingController::class, 'edit'])->name('notification-settings.edit');
});

// ─── Admin ────────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
});
