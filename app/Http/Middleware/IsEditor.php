<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsEditor
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if ($request->is('admin')) {
      if (!Auth::check() || Auth::user()->role !== 'editor') {
        return redirect()->route('forbidden')->with([
          'code' => 403,
          'error' => 'Access Forbidden',
          'message' => 'You are not authorized to access this page.',
        ]);
      }

      return redirect()->route('admin.dashboard');
    }
    return $next($request);
  }
}
