<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoViewHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticController extends Controller
{
  public function index(Request $request)
  {
    $defaultStartDate = now()->subDays(30)->startOfDay();
    $defaultEndDate = now()->endOfDay();

    $startDate = $request->input('start_date')
      ? Carbon::parse($request->input('start_date'))->startOfDay()
      : $defaultStartDate;

    $endDate = $request->input('end_date')
      ? Carbon::parse($request->input('end_date'))->endOfDay()
      : $defaultEndDate;

    if ($startDate->diffInDays($endDate) > 365) {
      $startDate = $endDate->copy()->subDays(365)->startOfDay();
    }


    $startDateStr = $startDate->format('Y-m-d');
    $endDateStr = $endDate->format('Y-m-d');

    $viewsData = VideoViewHistory::query()
      ->select([
        'view_date',
        DB::raw('SUM(view_count) as total_views'),
        DB::raw('COUNT(DISTINCT video_id) as unique_videos_viewed')
      ])
      ->whereDate('view_date', '>=', $startDateStr)
      ->whereDate('view_date', '<=', $endDateStr)
      ->groupBy('view_date')
      ->orderBy('view_date')
      ->get()
      ->keyBy(function ($item) {
        return Carbon::parse($item->view_date)->format('Y-m-d');
      });

    $videosData = Video::query()
      ->selectRaw('DATE(created_at) as date, COUNT(*) as new_videos')
      ->whereBetween('created_at', [$startDate, $endDate])
      ->groupBy('date')
      ->orderBy('date')
      ->get()
      ->keyBy('date');

    $topVideos = VideoViewHistory::query()
      ->select([
        'video_id',
        DB::raw('SUM(view_count) as total_views'),
      ])
      ->with(['video'])
      ->whereDate('view_date', '>=', $startDateStr)
      ->whereDate('view_date', '<=', $endDateStr)
      ->groupBy('video_id')
      ->orderByDesc('total_views')
      ->limit(10)
      ->get();

    $summary = [
      'total_views' => $viewsData->sum('total_views'),
      'total_videos' => Video::whereBetween('created_at', [$startDate, $endDate])->count(),
      'unique_videos_viewed' => VideoViewHistory::whereDate('view_date', '>=', $startDateStr)
        ->whereDate('view_date', '<=', $endDateStr)
        ->distinct('video_id')
        ->count('video_id'),
    ];

    $charts = [];
    $period = CarbonPeriod::create($startDateStr, $endDateStr);

    foreach ($period as $date) {
      $dateString = $date->format('Y-m-d');

      $chartItem = [
        'date' => $dateString,
        'total_views' => 0,
        'unique_videos_viewed' => 0,
        'new_videos' => 0,
      ];

      $viewItem = $viewsData->first(function ($item, $key) use ($dateString) {
        return Carbon::parse($item->view_date)->format('Y-m-d') === $dateString;
      });

      if ($viewItem) {
        $chartItem['total_views'] = (int) $viewItem->total_views;
        $chartItem['unique_videos_viewed'] = (int) $viewItem->unique_videos_viewed;
      }

      if ($videosData->has($dateString)) {
        $chartItem['new_videos'] = (int) $videosData->get($dateString)->new_videos;
      }

      $charts[] = $chartItem;
    }

    return Inertia::render('admin/Analytics', [
      'analytics' => [
        'charts' => $charts,
        'top_videos' => $topVideos,
        'summary' => $summary,
        'filters' => [
          'start_date' => $startDate->format('Y-m-d'),
          'end_date' => $endDate->format('Y-m-d'),
          'days' => $startDate->diffInDays($endDate) + 1,
        ],
      ],
    ]);
  }
}
