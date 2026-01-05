<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\TrackViews;

class Video extends Model
{
  use HasUuids, TrackViews;
  protected $table = 'videos';
  public $incrementing = false;
  protected $fillable = [
    'id',
    'title',
    'original_link',
    'encode_link',
    'poster',
    'type',
    'ad_vast',
    'enable_popunder_ad',
    'popunder_ad_code',
    'additional_ad_vast',
    'enable_button_download',
    'download_link',
    'views'
  ];

  protected $with = ['subtitles'];

  protected $casts = [
    'enable_popunder_ad' => 'boolean',
    'enable_button_download' => 'boolean',
  ];

  public const TYPES = [
    'google_drive',
    'yandex_disk',
    'amazon_drive',
    'archive',
    'facebook',
    'google_photo',
    'rumble',
    'dropbox',
    'youtube',
    'mega_disk',
    'm3u8',
    'mp4',
    'mkv',
    'ts',
    'mpd'
  ];

  public function getTypeLabelAttribute()
  {
    return ucfirst($this->type);
  }

  public function subtitles()
  {
    return $this->hasMany(Subtitle::class);
  }

  public function viewHistory(): HasMany
  {
    return $this->hasMany(VideoViewHistory::class);
  }

  public function scopeWithSubtitles($query)
  {
    return $query->with('subtitles');
  }

  public function scopePopular($query)
  {
    return $query->orderBy('views', 'desc');
  }

  public function getFormattedViewsAttribute()
  {
    if ($this->views >= 1000000) {
      return round($this->views / 1000000, 1) . 'M';
    } elseif ($this->views >= 1000) {
      return round($this->views / 1000, 1) . 'K';
    }
    return $this->views;
  }

  public function incrementViews()
  {
    $this->increment('views');
  }

  public function getThumbnailUrlAttribute()
  {
    return $this->poster ? $this->poster : asset('images/default-thumbnail.jpg');
  }

  public function scopeSearch($query, $search)
  {
    return $query->where(function ($q) use ($search) {
      $q->where('title', 'like', "%{$search}%")
        ->orWhere('original_link', 'like', "%{$search}%")
        ->orWhere('type', 'like', "%{$search}%");
    });
  }

  public function scopeOnePerType($query)
  {
    return $query->select('videos.*')
      ->whereIn('id', function ($subquery) {
        $subquery->selectRaw('MAX(id)')
          ->from('videos')
          ->groupBy('type');
      });
  }

  public function scopeRandomOnePerType($query)
  {
    return $query->select('videos.*')
      ->whereIn('id', function ($subquery) {
        $subquery->selectRaw('MAX(id)')
          ->from('videos')
          ->groupBy('type');
      })
      ->inRandomOrder();
  }

  public function scopeByViewCountPerType($query)
  {
    return $query->select('videos.*')
      ->whereIn('id', function ($subquery) {
        $subquery->selectRaw('MAX(id)')
          ->from('videos as v2')
          ->whereRaw('v2.type = videos.type')
          ->groupBy('v2.type');
      })
      ->orderBy('views', 'desc');
  }
}