<?php

namespace App\Http\Middleware;

use App\Models\Video;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVideoView
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    if ($response->getStatusCode() === 200 && $request->route()->getName() === 'play.index') {
      $videoId = $request->route('id');

      try {
        dispatch(function () use ($videoId) {
          $video = Video::where('id', $videoId)->first();
          if ($video) {
            $video->incrementView();
          }
        })->afterResponse();
      } catch (\Exception $e) {
        // Log error or ignore
      }
    }

    return $response;
  }
}
