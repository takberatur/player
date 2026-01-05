<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Video;
use App\Models\Subtitle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $defaultStartDate = now()->subDays(30)->format('Y-m-d 00:00:00');
    $defaultEndDate = now()->format('Y-m-d 23:59:59');

    $startDate = $request->input('start_date', $defaultStartDate);
    $endDate = $request->input('end_date', $defaultEndDate);
    $perPage = (int) $request->input('per_page', 20);

    if (!in_array($perPage, [10, 20, 50, 100], true)) {
      $perPage = 20;
    }

    try {
      $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $startDate);
      $endCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $endDate);
    } catch (\Exception $e) {
      $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $defaultStartDate);
      $endCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $defaultEndDate);
    }

    $videoQuery = Video::query();
    $userQuery = User::query();
    $subtitleQuery = Subtitle::query();

    $videoQuery->whereBetween('created_at', [$startCarbon, $endCarbon]);
    $userQuery->whereBetween('created_at', [$startCarbon, $endCarbon]);
    $subtitleQuery->whereBetween('created_at', [$startCarbon, $endCarbon]);

    $stats = [
      'total_video' => $videoQuery->count(),
      'total_views' => $videoQuery->sum('views'),
      'total_users' => $userQuery->count(),
      'total_subtitles' => $subtitleQuery->count(),
    ];

    $topVideos = $videoQuery
      ->orderBy('views', 'desc')
      ->paginate($perPage)
      ->withQueryString();

    return inertia('admin/Dashboard', [
      'dashboard' => [
        'stats' => $stats,
        'topVideos' => $topVideos,
        'filters' => [
          'start_date' => $startDate,
          'end_date' => $endDate,
          'per_page' => (int) $perPage,
        ],
      ],
    ]);
  }
}