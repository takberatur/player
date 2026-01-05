<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdVastTagAnalytic;
use App\Models\CustomAdVastTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdTrackingController extends Controller
{
  public function track(Request $request)
  {
    $type = $request->query('type', 'unknown'); // imp, click, start, complete
    $ad_id = $request->query('ad', 'unknown');

    // Basic Validation
    if ($type === 'unknown' || $ad_id === 'unknown') {
      return response()->noContent();
    }

    try {
      // 1. Log to laravel log (optional, good for debugging)
      // Log::channel('daily')->info("Ad Tracking | Ad ID: {$ad_id} | Type: {$type} | IP: " . $request->ip());

      // 2. Check if Ad exists (optional but recommended to avoid junk data)
      // We can skip this if we want performance and rely on foreign key constraints failing silently or handling errors
      // But checking exists is safer.
      $exists = CustomAdVastTag::where('id', $ad_id)->exists();

      if ($exists) {
        // 3. Update Analytics
        $analytic = AdVastTagAnalytic::firstOrCreate(
          [
            'custom_ad_vast_tag_id' => $ad_id,
            'date' => now()->toDateString(),
          ],
          [
            'id' => Str::uuid(),
            'impressions' => 0,
            'clicks' => 0,
            'starts' => 0,
            'completes' => 0,
          ]
        );

        // 4. Increment specific counter
        switch ($type) {
          case 'imp':
            $analytic->increment('impressions');
            break;
          case 'click':
            $analytic->increment('clicks');
            break;
          case 'start':
            $analytic->increment('starts');
            break;
          case 'complete':
            $analytic->increment('completes');
            break;
        }
      }
    } catch (\Throwable $th) {
      // Silently fail to not break the ad experience
      Log::error("Ad Tracking Error: " . $th->getMessage());
    }

    // 5. Response
    // Return a 1x1 pixel image for impression tracking (standard practice)
    if ($type === 'imp') {
      return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='))
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    // For other events (clicks, starts, etc), just return success
    return response()->json(['status' => 'success']);
  }
}