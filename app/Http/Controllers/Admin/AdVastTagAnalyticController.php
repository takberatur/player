<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdVastTagAnalytic;
use App\Models\CustomAdVastTag;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdVastTagAnalyticController extends Controller
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

    // Aggregate data by date
    $analyticsData = AdVastTagAnalytic::query()
      ->select([
        'date',
        DB::raw('SUM(impressions) as total_impressions'),
        DB::raw('SUM(clicks) as total_clicks'),
        DB::raw('SUM(starts) as total_starts'),
        DB::raw('SUM(completes) as total_completes')
      ])
      ->whereDate('date', '>=', $startDateStr)
      ->whereDate('date', '<=', $endDateStr)
      ->groupBy('date')
      ->orderBy('date')
      ->get()
      ->keyBy(function ($item) {
        return Carbon::parse($item->date)->format('Y-m-d');
      });

    // Prepare Chart Data filling missing dates with 0
    $charts = [];
    $period = CarbonPeriod::create($startDateStr, $endDateStr);

    foreach ($period as $date) {
      $dateString = $date->format('Y-m-d');

      $chartItem = [
        'date' => $dateString,
        'total_impressions' => 0,
        'total_clicks' => 0,
        'total_starts' => 0,
        'total_completes' => 0,
      ];

      if ($analyticsData->has($dateString)) {
        $data = $analyticsData->get($dateString);
        $chartItem['total_impressions'] = (int) $data->total_impressions;
        $chartItem['total_clicks'] = (int) $data->total_clicks;
        $chartItem['total_starts'] = (int) $data->total_starts;
        $chartItem['total_completes'] = (int) $data->total_completes;
      }

      $charts[] = $chartItem;
    }

    // Calculate Summary
    $summary = [
      'total_impressions' => (int) $analyticsData->sum('total_impressions'),
      'total_clicks' => (int) $analyticsData->sum('total_clicks'),
      'total_starts' => (int) $analyticsData->sum('total_starts'),
      'total_completes' => (int) $analyticsData->sum('total_completes'),
    ];

    // Get Top Ad Vast
    $topAdVast = AdVastTagAnalytic::query()
      ->select([
        'custom_ad_vast_tag_id',
        DB::raw('SUM(impressions) as total_impressions'),
        DB::raw('SUM(clicks) as total_clicks'),
        DB::raw('SUM(starts) as total_starts'),
        DB::raw('SUM(completes) as total_completes'),
      ])
      ->with('adVastTag')
      ->whereDate('date', '>=', $startDateStr)
      ->whereDate('date', '<=', $endDateStr)
      ->groupBy('custom_ad_vast_tag_id')
      ->havingRaw('SUM(impressions) > 0')
      ->orderByDesc('total_impressions')
      ->limit(10)
      ->get()
      ->map(function ($analytic) {
        $ad = $analytic->adVastTag;
        if ($ad) {
          // Map aggregated values to standard attributes to match interface
          $analytic->impressions = $analytic->total_impressions;
          $analytic->clicks = $analytic->total_clicks;
          $analytic->starts = $analytic->total_starts;
          $analytic->completes = $analytic->total_completes;

          $ad->setRelation('analytics', [$analytic]);
        }
        return $ad;
      })
      ->filter();


    return Inertia::render('admin/advast/Analytics', [
      'adVastTagAnalyticProps' => [
        'charts' => $charts,
        'top_ad_vast' => $topAdVast->values()->all(),
        'summary' => $summary,
        'filters' => [
          'start_date' => $startDate->format('Y-m-d'),
          'end_date' => $endDate->format('Y-m-d'),
          'days' => $startDate->diffInDays($endDate) + 1,
        ],
      ]
    ]);
  }
}