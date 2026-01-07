<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoHelper
{

  public static function getQualityLabelFromSize(int $size): string
  {
    if ($size > 500 * 1024 * 1024) { // > 500MB
      return '1080p';
    } elseif ($size > 200 * 1024 * 1024) { // > 200MB
      return '720p';
    } elseif ($size > 100 * 1024 * 1024) { // > 100MB
      return '480p';
    } else {
      return '360p';
    }
  }


  protected static function createSource(string $file, string $type, string $label, bool $isDefault = false): array
  {
    $source = [
      'file' => $file,
      'type' => $type,
      'label' => $label,
    ];

    if ($isDefault) {
      $source['default'] = true;
    }

    return $source;
  }

  protected static function preferDefaultQuality(array $sources): array
  {
    $hasDefault = false;
    foreach ($sources as $s) {
      if (isset($s['default']) && $s['default'] === true) {
        $hasDefault = true;
        break;
      }
    }
    if ($hasDefault) {
      return $sources;
    }
    $preferred = self::getPreferredDefaultQuality();
    $preferredNum = null;
    if (preg_match('/([0-9]{3,4})/', $preferred, $pm)) {
      $preferredNum = (int)$pm[1];
    }
    $preferIdx = null;
    foreach ($sources as $i => $s) {
      $label = $s['label'] ?? '';
      if ($label === $preferred) {
        $preferIdx = $i;
        break;
      }
      if ($preferredNum !== null && preg_match('/([0-9]{3,4})/', (string)$label, $lm)) {
        if ((int)$lm[1] === $preferredNum) {
          $preferIdx = $i;
          break;
        }
      }
    }
    if ($preferIdx !== null) {
      $sources[$preferIdx]['default'] = true;
      return $sources;
    }
    $bestIdx = null;
    $bestNum = -1;
    foreach ($sources as $i => $s) {
      $label = (string)($s['label'] ?? '');
      $num = -1;
      if (preg_match('/([0-9]{3,4})/', $label, $lm)) {
        $num = (int)$lm[1];
      }
      if ($num > $bestNum) {
        $bestNum = $num;
        $bestIdx = $i;
      }
    }
    if ($bestIdx !== null && $bestNum > 0) {
      $sources[$bestIdx]['default'] = true;
      return $sources;
    }
    if (!empty($sources)) {
      $sources[0]['default'] = true;
    }
    return $sources;
  }

  protected static function getPreferredDefaultQuality(): string
  {
    $val = 'HD';
    if (!is_string($val) || $val === '') {
      return '720p';
    }
    return $val;
  }

  protected static function guessMimeTypeFromTitle(string $title): string
  {
    $extension = pathinfo($title, PATHINFO_EXTENSION);

    return match (strtolower($extension)) {
      'mp4' => 'video/mp4',
      'avi' => 'video/x-msvideo',
      'mov' => 'video/quicktime',
      'wmv' => 'video/x-ms-wmv',
      'flv' => 'video/x-flv',
      'mkv' => 'video/x-matroska',
      'webm' => 'video/webm',
      'm4v' => 'video/x-m4v',
      'mpg', 'mpeg' => 'video/mpeg',
      '3gp' => 'video/3gpp',
      default => 'video/mp4', // default to mp4
    };
  }

  protected static function getQualityFromResolution(int $width, int $height): string
  {
    if ($height >= 2160 || $width >= 3840) {
      return '2160p (4K)';
    } elseif ($height >= 1440 || $width >= 2560) {
      return '1440p (2K)';
    } elseif ($height >= 1080 || $width >= 1920) {
      return '1080p (Full HD)';
    } elseif ($height >= 720 || $width >= 1280) {
      return '720p (HD)';
    } elseif ($height >= 480 || $width >= 854) {
      return '480p';
    } elseif ($height >= 360 || $width >= 640) {
      return '360p';
    } else {
      return 'SD';
    }
  }

  protected static function getQualityFromHeight(int $height): string
  {
    if ($height >= 2160) {
      return '2160p (4K)';
    } elseif ($height >= 1440) {
      return '1440p (2K)';
    } elseif ($height >= 1080) {
      return '1080p (Full HD)';
    } elseif ($height >= 720) {
      return '720p (HD)';
    } elseif ($height >= 480) {
      return '480p';
    } elseif ($height >= 360) {
      return '360p';
    } else {
      return 'SD';
    }
  }

  protected static function extractArchiveIdentifier(string $url): ?string
  {
    $patterns = [
      // https://archive.org/details/thor-arrives-in-wakanda
      '/archive\.org\/details\/([a-zA-Z0-9_-]+)/',

      // https://archive.org/embed/thor-arrives-in-wakanda
      '/archive\.org\/embed\/([a-zA-Z0-9_-]+)/',

      // With additional path
      '/archive\.org\/details\/([a-zA-Z0-9_-]+)(?:\/|$)/',
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
      }
    }

    return null;
  }

  protected static function isCommandAvailable(string $command): bool
  {
    $which = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
    $output = null;
    $returnCode = null;

    exec("$which $command", $output, $returnCode);

    return $returnCode === 0;
  }

  protected static function normalizeIV(string $iv): string
  {
    $ivLength = 16;

    if ($iv === '' || $iv === null) {
      return str_repeat("\0", $ivLength);
    }

    if (strlen($iv) < $ivLength) {
      return str_pad($iv, $ivLength, "\0");
    } elseif (strlen($iv) > $ivLength) {
      return substr($iv, 0, $ivLength);
    }

    return $iv;
  }

  protected static function normalizeKey(string $key): string
  {
    $keyLength = 32;

    if (strlen($key) < $keyLength) {
      return str_pad($key, $keyLength, "\0");
    } elseif (strlen($key) > $keyLength) {
      return substr($key, 0, $keyLength);
    }

    return $key;
  }

  protected static function isVideoFile(string $filename): bool
  {
    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v', 'mpg', 'mpeg'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return in_array($extension, $videoExtensions);
  }

  protected static function determineQualityFromFilename(string $filename): string
  {
    $filename = strtolower($filename);

    if (preg_match('/([0-9]+)p/', $filename, $match)) {
      return $match[1] . 'p';
    }

    if (preg_match('/([0-9]+)x([0-9]+)/', $filename, $match)) {
      $height = (int)$match[2];
      return self::getQualityFromHeight($height);
    }

    if (str_contains($filename, '1080')) {
      return '1080p';
    }
    if (str_contains($filename, '720')) {
      return '720p';
    }
    if (str_contains($filename, '480')) {
      return '480p';
    }
    if (str_contains($filename, '360')) {
      return '360p';
    }

    return 'HD';
  }

  protected static function getMimeTypeFromFilename(string $filename): string
  {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return match ($extension) {
      'mp4', 'm4v' => 'video/mp4',
      'webm' => 'video/webm',
      'm3u8' => 'application/x-mpegURL',
      'ogg' => 'video/ogg',
      'mov' => 'video/quicktime',
      'avi' => 'video/x-msvideo',
      'mkv' => 'video/x-matroska',
      'flv' => 'video/x-flv',
      'wmv' => 'video/x-ms-wmv',
      'mpg', 'mpeg' => 'video/mpeg',
      default => 'video/mp4',
    };
  }

  protected static function extractVideoUrlsFromGoogleDataAlternative(array $data): array
  {
    $urls = [];

    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveArrayIterator($data),
      \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $key => $value) {
      if (is_string($value) && str_contains($value, 'video-download.googleusercontent.com')) {
        $quality = 'HD';

        if (preg_match('/=m([0-9]+)/', $value, $match)) {
          $quality = match ((int)$match[1]) {
            37 => '1080p',
            22 => '720p',
            18 => '360p',
            default => 'HD',
          };
        }

        $urls[$quality] = $value;
      }
    }

    return $urls;
  }

  protected static function getHttpOptions(): array
  {
    $verify = config('app.env') === 'production';
    $caBundle = config('services.video.ca_bundle');
    if ($caBundle) {
      $path = $caBundle;
      $isAbsolute = (strlen($caBundle) > 1 && $caBundle[1] === ':')
        || str_starts_with($caBundle, '\\')
        || str_starts_with($caBundle, '/');
      if (!$isAbsolute) {
        $path = base_path(ltrim($caBundle, '\/'));
      }
      if (is_string($path) && is_file($path)) {
        $verify = $path;
      }
    }
    return [
      'verify' => $verify,
      'timeout' => 30,
      'connect_timeout' => 10,
    ];
  }
}