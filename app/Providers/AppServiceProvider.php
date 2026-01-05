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

    if ($this->app->environment('production') || $this->app->environment('staging')) {
      URL::forceScheme('https');
      $this->app['request']->server->set('HTTPS', 'on');
    }
  }
}
