<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomAdVastTag;
use App\Models\Video;
use App\Models\Subtitle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
  public function index(Request $request)
  {

    $defaultStartDate = now()->subDays(30)->format('Y-m-d 00:00:00');
    $defaultEndDate = now()->format('Y-m-d 23:59:59');

    $startDate = $request->input('start_date', $defaultStartDate);
    $endDate = $request->input('end_date', $defaultEndDate);
    $perPage = (int) $request->input('per_page', 10);
    $search = $request->input('search');
    $type = $request->input('type');
    $sortField = $request->input('sort_field', 'created_at');
    $sortDirection = $request->input('sort_direction', 'desc');

    try {
      $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $startDate);
      $endCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $endDate);
    } catch (\Exception $e) {
      $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $defaultStartDate);
      $endCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $defaultEndDate);
    }

    if (!in_array($perPage, [10, 20, 50, 100], true)) {
      $perPage = 10;
    }
    if (!in_array($sortDirection, ['asc', 'desc'], true)) {
      $sortDirection = 'desc';
    }

    $allowedSortFields = ['id', 'title', 'original_link', 'created_at', 'updated_at', 'type'];
    if (!in_array($sortField, $allowedSortFields, true)) {
      $sortField = 'created_at';
    }


    $query = Video::query()->with(['subtitles']);

    if ($search) {
      $query->where('title', 'like', '%' . $search . '%');
    }

    if ($type) {
      $query->where('type', $type);
    }

    $videos = $query
      ->whereBetween('created_at', [$startCarbon, $endCarbon])
      ->orderBy($sortField, $sortDirection)
      ->paginate($perPage)
      ->withQueryString();


    return inertia("admin/video/Index", [
      'videoPaginateProps' => [
        'videos' => $videos,
        'filters' => [
          'search' => $search,
          'type' => $type,
          'per_page' => $perPage,
          'page' => $videos->currentPage(),
          'start_date' => $startDate,
          'end_date' => $endDate,
          'sort_field' => $sortField,
          'sort_direction' => $sortDirection,
        ],
      ]
    ]);
  }

  public function create()
  {

    $adVastCount = CustomAdVastTag::count();
    return inertia("admin/video/Create", [
      'adVastCount' => $adVastCount,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'original_link' => 'required|string|max:255',
      'poster' => 'required|string|max:1000',
      'type' => 'required|string|max:255|in:' . implode(',', Video::TYPES),
      'ad_vast' => 'nullable|string|max:1000',
      'enable_popunder_ad' => 'required|boolean',
      'popunder_ad_code' => 'nullable|string|max:1000',
      'additional_ad_vast' => 'nullable|string|max:1000',
      'enable_button_download' => 'required|boolean',
      'download_link' => 'nullable|string|max:1000',
      'subtitles' => 'nullable|array',
      'subtitles.*.language' => 'required|string|size:2',
      'subtitles.*.name' => 'required|string|max:255',
      'subtitles.*.file' => 'required|file|mimes:vtt,srt|max:2048',
    ]);

    try {
      $videoData = [
        'title' => $validated['title'],
        'original_link' => $validated['original_link'],
        'poster' => $validated['poster'],
        'type' => $validated['type'],
        'ad_vast' => $validated['ad_vast'],
        'enable_popunder_ad' => $validated['enable_popunder_ad'],
        'popunder_ad_code' => $validated['popunder_ad_code'],
        'additional_ad_vast' => $validated['additional_ad_vast'],
        'enable_button_download' => $validated['enable_button_download'],
        'download_link' => $validated['download_link'],
      ];
      $video = Video::create($videoData);
      $this->processSubtitles($request, $video);
      return redirect()->route('admin.video.index')->with('success', 'Video created successfully');
    } catch (\Exception $e) {
      Log::error('Error storing video: ' . $e->getMessage());
      return redirect()->back()->with(['error' => 'An error occurred while storing the video.']);
    }
  }

  public function edit(string $id)
  {
    try {
      $video = Video::findOrFail($id);

      $adVastCount = CustomAdVastTag::count();

      return inertia("admin/video/Edit", [
        'video' => $video,
        'adVastCount' => $adVastCount,
      ]);
    } catch (\Exception $e) {
      Log::error('Error editing video: ' . $e->getMessage());
      return redirect()->route('admin.video.index')->with(['error' => 'An error occurred while editing the video.']);
    }
  }

  public function update(Request $request, string $id)
  {
    try {
      $video = Video::findOrFail($id);

      $rules = [
        'title' => 'required|string|max:255',
        'original_link' => 'required|string|max:255',
        'poster' => 'required|string|max:1000',
        'type' => 'required|string|max:255|in:' . implode(',', Video::TYPES),
        'ad_vast' => 'nullable|string|max:1000',
        'enable_popunder_ad' => 'required|boolean',
        'popunder_ad_code' => 'nullable|string|max:1000',
        'additional_ad_vast' => 'nullable|string|max:1000',
        'enable_button_download' => 'required|boolean',
        'download_link' => 'nullable|string|max:1000',
        'subtitles' => 'nullable|array',
        'subtitles.*.id' => 'nullable|exists:subtitles,id',
        'subtitles.*.language' => 'required|string|size:2',
        'subtitles.*.name' => 'required|string|max:255',
        'subtitles.*.file' => 'nullable|file|mimes:vtt,srt|max:2048',
      ];

      $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

      $validator->after(function ($validator) use ($request) {
        if ($request->has('subtitles') && is_array($request->subtitles)) {
          foreach ($request->subtitles as $index => $subtitle) {
            // If ID is missing (new subtitle), file is required
            if (empty($subtitle['id']) && empty($subtitle['file'])) {
              $validator->errors()->add("subtitles.{$index}.file", 'The subtitle file is required for new subtitles.');
            }
          }
        }
      });

      if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
      }

      $validated = $validator->validated();

      $video->update($validated);
      $this->processSubtitles($request, $video);
      return redirect()->route('admin.video.index')->with('success', 'Video updated successfully');
    } catch (\Exception $e) {
      Log::error('Error updating video: ' . $e->getMessage());
      return redirect()->back()->with(['error' => 'An error occurred while updating the video.']);
    }
  }

  public function destroy(string $id)
  {
    try {
      $video = Video::findOrFail($id);

      foreach ($video->subtitles as $subtitle) {
        if ($subtitle->url) {
          $path = str_replace('/storage/', '', parse_url($subtitle->url, PHP_URL_PATH));
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }

        $subtitle->delete();
      }
      $video->delete();
      return redirect()->route('admin.video.index')->with('success', 'Video deleted successfully');
    } catch (\Exception $e) {
      Log::error('Error deleting video: ' . $e->getMessage());
      return redirect()->route('admin.video.index')->with(['error' => 'An error occurred while deleting the video.']);
    }
  }

  public function bulkDestroy(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:videos,id'
    ]);

    $ids = $validated['ids'];

    if (empty($ids)) {
      if ($request->expectsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'No videos selected for deletion.'
        ], 422);
      }
      return redirect()->route('admin.video.index')->with('error', 'No videos selected for deletion.');
    }
    foreach ($ids as $vidId) {
      $subtitles = Subtitle::where('video_id', $vidId)->get();
      foreach ($subtitles as $subtitle) {
        if ($subtitle->url) {
          $path = str_replace('/storage/', '', parse_url($subtitle->url, PHP_URL_PATH));
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }
        $subtitle->delete();
      }
    }

    $deletedCount = Video::whereIn('id', $ids)->delete();

    if ($request->expectsJson()) {
      return response()->json([
        'success' => true,
        'message' => "Successfully deleted {$deletedCount} video(s).",
        'count' => $deletedCount
      ], 200);
    }

    return redirect()->route('admin.video.index')->with('success', 'Selected videos deleted successfully.');
  }

  private function processSubtitles(Request $request, Video $video): void
  {
    if (!$request->has('subtitles') || !is_array($request->subtitles)) {
      return;
    }

    $subtitles = $request->input('subtitles', []);
    $subtitleFiles = $request->file('subtitles', []);

    // If $request->subtitles already contains files (because of PHP's handling of file arrays), use it.
    // Otherwise, try to merge from $request->file().
    // Note: $request->subtitles dynamic property usually does the merge.
    // But let's iterate carefully.

    $inputSubtitles = $request->subtitles;
    $processedIds = [];

    foreach ($inputSubtitles as $index => $subtitleData) {
      // Ensure file is present if it's in the files array but not in the input array (rare but possible)
      if (!isset($subtitleData['file']) && isset($subtitleFiles[$index]['file'])) {
        $subtitleData['file'] = $subtitleFiles[$index]['file'];
      }

      // Update existing subtitle
      if (!empty($subtitleData['id'])) {
        $processedIds[] = $subtitleData['id'];
        $subtitle = $video->subtitles()->find($subtitleData['id']);
        if ($subtitle) {
          $updateData = [
            'language' => $subtitleData['language'] ?? $subtitle->language,
            'name' => $subtitleData['name'] ?? $subtitle->name,
          ];

          // If a new file is uploaded, replace the old one
          if (isset($subtitleData['file']) && $subtitleData['file'] instanceof \Illuminate\Http\UploadedFile && $subtitleData['file']->isValid()) {
            // Delete old file
            if ($subtitle->url) {
              $oldPath = str_replace('/storage/', '', parse_url($subtitle->url, PHP_URL_PATH));
              if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
              }
            }

            $file = $subtitleData['file'];
            $base = Str::slug($video->id . '_' . ($updateData['language']) . '_' . time());
            $ext = $file->getClientOriginalExtension();
            $filename = $base . '.' . $ext;
            $path = $file->storeAs('subtitles', $filename, 'public');

            $updateData['url'] = Storage::url($path);
            $updateData['type'] = $ext;
          }

          $subtitle->update($updateData);
        }
      }
      // Create new subtitle
      else {
        if (isset($subtitleData['file']) && $subtitleData['file'] instanceof \Illuminate\Http\UploadedFile && $subtitleData['file']->isValid()) {
          $file = $subtitleData['file'];
          $language = $subtitleData['language'] ?? 'en';
          $name = $subtitleData['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

          $base = Str::slug($video->id . '_' . $language . '_' . time());
          $ext = $file->getClientOriginalExtension();
          $filename = $base . '.' . $ext;

          $path = $file->storeAs('subtitles', $filename, 'public');

          $newSubtitle = $video->subtitles()->create([
            'language' => $language,
            'name' => $name,
            'url' => Storage::url($path),
            'type' => $file->getClientOriginalExtension(),
          ]);

          $processedIds[] = $newSubtitle->id;
        }
      }
    }

    // Delete missing subtitles
    $existingSubtitles = $video->subtitles()->get();
    foreach ($existingSubtitles as $existingSubtitle) {
      if (!in_array($existingSubtitle->id, $processedIds)) {
        // Delete file from storage
        if ($existingSubtitle->url) {
          $path = str_replace('/storage/', '', parse_url($existingSubtitle->url, PHP_URL_PATH));
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }
        $existingSubtitle->delete();
      }
    }
  }
}
