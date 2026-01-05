<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    try {
      $appearance = $request->cookie('appearance') ?? 'system';
    } catch (DecryptException $e) {
      $appearance = 'system';
    }

    View::share('appearance', $appearance);

    return $next($request);
  }
}