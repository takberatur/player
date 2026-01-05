<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomAdVastTag extends Model
{
  use HasFactory, HasUuids;

  protected $fillable = [
    'name',
    'video_url',
    'direct_link_ad',
    'file_url', // Path to the generated VAST XML
    'additional_direct_link_ad',
    'tracking_url',
    'duration',
  ];

  /**
   * Relation to analytics
   */
  public function analytics()
  {
    return $this->hasMany(AdVastTagAnalytic::class, 'custom_ad_vast_tag_id');
  }

  /**
   * The "booted" method of the model.
   */
  protected static function booted(): void
  {
    static::deleting(function (CustomAdVastTag $adVastTag) {
      // Delete Video File if exists in storage
      if ($adVastTag->video_url && str_contains($adVastTag->video_url, '/storage/ad-videos/')) {
        $videoPath = str_replace('/storage/', '', parse_url($adVastTag->video_url, PHP_URL_PATH));
        Storage::disk('public')->delete($videoPath);
      }

      // Delete VAST XML File if exists
      if ($adVastTag->file_url && str_contains($adVastTag->file_url, '/storage/vast/')) {
        $xmlPath = str_replace('/storage/', '', parse_url($adVastTag->file_url, PHP_URL_PATH));
        Storage::disk('public')->delete($xmlPath);
      }
    });
  }
}