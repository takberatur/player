<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActionPermission
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {

    if (!$request->isMethod('get')) {
      if (!Auth::check() || Auth::user()->role !== 'admin') {
        return back()->with('error', 'You do not have permission to perform this action.');
      }
    }

    return $next($request);
  }
}
