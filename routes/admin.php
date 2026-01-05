<?php

use App\Http\Controllers\Admin\AdVastTagAnalyticController;
use App\Http\Controllers\Admin\AnalyticController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\CustomAdVastTagController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin', 'editor', 'verified'])->prefix('admin')->name('admin.')->group(function () {
  Route::redirect('/admin', '/admin/dashboard');

  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  Route::get('/analytics', [AnalyticController::class, 'index'])->name('analytics');

  // ========================
  // Video Routes
  // ========================
  Route::get('/video', [VideoController::class, 'index'])->name('video.index');

  Route::get('/video/create', [VideoController::class, 'create'])->name('video.create');

  Route::post('/video', [VideoController::class, 'store'])->name('video.store');

  Route::get('/video/{id}/edit', [VideoController::class, 'edit'])->name('video.edit');

  Route::put('/video/{id}', [VideoController::class, 'update'])->name('video.update')->middleware('action-permission');

  Route::delete('/video/{id}', [VideoController::class, 'destroy'])->name('video.destroy')->middleware('action-permission');

  Route::post('/video/bulk-destroy', [VideoController::class, 'bulkDestroy'])->name('video.bulk-destroy')->middleware('action-permission');

  // ========================
  // Custom Ad Vast Tag Routes
  // ========================
  Route::get('/custom-ads', [CustomAdVastTagController::class, 'index'])->name('custom-ads.index');

  Route::get('/custom-ads/create', [CustomAdVastTagController::class, 'create'])->name('custom-ads.create');

  Route::post('/custom-ads', [CustomAdVastTagController::class, 'store'])->name('custom-ads.store');

  Route::get('/custom-ads/{id}/edit', [CustomAdVastTagController::class, 'edit'])->name('custom-ads.edit');

  Route::put('/custom-ads/{id}', [CustomAdVastTagController::class, 'update'])->name('custom-ads.update')->middleware('action-permission');

  Route::delete('/custom-ads/{id}', [CustomAdVastTagController::class, 'destroy'])->name('custom-ads.destroy')->middleware('action-permission');

  Route::post('/custom-ads/bulk-destroy', [CustomAdVastTagController::class, 'bulkDestroy'])->name('custom-ads.bulk-destroy')->middleware('action-permission');

  Route::get('/custom-ads/analytics', [AdVastTagAnalyticController::class, 'index'])->name('custom-ads.analytics.index');

  Route::get('/api/custom-ads/search', [CustomAdVastTagController::class, 'search'])->name('custom-ads.api.search');


  // ========================
  // Miscellaneous Routes
  // ========================
  Route::post('/clear-cache', function (Request $request) {
    if ($request->user() && ($request->user()->isAdmin() || $request->user()->isEditor())) {
      Artisan::call('cache:clear');
      Artisan::call('config:clear');
      Artisan::call('route:clear');
      Artisan::call('view:clear');
      Artisan::call('optimize:clear');
      return back()->with('success', 'Cache cleared successfully!');
    }
    return back()->with('error', 'Unauthorized action!');
  })->name('cache-clear');
});
