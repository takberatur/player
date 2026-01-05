<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdVastTagAnalytic extends Model
{
  use HasUuids;

  protected $fillable = [
    'custom_ad_vast_tag_id',
    'date',
    'impressions',
    'clicks',
    'starts',
    'completes',
  ];

  protected $casts = [
    'date' => 'date',
    'impressions' => 'integer',
    'clicks' => 'integer',
    'starts' => 'integer',
    'completes' => 'integer',
  ];

  /**
   * Relation back to the ad tag
   */
  public function adVastTag()
  {
    return $this->belongsTo(CustomAdVastTag::class, 'custom_ad_vast_tag_id');
  }

  /**
   * Scope a query to only include analytics between dates.
   */
  public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
  {
    return $query->whereBetween('date', [$startDate, $endDate]);
  }

  /**
   * Scope to sum up metrics
   */
  public function scopeSummary(Builder $query): Builder
  {
    return $query->selectRaw('
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks,
            SUM(starts) as total_starts,
            SUM(completes) as total_completes
        ');
  }
}