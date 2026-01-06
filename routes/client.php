<?php

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\PlayerController;
use App\Http\Controllers\Client\VideoController;
use App\Http\Controllers\Client\CustomAdVastTagController;
use App\Http\Controllers\Client\AdTrackingController;
use App\Http\Controllers\Client\DocumentationController;
use App\Http\Controllers\Client\SubtitleController;
use Illuminate\Support\Facades\Route;

Route::get("/", [HomeController::class, "index"])->name("home");
Route::get("/forbidden", function () {
  return inertia('client/Forbidden');
})->name("forbidden");

// Ad Tracking & VAST Serving
Route::get('/ads/track', [AdTrackingController::class, 'track'])->name('ads.track');
Route::get('/ads/vast.xml', [CustomAdVastTagController::class, 'serveRandom'])->name('ads.vast.random');
Route::get('/ads/vast/{id}.xml', [CustomAdVastTagController::class, 'show'])->name('ads.vast.show');

Route::get("play/{id}", [PlayerController::class, "index"])->name("play.index");

// Subtitle
Route::get("/subtitle/{url}", [SubtitleController::class, "show"])->where('url', '.*')->name("subtitle.show");



Route::post("/api/rumble-video", [VideoController::class, "rumble"])->name("sources.rumble");
Route::post("/api/youtube-video", [VideoController::class, "youtube"])->name("sources.youtube");
Route::post("/api/facebook-video", [VideoController::class, "facebook"])->name("sources.facebook");
Route::post("/api/google-photo-video", [VideoController::class, "googlePhoto"])->name("sources.google_photo");
Route::post("/api/google-drive-video", [VideoController::class, "googleDrive"])->name("sources.google_drive");
Route::post("/api/archive-video", [VideoController::class, "archive"])->name("sources.archive");
Route::post("/api/yandex-disk-video", [VideoController::class, "yandexDisk"])->name("sources.yandex_disk");
Route::post("/api/mega-video", [VideoController::class, "mega"])->name("sources.mega");
Route::get("/api/video-stream", [VideoController::class, "stream"])->name("video.stream");
Route::get("/api/video-stream-mega", [VideoController::class, "streamMega"])->name("video.stream-mega");


// =======================
// Documentation
// =======================
Route::middleware(['is-enable-documentation'])->prefix('documentation')->name('documentation.')->group(function () {
  Route::redirect('/documentation', '/documentation');

  Route::get("/", [DocumentationController::class, "index"])->name("documentation.index");
});
