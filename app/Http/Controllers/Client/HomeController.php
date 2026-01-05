<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
  public function index(Request $request)
  {
    $videos = Video::with(['subtitles'])
      ->byViewCountPerType()
      ->get();

    $canRegister = Features::enabled(Features::registration());
    $host = $request->fullUrl();


    return inertia('client/Home', [
      'canRegister' => $canRegister,
      'videos' => $videos,
      'host' => $host,
    ]);
  }
}
