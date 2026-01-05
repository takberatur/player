<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {

    //  Schema::defaultStringLength(191);

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      URL::forceScheme('https');
    }
  }
}