<?php

use App\Http\Controllers\Admin\Settings\PasswordController;
use App\Http\Controllers\Admin\Settings\ProfileController;
use App\Http\Controllers\Admin\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\Admin\Settings\WebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'admin', 'editor', 'verified'])->prefix('admin')->name('admin.settings.')->group(function () {
  Route::redirect('settings', '/settings/profile');

  Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('action-permission');
  Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('action-permission');

  Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

  Route::put('settings/password', [PasswordController::class, 'update'])
    ->middleware(['throttle:6,1', 'action-permission'])
    ->name('user-password.update');

  Route::get('settings/appearance', function () {
    return Inertia::render('admin/settings/Appearance');
  })->name('appearance.edit');

  Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
    ->name('two-factor.show');

  Route::get('settings/web', [WebController::class, 'index'])->name('web.index');
  Route::post('settings/web', [WebController::class, 'update'])->name('web.update')->middleware('action-permission');
});
