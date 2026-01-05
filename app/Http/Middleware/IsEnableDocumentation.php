<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsEnableDocumentation
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if ($request->is('documentation')) {
      $enableRoute = Setting::get('enable_documentation')  === '1';
      if (!$enableRoute) {
        return redirect()->route('forbidden')->with([
          'code' => 404,
          'error' => 'Page Not Found',
          'message' => 'The requested page could not be found.',
        ]);
      }
    }
    return $next($request);
  }
}
