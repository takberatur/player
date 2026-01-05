<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
  use HasUuids;
  protected $table = 'subtitles';
  protected $fillable = [
    'id',
    'video_id',
    'language',
    'name',
    'url',
    'type',
  ];

  protected $casts = [
    'type' => 'string',
  ];

  public function video()
  {
    return $this->belongsTo(Video::class);
  }

  public function type()
  {
    return $this->type;
  }
}