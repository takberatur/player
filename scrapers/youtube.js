import { exit } from 'process';
import puppeteer from 'puppeteer';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

(async () => {
  let browser;
  try {
    // Launch Puppeteer
    browser = await puppeteer.launch({
      headless: true, // or "new"
      executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--disable-gpu',
        '--disable-features=IsolateOrigins,site-per-process',
        '--ignore-certificate-errors',
        '--disable-extensions',
        '--window-size=1920,1080',
        `--user-data-dir=/tmp/puppeteer_user_data_yt_${Date.now()}`,
      ],
      // dumpio: true,
    });

    const page = await browser.newPage();

    // Set a consistent User-Agent
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    );

    // Try to intercept video playback requests or use ytdl-core logic inside puppeteer if needed?
    // Actually, capturing requests is hard for YouTube because it uses complex chunked loading (DASH/Blob).
    // Better approach: Use a library that runs INSIDE the browser context or extract ytInitialPlayerResponse.

    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });

    // Click "Reject all" or "Accept all" cookies if the modal appears (EU)
    // This is often needed to get the player to load
    try {
      const consentButton = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button'));
        return buttons.find(
          (b) =>
            b.innerText.includes('Reject all') ||
            b.innerText.includes('Accept all'),
        );
      });
      if (consentButton) {
        await page.click(consentButton);
        await new Promise((r) => setTimeout(r, 2000));
      }
    } catch (e) {
      // Ignore consent errors
    }

    // Extract ytInitialPlayerResponse
    const playerResponse = await page.evaluate(() => {
      if (window.ytInitialPlayerResponse) {
        return window.ytInitialPlayerResponse;
      }
      return null;
    });

    if (!playerResponse) {
      // Maybe try to get it from the page text if not in window
      // But usually it's there.
      console.error(
        JSON.stringify({ error: 'Could not find player response' }),
      );
      await browser.close();
      exit(1);
    }

    const streamingData = playerResponse.streamingData;
    if (!streamingData) {
      console.error(
        JSON.stringify({
          error: 'No streaming data found (maybe login required?)',
        }),
      );
      await browser.close();
      exit(1);
    }

    const formats = [
      ...(streamingData.formats || []),
      ...(streamingData.adaptiveFormats || []),
    ];

    const sources = [];
    const seenQualities = new Set();

    // Helper to get quality rank
    const getQualityRank = (label) => {
      if (!label) return 0;
      const match = label.match(/(\d+)p/);
      return match ? parseInt(match[1]) : 0;
    };

    // Filter and map formats
    for (const f of formats) {
      // We prefer mp4 and significant qualities
      const isMp4 = (f.mimeType || '').includes('video/mp4');
      const hasVideo = !!f.width; // or f.qualityLabel
      const hasAudio = !!f.audioQuality || (f.mimeType || '').includes('audio'); // simplistic check

      // Note: adaptiveFormats usually have separate audio/video.
      // We only want combined formats OR we might need a proxy to combine them (which we don't have easily).
      // Standard `formats` usually have both.

      // IF we only support combined streams (progressive download):
      if (isMp4 && f.url) {
        // It's a direct link
        const label = f.qualityLabel || (f.height ? `${f.height}p` : 'SD');
        const rank = getQualityRank(label);

        // Avoid duplicates or very low quality if we have better?
        // Actually, let's just list them all and let frontend pick.

        sources.push({
          file: f.url,
          type: 'video/mp4',
          label: label,
          default: rank === 720,
        });
      }
    }

    // Also check for HLS
    if (streamingData.hlsManifestUrl) {
      sources.push({
        file: streamingData.hlsManifestUrl,
        type: 'application/x-mpegURL',
        label: 'HLS',
        default: true, // HLS is usually best
      });
    }

    // Sort by quality descending
    sources.sort((a, b) => getQualityRank(b.label) - getQualityRank(a.label));

    console.log(JSON.stringify(sources));
    await browser.close();
    exit(0);
  } catch (error) {
    console.error(JSON.stringify({ error: error.message }));
    if (browser) await browser.close();
    exit(1);
  }
})();
