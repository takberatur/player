<?php

namespace App\Traits;

use App\Models\VideoViewHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait TrackViews
{
  public function incrementView()
  {
    $this->increment('views');

    $today = Carbon::today()->toDateString();

    $history = VideoViewHistory::firstOrNew([
      'video_id' => $this->id,
      'view_date' => $today,
    ]);

    $history->view_count = ($history->view_count ?? 0) + 1;
    $history->save();

    return $this;
  }

  public function getDailyViews($startDate, $endDate)
  {
    return VideoViewHistory::where('video_id', $this->id)
      ->whereBetween('view_date', [$startDate, $endDate])
      ->orderBy('view_date')
      ->get();
  }
}