<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

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

    $scriptPath = base_path('scrapers/youtube.js');

    // Use 'node' assuming it's in the PATH. If not, might need full path.
    // In Laragon, it is in path.
    $process = new Process(['node', $scriptPath, $link]);
    $process->setTimeout(60); // 60 seconds timeout

    // Environment variables handling for cross-platform compatibility
    $env = [
      'PATH' => getenv('PATH'),
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'),
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
      Log::error('YouTube Scraper Failed', [
        'link' => $link,
        'output' => $process->getOutput(),
        'error' => $process->getErrorOutput(),
        'exitCode' => $process->getExitCode()
      ]);
      return response()->json([
        'success' => false,
        'error' => $process->getErrorOutput() ?: 'Failed to fetch YouTube info'
      ], 500);
    }

    $output = $process->getOutput();
    $sources = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error('YouTube Scraper Invalid JSON', ['output' => $output]);
      return response()->json([
        'success' => false,
        'error' => 'Invalid output from scraper'
      ], 500);
    }

    // Proxy the YouTube URLs to avoid 403 Forbidden / IP blocking
    foreach ($sources as &$source) {
      if (isset($source['file']) && strpos($source['file'], 'googlevideo.com') !== false) {
        $source['file'] = route('video.stream', ['url' => $source['file']]);
      }
    }

    return response()->json($sources);
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
      'HOME' => getenv('HOME') ?: getenv('USERPROFILE'), // Linux uses HOME, Windows uses USERPROFILE
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
