<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
  public function index(string $id)
  {
    $video = Video::with(['subtitles'])->where('id', $id)->first();
    if (!$video) {
      return abort(404);
    }

    foreach ($video->subtitles as $sub) {
      // Use urlencode to handle slashes in the URL parameter correctly if needed by the route definition
      // But since we used "where('url', '.*')" in route, simple passing should work.
      // However, Laravel route() helper automatically encodes parameters.
      // We pass the full path stored in DB (e.g. /storage/subtitles/...)
      $sub->url = route('subtitle.show', ['url' => ltrim($sub->url, '/')]);
    }

    return inertia('client/play/Index', [
      'video' => $video,
    ]);
  }
}
