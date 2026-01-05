<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomAdVastTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CustomAdVastTagController extends Controller
{
  public function index(Request $request)
  {

    $defaultStartDate = now()->subDays(30)->format('Y-m-d 00:00:00');
    $defaultEndDate = now()->format('Y-m-d 23:59:59');

    $startDate = $request->input('start_date', $defaultStartDate);
    $endDate = $request->input('end_date', $defaultEndDate);
    $perPage = (int) $request->input('per_page', 10);
    $search = $request->input('search');

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

    $query = CustomAdVastTag::query();

    if ($search) {
      $query->where('name', 'like', '%' . $search . '%');
    }

    $adVastTags = $query
      ->whereBetween('created_at', [$startCarbon, $endCarbon])
      ->orderBy('created_at', 'desc')
      ->paginate($perPage)
      ->withQueryString();

    return inertia('admin/advast/Index', [
      'adVastTagPaginateProps' => [
        'adVastTags' => $adVastTags,
        'filters' => [
          'search' => $search,
          'per_page' => $perPage,
          'page' => $adVastTags->currentPage(),
          'start_date' => $startDate,
          'end_date' => $endDate,
        ],
      ],
    ]);
  }

  public function create()
  {
    return inertia('admin/advast/Create');
  }

  public function store(Request $request)
  {
    try {
      $request->validate([
        'name' => 'required|string|max:255',
        'video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,qt|max:51200', // Max 50MB
        'video_url' => 'required_without:video_file|nullable|url',
        'direct_link_ad' => 'required|url',
        'duration' => 'required|string', // Format: 00:00:15
        'additional_direct_link_ad' => 'nullable|url',
        'tracking_url' => 'nullable|url',
      ]);

      $data = $request->except(['file_url']); // prevent file_url injection

      if ($request->hasFile('video_file')) {
        $path = $request->file('video_file')->store('ad-videos', 'public');
        $data['video_url'] = Storage::url($path);
      }

      // Generate UUID first
      $uuid = (string) \Illuminate\Support\Str::uuid();

      // Create instance manually to set ID and generate XML before save
      $ad = new CustomAdVastTag($data);
      $ad->id = $uuid;

      // Generate VAST XML Content
      $xmlContent = $this->generateVastXml($ad);

      // Save XML to Storage
      $fileName = 'vast_' . $uuid . '.xml';
      Storage::disk('public')->put('vast/' . $fileName, $xmlContent);

      // Set file_url and save to DB
      $ad->file_url = Storage::url('vast/' . $fileName);
      $ad->save();

      return redirect()->route('admin.custom-ads.index')->with('success', 'Ad created and VAST XML generated successfully');
    } catch (\Throwable $th) {
      \Illuminate\Support\Facades\Log::error('Ad Create Error: ' . $th->getMessage());
      return redirect()->back()->with('error', $th->getMessage());
    }
  }

  public function edit($id)
  {
    $adVastTag = CustomAdVastTag::findOrFail($id);

    return inertia('admin/advast/Edit', [
      'adVastTag' => $adVastTag,
    ]);
  }

  public function update(Request $request, $id)
  {
    try {
      $request->validate([
        'name' => 'required|string|max:255',
        'video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,qt|max:51200',
        'video_url' => 'required_without:video_file|nullable|url',
        'direct_link_ad' => 'required|url',
        'duration' => 'required|string', // Format: 00:00:15
        'additional_direct_link_ad' => 'nullable|url',
        'tracking_url' => 'nullable|url',
      ]);

      $adVastTag = CustomAdVastTag::findOrFail($id);
      $data = $request->except(['file_url']); // prevent file_url injection

      // Check if current video is local storage
      $isOldLocal = $adVastTag->video_url && str_contains($adVastTag->video_url, '/storage/ad-videos/');

      if ($request->hasFile('video_file')) {
        // If uploading new file, delete old local file if exists
        if ($isOldLocal) {
          $oldPath = str_replace('/storage/', '', parse_url($adVastTag->video_url, PHP_URL_PATH));
          Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('video_file')->store('ad-videos', 'public');
        $data['video_url'] = Storage::url($path);
      } elseif (isset($data['video_url'])) {
        // Check if URL actually changed (ignoring domain/protocol differences)
        $newPath = parse_url($data['video_url'], PHP_URL_PATH);
        $oldPath = parse_url($adVastTag->video_url, PHP_URL_PATH);

        if ($newPath !== $oldPath) {
          // If changing to a different URL and old one was local, delete old local file
          if ($isOldLocal) {
            $oldFilePath = str_replace('/storage/', '', $oldPath);
            Storage::disk('public')->delete($oldFilePath);
          }
        }
      }

      // Fill data without saving
      $adVastTag->fill($data);

      // Regenerate XML with new data
      $xmlContent = $this->generateVastXml($adVastTag);
      $fileName = 'vast_' . $adVastTag->id . '.xml';
      Storage::disk('public')->put('vast/' . $fileName, $xmlContent);

      // Update file_url
      $adVastTag->file_url = Storage::url('vast/' . $fileName);

      // Save all changes
      $adVastTag->save();

      return redirect()->route('admin.custom-ads.index')->with('success', 'Ad updated successfully');
    } catch (\Throwable $th) {
      \Illuminate\Support\Facades\Log::error('Ad Update Error: ' . $th->getMessage());
      return redirect()->back()->with('error', $th->getMessage());
    }
  }

  public function destroy($id)
  {
    try {
      $adVastTag = CustomAdVastTag::findOrFail($id);
      $adVastTag->delete();

      return redirect()->route('admin.custom-ads.index')->with('success', 'Ad deleted successfully');
    } catch (\Throwable $th) {
      return redirect()->back()->with('error', $th->getMessage());
    }
  }

  public function bulkDestroy(Request $request)
  {
    try {
      $validated = $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:custom_ad_vast_tags,id'
      ]);

      $ids = $validated['ids'];

      if (empty($ids)) {
        return redirect()->back()->with('error', 'No ads selected');
      }

      // Must use get() and loop delete() to trigger model events
      $ads = CustomAdVastTag::whereIn('id', $ids)->get();

      foreach ($ads as $ad) {
        $ad->delete();
      }

      return redirect()->route('admin.custom-ads.index')->with('success', 'Ads deleted successfully');
    } catch (\Throwable $th) {
      return redirect()->back()->with('error', $th->getMessage());
    }
  }

  public function search(Request $request)
  {
    try {
      $validated = $request->validate([
        'name' => 'required|string|max:255',
      ]);

      $name = $validated['name'];

      $adVastTag = CustomAdVastTag::where('name', 'like', '%' . $name . '%')
        ->limit(20)
        ->get();

      if (!$adVastTag) {
        return redirect()->back()->with('error', 'Ad not found');
      }

      return response()->json([
        'success' => true,
        'message' => 'Ad found',
        'data' => $adVastTag,
      ]);
    } catch (\Throwable $th) {
      return redirect()->back()->with([
        'success' => false,
        'message' => $th->getMessage(),
        'data' => null
      ]);
    }
  }

  private function generateVastXml(CustomAdVastTag $ad)
  {
    $trackingBaseUrl = route('ads.track');
    $impressionUrl = "{$trackingBaseUrl}?type=imp&ad={$ad->id}";
    $clickTrackingUrl = "{$trackingBaseUrl}?type=click&ad={$ad->id}";
    $startTrackingUrl = "{$trackingBaseUrl}?type=start&ad={$ad->id}";
    $completeTrackingUrl = "{$trackingBaseUrl}?type=complete&ad={$ad->id}";

    $videoUrl = $ad->video_url;
    if (!str_starts_with($videoUrl, 'http')) {
      $videoUrl = url($videoUrl);
    }

    return <<<XML
      <VAST version="4.0">
        <Ad id="{$ad->id}">
          <InLine>
            <AdTitle><![CDATA[{$ad->name}]]></AdTitle>
            <Impression><![CDATA[{$impressionUrl}]]></Impression>
            <Creatives>
              <Creative>
                <Linear>
                  <Duration>{$ad->duration}</Duration>
                  <TrackingEvents>
                    <Tracking event="start"><![CDATA[{$startTrackingUrl}]]></Tracking>
                    <Tracking event="complete"><![CDATA[{$completeTrackingUrl}]]></Tracking>
                  </TrackingEvents>
                  <MediaFiles>
                    <MediaFile delivery="progressive" type="video/mp4" width="640" height="360">
                      <![CDATA[{$videoUrl}]]>
                    </MediaFile>
                  </MediaFiles>
                  <VideoClicks>
                    <ClickThrough><![CDATA[{$ad->direct_link_ad}]]></ClickThrough>
                    <ClickTracking><![CDATA[{$clickTrackingUrl}]]></ClickTracking>
                  </VideoClicks>
                </Linear>
              </Creative>
            </Creatives>
          </InLine>
        </Ad>
      </VAST>
      XML;
  }
}