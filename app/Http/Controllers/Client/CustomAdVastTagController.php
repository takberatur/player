<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomAdVastTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomAdVastTagController extends Controller
{

  /**
   * Generate VAST XML string.
   */
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

  /**
   * Serve a random VAST tag dynamically (like the user's vast.php example).
   */
  public function serveRandom()
  {
    $ads = CustomAdVastTag::all();

    if ($ads->isEmpty()) {
      return response('<VAST version="4.0"/>', 200, ['Content-Type' => 'application/xml']);
    }

    $ad = $ads->random();

    $xmlContent = $this->generateVastXml($ad);

    return response($xmlContent, 200, [
      'Content-Type' => 'application/xml',
      'Access-Control-Allow-Origin' => '*', // CORS for player
    ]);
  }

  /**
   * Serve a specific VAST tag by ID.
   */
  public function show($id)
  {
    $ad = CustomAdVastTag::findOrFail($id);
    $xmlContent = $this->generateVastXml($ad);

    return response($xmlContent, 200, [
      'Content-Type' => 'application/xml',
      'Access-Control-Allow-Origin' => '*',
    ]);
  }
}
