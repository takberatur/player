<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubtitleController extends Controller
{
  public function show(string $url)
  {
    // example from DB : /storage/subtitles/019b7f2e-644f-725f-8e4a-99e7a0d2509c-en-1767365305.srt

    // Remove leading slash and 'storage' prefix to get relative path for public disk
    // If url is /storage/subtitles/file.srt, we want subtitles/file.srt
    $relativePath = preg_replace('#^/?storage/#', '', $url);

    if (!Storage::disk('public')->exists($relativePath)) {
      abort(404);
    }

    $absolutePath = Storage::disk('public')->path($relativePath);
    $extension = pathinfo($relativePath, PATHINFO_EXTENSION);

    $mimeType = match (strtolower($extension)) {
      'srt' => 'text/plain', // JW Player prefers generic text/plain usually, or application/x-subrip
      'vtt' => 'text/vtt',
      default => 'text/plain'
    };

    // Use response()->file to handle Range headers, Content-Length, etc. automatically
    return response()->file($absolutePath, [
      'Content-Type' => $mimeType,
      'Content-Disposition' => 'inline',
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Methods' => 'GET, OPTIONS',
      'Access-Control-Allow-Headers' => 'Range, Origin, Accept',
      'Cache-Control' => 'public, max-age=86400',
    ]);
  }
}
