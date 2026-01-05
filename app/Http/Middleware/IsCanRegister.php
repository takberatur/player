<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCanRegister
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $isCanRegister = Setting::get('enable_registration') === '1';

    // Check if route is 'register' (GET) or 'register.store' (POST)
    if ($request->routeIs(['register', 'register.store']) && !$isCanRegister) {
      return redirect()->route('forbidden')->with([
        'code' => 403,
        'error' => 'Registration Not Enabled',
        'message' => 'Registration is not enabled. Please contact the administrator.',
      ]);
    }
    return $next($request);
  }
}