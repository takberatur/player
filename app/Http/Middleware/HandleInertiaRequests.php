<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that's loaded on the first page visit.
   *
   * @see https://inertiajs.com/server-side-setup#root-template
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determines the current asset version.
   *
   * @see https://inertiajs.com/asset-versioning
   */
  public function version(Request $request): ?string
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @see https://inertiajs.com/shared-data
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

    return [
      ...parent::share($request),
      'name' => config('app.name'),
      'quote' => ['message' => trim($message), 'author' => trim($author)],
      'auth' => [
        'user' => $request->user(),
      ],
      'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
      'setting' => [
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
      ],
      'release_date' => Setting::select('created_at')->first()->created_at->format('F d, Y'),
      'status' => Session::get('status'),
      'success' => Session::get('success'),
      'error' => Session::get('error'),
      'flash' => [
        'status' => Session::get('status'),
        'success' => Session::get('success'),
        'error' => Session::get('error'),
        'reload' => Session::get('reload'),
      ],
    ];
  }
}
