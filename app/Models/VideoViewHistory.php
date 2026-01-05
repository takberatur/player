<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VideoViewHistory extends Model
{
  use HasUuids;

  protected $table = "video_view_history";

  protected $fillable = [
    'id',
    'video_id',
    'view_date',
    'view_count',
  ];

  protected $casts = [
    'view_date' => 'date',
    'view_count' => 'integer',
  ];

  public function video()
  {
    return $this->belongsTo(Video::class);
  }
}
