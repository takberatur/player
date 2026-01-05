<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  use HasFactory;
  use HasUuids;

  protected $fillable = ['id', 'key', 'value', 'type'];

  public static function get($key, $default = null)
  {
    $setting = self::where('key', $key)->first();
    return $setting ? $setting->value : $default;
  }

  public static function set($key, $value)
  {
    return self::updateOrCreate(
      ['key' => $key],
      ['value' => $value]
    );
  }
  public function getAllValue()
  {
    return [
      'site_name' => Setting::get('site_name'),
      'site_description' => Setting::get('site_description'),
      'site_keywords' => Setting::get('site_keywords'),
      'site_tagline' => Setting::get('site_tagline'),
      'site_og_image' => Setting::get('site_og_image'),
      'site_og_description' => Setting::get('site_og_description'),
      'site_og_title' => Setting::get('site_og_title'),
      'site_twitter_image' => Setting::get('site_twitter_image'),
      'site_twitter_description' => Setting::get('site_twitter_description'),
      'site_twitter_title' => Setting::get('site_twitter_title'),
      'site_email' => Setting::get('site_email'),
      'site_logo' => Setting::get('site_logo'),
      'site_favicon' => Setting::get('site_favicon'),
      'site_phone' => Setting::get('site_phone'),
      'enable_registration' => Setting::get('enable_registration')  === '1' ? true : false,
      'enable_documentation' => Setting::get('enable_documentation')  === '1' ? true : false,
    ];
  }
}
