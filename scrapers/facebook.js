import { exit } from 'process';
import puppeteer from 'puppeteer';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

// Function to extract video ID from various Facebook URL formats
const getVideoId = (link) => {
  const match = link.match(/\/videos\/(\d+)/) || link.match(/video_id=(\d+)/) || link.match(/\/watch\/\?v=(\d+)/);
  return match ? match[1] : null;
};

(async () => {
  const videoId = getVideoId(url);
  if (!videoId) {
    console.error(JSON.stringify({ error: 'Could not extract Video ID' }));
    exit(1);
  }

  const embedUrl = `https://www.facebook.com/video/embed?video_id=${videoId}`;

  try {
    const browser = await puppeteer.launch({
      headless: "new",
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--disable-gpu',
      ],
    });

    const page = await browser.newPage();
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    );

    // Go to the embed page
    await page.goto(embedUrl, { waitUntil: 'networkidle2' });

    // Get page content
    const content = await page.content();

    // Extract sources using Regex (similar to previous frontend logic)
    const hdMatch = content.match(/"hd_src":"(.*?)"/);
    const sdMatch = content.match(/"sd_src":"(.*?)"/);

    const sources = [];

    if (hdMatch && hdMatch[1]) {
      sources.push({
        file: hdMatch[1].replace(/\\/g, ''),
        type: 'video/mp4',
        label: 'HD',
        default: true,
      });
    }

    if (sdMatch && sdMatch[1]) {
      sources.push({
        file: sdMatch[1].replace(/\\/g, ''),
        type: 'video/mp4',
        label: 'SD',
        default: false,
      });
    }

    await browser.close();

    if (sources.length === 0) {
        // Fallback: Try to find video tag src if regex fails
        // Sometimes the embed page structure is different
         console.error(JSON.stringify({ error: 'No sources found via regex' }));
         exit(1);
    }

    console.log(JSON.stringify({ success: true, sources }));
    exit(0);

  } catch (e) {
    console.error(JSON.stringify({ error: e.message }));
    exit(1);
  }
})();
