<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

class VideoController extends Controller
{
  protected $isProduction = false;

  public function __construct()
  {
    $this->isProduction = env('APP_ENV') === 'production';
  }

  public function youtube(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    try {
      $yt = new YoutubeDl();

      // Set path to yt-dlp binary if not in global PATH, or ensure it's detectable
      // On Windows/Laragon it seems to be in PATH.
      // If needed: $yt->setBinPath('/usr/bin/yt-dlp');

      $options = Options::create()
        ->downloadPath(sys_get_temp_dir())
        ->skipDownload(true)
        ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
        ->extractorArgs('youtube', (function () {
          $raw = env('YTDL_YOUTUBE_EXTRACTOR_ARGS');
          if ($raw) return $raw;
          $client = env('YTDL_YOUTUBE_CLIENT', 'android');
          $po = env('YTDL_PO_TOKEN');
          return $po ? ("player_client={$client};po_token={$po}") : ("player_client={$client}");
        })())
        ->header('Accept-Language', 'en-US,en;q=0.9')
        ->header('Referer', 'https://www.youtube.com/')
        ->header('Origin', 'https://www.youtube.com')
        ->url($link)
        ->format('bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best');

      $cacheDir = env('YTDL_CACHE_DIR');
      if ($cacheDir) {
        $options = $options->cacheDir($cacheDir);
      }

      $proxy = env('YTDL_PROXY');
      if ($proxy) {
        $options = $options->proxy($proxy);
      }
      $sourceAddress = env('YTDL_SOURCE_ADDRESS');
      if ($sourceAddress) {
        $options = $options->sourceAddress($sourceAddress);
      }
      $sleepInterval = env('YTDL_SLEEP_INTERVAL');
      if ($sleepInterval) {
        $options = $options->sleepInterval((int)$sleepInterval);
      }

      // Use cookies if available
      $cookiesPath = env('YTDL_COOKIES_PATH');
      if (!$cookiesPath || !file_exists($cookiesPath)) {
        $localCookies = base_path('scrapers/youtube-cookies.json');
        if (file_exists($localCookies)) {
          $cookiesPath = $localCookies;
        }
      }

      $tempCookiesFile = null;
      $cookiesJsonEnv = env('YTDL_COOKIES_JSON');
      if ($cookiesJsonEnv) {
        $json = json_decode($cookiesJsonEnv, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
          $names = array_map(function ($c) {
            return $c['name'] ?? '';
          }, $json);
          $hasSecureSession = in_array('__Secure-3PSID', $names, true) || in_array('__Secure-3PSIDTS', $names, true) || in_array('__Secure-3PSIDCC', $names, true);
          Log::info('YTDL cookies (env JSON) parsed', [
            'count' => count($names),
            'has_secure_session' => $hasSecureSession,
          ]);
          $netscapeContent = "# Netscape HTTP Cookie File\n";
          foreach ($json as $cookie) {
            $domain = $cookie['domain'] ?? '';
            $flag = strpos($domain, '.') === 0 ? 'TRUE' : 'FALSE';
            $path = $cookie['path'] ?? '/';
            $secure = ($cookie['secure'] ?? false) ? 'TRUE' : 'FALSE';
            $expiration = isset($cookie['expirationDate']) ? (int)$cookie['expirationDate'] : (time() + 31536000);
            $name = $cookie['name'] ?? '';
            $value = $cookie['value'] ?? '';
            $netscapeContent .= "{$domain}\t{$flag}\t{$path}\t{$secure}\t{$expiration}\t{$name}\t{$value}\n";
          }
          $tempCookiesFile = tempnam(sys_get_temp_dir(), 'ytdl_cookies_');
          file_put_contents($tempCookiesFile, $netscapeContent);
          $options->cookies($tempCookiesFile);
        }
      } elseif ($cookiesPath && file_exists($cookiesPath)) {
        // Check if file is JSON and convert to Netscape format if needed
        $content = file_get_contents($cookiesPath);
        $json = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
          $names = array_map(function ($c) {
            return $c['name'] ?? '';
          }, $json);
          $hasSecureSession = in_array('__Secure-3PSID', $names, true) || in_array('__Secure-3PSIDTS', $names, true) || in_array('__Secure-3PSIDCC', $names, true);
          Log::info('YTDL cookies (file JSON) parsed', [
            'path' => $cookiesPath,
            'count' => count($names),
            'has_secure_session' => $hasSecureSession,
          ]);
          // Convert JSON to Netscape format
          $netscapeContent = "# Netscape HTTP Cookie File\n";
          foreach ($json as $cookie) {
            $domain = $cookie['domain'] ?? '';
            $flag = strpos($domain, '.') === 0 ? 'TRUE' : 'FALSE';
            $path = $cookie['path'] ?? '/';
            $secure = ($cookie['secure'] ?? false) ? 'TRUE' : 'FALSE';
            $expiration = isset($cookie['expirationDate']) ? (int)$cookie['expirationDate'] : (time() + 31536000);
            $name = $cookie['name'] ?? '';
            $value = $cookie['value'] ?? '';

            $netscapeContent .= "{$domain}\t{$flag}\t{$path}\t{$secure}\t{$expiration}\t{$name}\t{$value}\n";
          }

          $tempCookiesFile = tempnam(sys_get_temp_dir(), 'ytdl_cookies_');
          file_put_contents($tempCookiesFile, $netscapeContent);
          $options->cookies($tempCookiesFile);
        } else {
          // Assume it's already Netscape format
          $lines = preg_split('/\r\n|\r|\n/', $content);
          $names = [];
          foreach ($lines as $line) {
            if (!$line || $line[0] === '#') continue;
            $parts = preg_split('/\t/', $line);
            if (count($parts) >= 7) {
              $names[] = $parts[5];
            }
          }
          $hasSecureSession = in_array('__Secure-3PSID', $names, true) || in_array('__Secure-3PSIDTS', $names, true) || in_array('__Secure-3PSIDCC', $names, true);
          Log::info('YTDL cookies (file Netscape) parsed', [
            'path' => $cookiesPath,
            'count' => count($names),
            'has_secure_session' => $hasSecureSession,
          ]);
          $options->cookies($cookiesPath);
        }
      }

      $collection = $yt->download($options);

      // Cleanup temp cookies
      if ($tempCookiesFile && file_exists($tempCookiesFile)) {
        @unlink($tempCookiesFile);
      }

      $sources = [];
      foreach ($collection->getVideos() as $video) {
        if ($video->getError() !== null) {
          Log::error('YoutubeDl Error: ' . $video->getError());
          continue;
        }

        if ($video->getUrl()) {
          $label = $video->getHeight() ? $video->getHeight() . 'p' : 'Auto';
          $sources[] = [
            'file' => $video->getUrl(),
            'type' => 'video/mp4',
            'label' => $label,
            'default' => $label === '720p' || $label === 'Auto',
          ];
        }

        $fmts = $video->get('formats', []);
        foreach ($fmts as $fmt) {
          $url = $fmt->getUrl();
          if (!$url) {
            continue;
          }
          $ext = $fmt->getExt();
          $vcodec = $fmt->getVcodec();
          $acodec = $fmt->getAcodec();
          if ($ext === 'mp4' && $vcodec && $vcodec !== 'none' && $acodec && $acodec !== 'none') {
            $height = $fmt->getHeight();
            $label = $height ? ($height . 'p') : ($fmt->getFormatNote() ?: 'Auto');
            $sources[] = [
              'file' => $url,
              'type' => 'video/mp4',
              'label' => $label,
              'default' => $label === '720p' || $label === 'Auto',
            ];
          }
        }
      }

      if (empty($sources)) {
        $jsRuntime = env('YTDL_JS_RUNTIMES');
        if ($jsRuntime) {
          $cookieFile = $tempCookiesFile && file_exists($tempCookiesFile) ? $tempCookiesFile : ($cookiesPath && file_exists($cookiesPath) ? $cookiesPath : null);
          $extractorArgs = env('YTDL_YOUTUBE_EXTRACTOR_ARGS', (function () {
            $client = env('YTDL_YOUTUBE_CLIENT', 'android');
            $po = env('YTDL_PO_TOKEN');
            return $po ? ("player_client={$client};po_token={$po}") : ("player_client={$client}");
          })());
          $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://www.youtube.com/',
            'Origin: https://www.youtube.com'
          ];
          $args = [
            'yt-dlp',
            '--ignore-config',
            '--ignore-errors',
            '--dump-json',
            '--format',
            'bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best',
            '--extractor-args',
            "youtube:{$extractorArgs}",
            '--js-runtimes',
            $jsRuntime,
          ];
          foreach ($headers as $h) {
            $args[] = '--add-header';
            $args[] = $h;
          }
          if ($cookieFile) {
            $args[] = '--cookies';
            $args[] = $cookieFile;
          }
          if ($proxy) {
            $args[] = '--proxy';
            $args[] = $proxy;
          }
          $args[] = $link;
          $proc = new Process($args);
          $proc->setTimeout(60);
          $proc->run();
          if ($proc->isSuccessful()) {
            $lines = preg_split('/\r\n|\r|\n/', $proc->getOutput());
            foreach ($lines as $line) {
              $line = trim($line);
              if ($line === '') continue;
              $data = json_decode($line, true);
              if (!is_array($data)) continue;
              $fmts = $data['formats'] ?? [];
              foreach ($fmts as $fmt) {
                $url = $fmt['url'] ?? null;
                if (!$url) continue;
                $ext = $fmt['ext'] ?? null;
                $vcodec = $fmt['vcodec'] ?? null;
                $acodec = $fmt['acodec'] ?? null;
                $height = $fmt['height'] ?? null;
                if ($ext === 'mp4' && $vcodec && $vcodec !== 'none' && $acodec && $acodec !== 'none') {
                  $label = $height ? ($height . 'p') : ($fmt['format_note'] ?? 'Auto');
                  $sources[] = [
                    'file' => $url,
                    'type' => 'video/mp4',
                    'label' => $label,
                    'default' => $label === '720p' || $label === 'Auto',
                  ];
                }
              }
            }
          } else {
            Log::error('yt-dlp raw failed', ['stderr' => $proc->getErrorOutput()]);
          }
        }
        if (empty($sources)) {
          throw new \Exception('No streaming data found via yt-dlp');
        }
      } {
        $bestIndex = null;
        $bestRes = 0;
        foreach ($sources as $i => $source) {
          $label = isset($source['label']) ? $source['label'] : '';
          $type = isset($source['type']) ? $source['type'] : '';
          $isMp4 = stripos($type, 'mp4') !== false;
          if (!$isMp4) {
            continue;
          }
          $res = 0;
          if (preg_match('/(\d+)\s*p/i', (string)$label, $m)) {
            $res = (int)$m[1];
          } elseif (is_string($label) && strtolower($label) === 'auto') {
            $res = 360;
          }
          if ($res > $bestRes) {
            $bestRes = $res;
            $bestIndex = $i;
          }
        }
        if ($bestIndex === null) {
          foreach ($sources as $i => $source) {
            if ((isset($source['type']) ? $source['type'] : '') === 'application/x-mpegURL') {
              $bestIndex = $i;
              break;
            }
          }
        }
        if ($bestIndex === null && !empty($sources)) {
          $bestIndex = 0;
        }
        foreach ($sources as &$s) {
          $s['default'] = false;
        }
        if ($bestIndex !== null) {
          $sources[$bestIndex]['default'] = true;
        }
      }

      // Proxy the YouTube URLs to avoid 403 Forbidden / IP blocking
      foreach ($sources as &$source) {
        if (isset($source['file']) && strpos($source['file'], 'googlevideo.com') !== false) {
          $source['file'] = route('video.stream', ['url' => $source['file']]);
        }
      }

      return response()->json($sources);
    } catch (\Exception $e) {
      Log::error('YouTube Scraper Failed (yt-dlp)', [
        'link' => $link,
        'error' => $e->getMessage()
      ]);
      return response()->json([
        'success' => false,
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function rumble(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/rumble.js');

    // Use 'node' assuming it's in the PATH.
    // Explicitly pass environment variables to avoid OpenSSL/CSPRNG errors on Windows
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      // Ensure HOME is set, fallback to /tmp if empty (crucial for Puppeteer in Docker)
      'HOME' => (getenv('HOME') ?: getenv('USERPROFILE')) ?: '/tmp',
      'PUPPETEER_EXECUTABLE_PATH' => env('VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows-specific variables for Node.js/OpenSSL
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      $errorOutput = $process->getErrorOutput();
      Log::error('Rumble Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $errorOutput,
        'exitCode' => $process->getExitCode()
      ]);

      // Fallback to error response
      return response()->json([
        'success' => false,
        'error' => $errorOutput ?: 'Failed to fetch Rumble info'
      ], 500);
    }

    $output = $process->getOutput();
    $sources = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Rumble Scraper Invalid JSON', ['output' => $output]);
      // If output is not valid JSON, it might be an error string or empty
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper: ' . $output
      ], 500);
    }

    // Check if the script returned an error object
    if (isset($sources['error'])) {
      Log::error('Rumble Scraper Error Object', ['error' => $sources['error']]);
      return response()->json([
        'success' => false,
        'error' => $sources['error']
      ], 500);
    }

    return response()->json($sources);
  }

  public function facebook(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/facebook.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Facebook Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Facebook info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Facebook Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    return response()->json($result);
  }

  public function archive(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/archive.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Archive Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Archive info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Archive Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    return response()->json(['sources' => $result]);
  }

  public function googlePhoto(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/google_photo.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Google Photo Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Google Photo info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Google Photo Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    if (isset($result['sources']) && is_array($result['sources'])) {
      foreach ($result['sources'] as &$source) {
        if (isset($source['file'])) {
          // Wrap the URL in the stream proxy
          $source['file'] = route('video.stream', ['url' => $source['file']]);
        }
      }
    }

    return response()->json($result);
  }

  public function googleDrive(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/google_drive.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Google Drive Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Google Drive info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Google Drive Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    if (isset($result['sources']) && is_array($result['sources'])) {
      foreach ($result['sources'] as &$source) {
        if (isset($source['file'])) {
          // Wrap the URL in the stream proxy
          $source['file'] = route('video.stream', ['url' => $source['file']]);
        }
      }
    }

    return response()->json($result);
  }

  public function mega(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/mega.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Mega Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Mega info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Mega Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    if (isset($result['sources']) && is_array($result['sources'])) {
      foreach ($result['sources'] as &$source) {
        if (isset($source['file'])) {
          // Wrap the URL in the stream proxy
          $source['file'] = route('video.stream-mega', ['url' => $source['file']]);
        }
      }
    }

    return response()->json($result);
  }

  public function yandexDisk(Request $request)
  {
    $link = $request->input('link');

    if (!$link) {
      return response()->json([
        'success' => false,
        'error' => 'No link provided'
      ], 400);
    }

    $scriptPath = base_path('scrapers/yandex_disk.js');

    // Use 'node' assuming it's in the PATH.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(120);

    // Environment variables handling
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => (getenv('HOME') ?: getenv('USERPROFILE')) ?: '/tmp',
      'PUPPETEER_EXECUTABLE_PATH' => env('VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Yandex Disk Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch Yandex Disk info'
      ], 500);
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('Yandex Disk Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    if (isset($result['sources']) && is_array($result['sources'])) {
      foreach ($result['sources'] as &$source) {
        if (isset($source['file'])) {
          // Wrap the URL in the stream proxy because Yandex download links expire and are IP bound
          $source['file'] = route('video.stream', ['url' => $source['file']]);
        }
      }
    }

    return response()->json($result);
  }

  public function streamMega(Request $request)
  {
    $url = $request->query('url');
    if (!$url) {
      abort(404);
    }

    // 1. Get file size first (head request logic equivalent)
    // For performance, we might cache this or pass it in query params.
    // But secure way is to check again or trust the request?
    // Let's use a quick node script or just assume client knows?
    // Actually, we need to know the size to handle Range requests properly (Content-Range).

    // Optimisation: User cached metadata if possible.
    // For now, let's run a lightweight node script to get size OR just rely on Node stream.
    // Wait, PHP needs to send headers BEFORE outputting body.

    $scriptPath = base_path('scrapers/mega.js');
    $process = new Process(['node', $scriptPath, $url]);

    // Environment variables handling
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
      $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
      $env['TEMP'] = getenv('TEMP');
      $env['TMP'] = getenv('TMP');
    }

    $process->setEnv($env);

    $process->run();

    if (!$process->isSuccessful()) {
      Log::error('Mega Stream Info Failed', [
        'url' => $url,
        'error' => $process->getErrorOutput(),
        'output' => $process->getOutput()
      ]);
      abort(500, 'Failed to get file info');
    }

    $info = json_decode($process->getOutput(), true);
    if (!$info || !isset($info['size'])) {
      abort(500, 'Invalid file info');
    }

    $size = $info['size'];
    $mime = 'video/mp4'; // Default
    $filename = $info['title'] ?? 'video.mp4';

    // Handle Range Header
    $start = 0;
    $end = $size - 1;
    $length = $size;
    $isRange = false;

    if ($request->header('Range')) {
      $cRange = $request->header('Range');
      if (preg_match('/bytes=(\d+)-(\d+)?/', $cRange, $matches)) {
        $start = intval($matches[1]);
        if (isset($matches[2])) {
          $end = intval($matches[2]);
        }
        $isRange = true;
      }
    }

    $length = $end - $start + 1;

    $headers = [
      'Content-Type' => $mime,
      'Content-Length' => $length,
      'Content-Disposition' => 'inline; filename="' . $filename . '"',
      'Accept-Ranges' => 'bytes',
    ];

    if ($isRange) {
      $headers['Content-Range'] = "bytes $start-$end/$size";
      $status = 206;
    } else {
      $status = 200;
    }

    return response()->stream(function () use ($url, $start, $end) {
      $scriptPath = base_path('scrapers/mega_stream.js');

      // Pass start and end to the node script
      $cmd = ['node', $scriptPath, $url, (string)$start, (string)$end];

      $process = new Process($cmd);
      $process->setTimeout(3600); // 1 hour

      // Environment variables handling
      $env = [
        'PATH' => getenv('PATH'),
        'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
      ];

      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
        $env['SystemDrive'] = getenv('SystemDrive') ?: 'C:';
        $env['TEMP'] = getenv('TEMP');
        $env['TMP'] = getenv('TMP');
      }

      $process->setEnv($env);

      // We need to stream the output directly
      $process->run(function ($type, $buffer) {
        if ($type === Process::OUT) {
          echo $buffer;
          flush();
        }
      });
    }, $status, $headers);
  }

  public function stream(Request $request)
  {
    $url = $request->query('url');
    if (!$url) {
      abort(404);
    }

    // We need to forward headers, especially Range.
    $headers = [];
    if ($request->header('Range')) {
      $headers['Range'] = $request->header('Range');
    }
    $headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    // Handle YouTube specific clients if c= parameter is present
    if (strpos($url, 'googlevideo.com') !== false) {
      if (strpos($url, 'c=ANDROID') !== false) {
        $headers['User-Agent'] = 'com.google.android.youtube/17.31.35 (Linux; U; Android 11) gzip';
      } elseif (strpos($url, 'c=IOS') !== false) {
        $headers['User-Agent'] = 'com.google.ios.youtube/17.33.2 (iPhone14,3; U; CPU iOS 15_6 like Mac OS X)';
      } elseif (strpos($url, 'c=WEB') !== false) {
        $headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
      } elseif (strpos($url, 'c=TVHTML5') !== false) {
        // Fallback if TVHTML5 still appears (shouldn't with scrapers update)
        // Use a Smart TV UA
        $headers['User-Agent'] = 'Mozilla/5.0 (SMART-TV; Linux; Tizen 2.4.0) AppleWebkit/538.1 (KHTML, like Gecko) SamsungBrowser/1.0 TV Safari/538.1';
      }

      $headers['Referer'] = 'https://www.youtube.com/';
      $headers['Origin'] = 'https://www.youtube.com';
    }

    // Use Guzzle directly for streaming
    $client = new \GuzzleHttp\Client();

    try {
      $response = $client->request('GET', $url, [
        'headers' => $headers,
        'stream' => true,
        'http_errors' => false, // Don't throw on 4xx/5xx
        'verify' => false,
        'allow_redirects' => true
      ]);

      $statusCode = $response->getStatusCode();
      $responseHeaders = $response->getHeaders();
      $body = $response->getBody();

      // If upstream returns 403/404, pass it through
      if ($statusCode >= 400) {
        return response("Upstream error: $statusCode", $statusCode);
      }

      // Forward specific headers to the client
      $forwardHeaders = [
        'Content-Type',
        'Content-Length',
        'Content-Range',
        'Accept-Ranges',
        'Content-Disposition'
      ];

      // If this is an HLS manifest, rewrite absolute URLs to proxy through this endpoint
      $contentType = isset($responseHeaders['Content-Type']) ? (is_array($responseHeaders['Content-Type']) ? $responseHeaders['Content-Type'][0] : $responseHeaders['Content-Type']) : '';
      $isHls = (stripos($contentType, 'application/vnd.apple.mpegurl') !== false) || (stripos($contentType, 'application/x-mpegURL') !== false) || (stripos($url, '.m3u8') !== false);
      if ($isHls) {
        try {
          $manifest = $body->getContents();
          $parsed = parse_url($url);
          $scheme = $parsed['scheme'] ?? 'https';
          $host = $parsed['host'] ?? '';
          $path = $parsed['path'] ?? '';
          $dir = rtrim(substr($path, 0, strrpos($path, '/') !== false ? strrpos($path, '/') : strlen($path)), '/');
          $base = $scheme . '://' . $host . ($dir ? $dir . '/' : '/');
          $lines = preg_split('/\r\n|\r|\n/', $manifest);
          $out = [];
          foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '#')) {
              $out[] = $line;
              continue;
            }
            if (preg_match('/^https?:\\/\\//i', $trim)) {
              $proxied = route('video.stream', ['url' => $trim]);
              $out[] = $proxied;
            } else {
              $abs = $base . ltrim($trim, '/');
              $proxied = route('video.stream', ['url' => $abs]);
              $out[] = $proxied;
            }
          }
          $rewritten = implode("\n", $out);
          // Remove Content-Length since content changed
          unset($responseHeaders['Content-Length']);
          $headersOut = array_intersect_key($responseHeaders, array_flip($forwardHeaders));
          $headersOut['Content-Type'] = 'application/vnd.apple.mpegurl';
          return response($rewritten, 200, $headersOut);
        } catch (\Exception $e) {
          Log::warning('Failed to rewrite HLS manifest: ' . $e->getMessage());
          // Fallback to streaming as-is
        }
      }

      return response()->stream(function () use ($body) {
        while (!$body->eof()) {
          echo $body->read(1024 * 64); // 64KB chunks
          flush();
        }
      }, $statusCode, array_intersect_key($responseHeaders, array_flip($forwardHeaders)));
    } catch (\Exception $e) {
      Log::error("Stream failed: " . $e->getMessage());
      return response("Error streaming video", 500);
    }
  }
}