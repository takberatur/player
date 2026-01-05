import { exit } from 'process';
import puppeteer from 'puppeteer';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

const getEmbedId = async (videoUrl) => {
  try {
    const oembedUrl = `https://rumble.com/api/Media/oembed.json?url=${encodeURIComponent(videoUrl)}`;
    const r = await fetch(oembedUrl);
    if (!r.ok) return null;
    const data = await r.json();
    if (!data.html) return null;
    const match = data.html.match(
      /src="https:\/\/rumble\.com\/embed\/([a-zA-Z0-9_]+)\/?/,
    );
    return match ? match[1] : null;
  } catch (e) {
    return null;
  }
};

(async () => {
  let browser;
  try {
    // Step 1: Get Embed ID
    const embedId = await getEmbedId(url);
    if (!embedId) {
      console.error(
        JSON.stringify({ error: 'Could not retrieve Embed ID via OEmbed' }),
      );
      exit(1);
    }

    const embedUrl = `https://rumble.com/embed/${embedId}/`;

    // Step 2: Launch Puppeteer
    try {
      browser = await puppeteer.launch({
        headless: true,
        executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-accelerated-2d-canvas',
          '--disable-gpu',
          '--ignore-certificate-errors',
          '--disable-extensions',
          '--disable-background-timer-throttling',
          '--disable-backgrounding-occluded-windows',
          '--disable-renderer-backgrounding',
          '--window-size=1920,1080',
          `--user-data-dir=/tmp/puppeteer_user_data_${Date.now()}`,
        ],
        dumpio: true,
      });
    } catch (launchError) {
      console.error(
        JSON.stringify({
          error: 'Puppeteer launch failed',
          details: launchError.message,
          path: process.env.PUPPETEER_EXECUTABLE_PATH,
        }),
      );
      exit(1);
    }

    const page = await browser.newPage();

    // Set a real user agent
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    );

    const foundSources = [];

    // Intercept responses to find video files or API data
    page.on('response', async (response) => {
      try {
        const responseUrl = response.url();
        const headers = response.headers();
        const contentType = headers['content-type'] || '';

        // Method A: Capture direct MP4/M3U8 links
        if (responseUrl.match(/\.(mp4|m3u8)(\?|$)/i)) {
          if (!foundSources.find((s) => s.file === responseUrl)) {
            foundSources.push({
              file: responseUrl,
              type: responseUrl.includes('.m3u8')
                ? 'application/x-mpegURL'
                : 'video/mp4',
              label: 'Auto',
              height: 720,
            });
          }
        }

        // Method B: Capture EmbedJS API response
        if (
          responseUrl.includes('/embedJS/') &&
          contentType.includes('application/json')
        ) {
          try {
            const data = await response.json();
            if (data.u) {
              const formats = ['mp4', 'webm'];
              for (const fmt of formats) {
                if (data.u[fmt]) {
                  const d = data.u[fmt];
                  if (d.url) {
                    foundSources.push({
                      file: d.url,
                      type: `video/${fmt}`,
                      label: 'HD',
                      height: 720,
                    });
                  } else {
                    for (const key of Object.keys(d)) {
                      if (d[key] && d[key].url) {
                        foundSources.push({
                          file: d[key].url,
                          type: `video/${fmt}`,
                          label: key,
                          height: parseInt(key) || 0,
                        });
                      }
                    }
                  }
                }
              }
            }
          } catch (e) {
            // Ignore JSON parse errors from response body
          }
        }
      } catch (e) {
        // Ignore response processing errors
      }
    });

    await page.goto(embedUrl, { waitUntil: 'networkidle2', timeout: 60000 });

    // Try to click play if it exists to trigger video load
    try {
      await page.evaluate(() => {
        const playBtn = document.querySelector('.bigPlayUI');
        if (playBtn) playBtn.click();
      });
    } catch (e) {}

    // Wait a bit for requests to capture
    await new Promise((r) => setTimeout(r, 5000));

    await browser.close();

    if (foundSources.length > 0) {
      // Deduplicate and sort
      const uniqueSources = [];
      const seen = new Set();
      for (const s of foundSources) {
        if (!seen.has(s.file)) {
          seen.add(s.file);
          uniqueSources.push(s);
        }
      }

      uniqueSources.sort((a, b) => (b.height || 0) - (a.height || 0));
      if (uniqueSources.length > 0) {
        uniqueSources[0].default = true;
      }

      console.log(JSON.stringify(uniqueSources));
      exit(0);
    } else {
      console.error(
        JSON.stringify({ error: 'No sources found via Puppeteer' }),
      );
      exit(1);
    }
  } catch (e) {
    if (browser) {
      try {
        await browser.close();
      } catch (err) {}
    }
    console.error(
      JSON.stringify({ error: 'Script failed', details: e.message }),
    );
    exit(1);
  }
})();
