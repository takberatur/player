<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebController extends Controller
{
  public function index()
  {
    return inertia('admin/settings/Web');
  }
  public function update(Request $request)
  {
    $request->validate([
      'enable_registration' => 'required|boolean',
      'enable_documentation' => 'required|boolean',
      'site_name' => 'required|string|max:255',
      'site_description' => 'required|string|max:255',
      'site_keywords' => 'nullable|string|max:255',
      'site_tagline' => 'nullable|string|max:255',
      'site_og_image' => 'nullable|string|max:255',
      'site_og_description' => 'nullable|string|max:255',
      'site_og_title' => 'nullable|string|max:255',
      'site_twitter_image' => 'nullable|string|max:255',
      'site_twitter_description' => 'nullable|string|max:255',
      'site_twitter_title' => 'nullable|string|max:255',
      'site_email' => 'nullable|email|max:255',
      'site_phone' => 'nullable|string|max:255',
      'site_logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
      'site_favicon' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
    ]);

    $keys = [
      'enable_registration',
      'enable_documentation',
      'site_name',
      'site_tagline',
      'site_description',
      'site_keywords',
      'site_og_image',
      'site_og_description',
      'site_og_title',
      'site_twitter_image',
      'site_twitter_description',
      'site_twitter_title',
      'site_email',
      'site_phone',
    ];

    try {
      foreach ($keys as $key) {
        // Handle enable_registration specifically because checkboxes might not be sent if unchecked (though Inertia usually handles this)
        if ($key === 'enable_registration') {
          $value = $request->boolean('enable_registration') ? '1' : '0';
          Setting::set($key, $value);
          continue;
        }
        // Handle enable_documentation specifically because checkboxes might not be sent if unchecked (though Inertia usually handles this)
        if ($key === 'enable_documentation') {
          $value = $request->boolean('enable_documentation') ? '1' : '0';
          Setting::set($key, $value);
          continue;
        }

        if ($request->has($key)) {
          $value = $request->input($key);

          if ($value === null) {
            $value = '';
          }

          if (is_array($value)) {
            $value = implode(',', $value);
          }
          Setting::set($key, $value);
        }
      }


      if ($request->hasFile('site_logo')) {
        $oldLogo = Setting::get('site_logo');
        if ($oldLogo) {
          $path = str_replace('/storage/', '', parse_url($oldLogo, PHP_URL_PATH));
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }
        $path = $request->file('site_logo')->store('sites', 'public');
        $url = Storage::url($path);

        Setting::set('site_logo', $url);
        Setting::set('site_og_image', $url);
        Setting::set('site_twitter_image', $url);
      }

      if ($request->hasFile('site_favicon')) {
        $oldFavicon = Setting::get('site_favicon');
        if ($oldFavicon) {
          $path = str_replace('/storage/', '', parse_url($oldFavicon, PHP_URL_PATH));
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }
        $path = $request->file('site_favicon')->store('sites', 'public');
        $url = Storage::url($path);

        Setting::set('site_favicon', $url);
      }

      return back()->with('success', 'Settings updated successfully');
    } catch (\Exception $e) {
      return back()->with('error', $e->getMessage());
    }
  }
}
