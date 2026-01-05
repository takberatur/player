import ytdl from '@distube/ytdl-core';

const url = process.argv[2];

if (!url) {
  console.error('URL is required');
  process.exit(1);
}

const run = async () => {
  try {
    if (!ytdl.validateURL(url)) {
      throw new Error('Invalid YouTube URL');
    }

    // Force clients that are easier to proxy (WEB and ANDROID)
    // TVHTML5 often produces links that are strictly IP/UA bound or require specific signatures
    // Use 'playerClients' as per @distube/ytdl-core implementation
    const info = await ytdl.getInfo(url, {
      playerClients: ['ANDROID', 'WEB', 'IOS'],
    });
    const formats = info.formats;

    const priorities = [2160, 1440, 1080, 720, 480, 360];
    const sources = [];
    const seenQualities = new Set();

    // Priority loop
    for (const p of priorities) {
      const fmt = formats.find(
        (f) =>
          (f.height || 0) === p &&
          (f.mimeType || '').includes('video/mp4') &&
          f.hasAudio &&
          f.hasVideo &&
          !!f.url,
      );
      if (fmt) {
        sources.push({
          file: fmt.url,
          type: 'video/mp4',
          label: `${p}p`,
          default: p === 720,
        });
        seenQualities.add(p);
      }
    }

    // If no sources found via priority, grab any mp4 with audio and video
    if (sources.length === 0) {
      for (const f of formats) {
        if ((f.mimeType || '').includes('video/mp4') && f.url && f.hasAudio && f.hasVideo) {
          const label = f.qualityLabel || (f.height ? `${f.height}p` : 'mp4');
          sources.push({ file: f.url, type: 'video/mp4', label });
        }
      }
    }

    // Check for HLS
    const hls = info.player_response?.streamingData?.hlsManifestUrl;
    if (hls) {
      sources.push({
        file: hls,
        type: 'application/x-mpegURL',
        label: 'HLS',
      });
    }

    console.log(JSON.stringify(sources));
  } catch (error) {
    // Output error as JSON to be handled gracefully if possible, or just stderr
    // Better to just stderr and exit 1
    console.error(error.message);
    process.exit(1);
  }
};

run();
