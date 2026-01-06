import ytdl from '@distube/ytdl-core';
import fs from 'fs';
import { exit } from 'process';
import puppeteer from 'puppeteer';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

(async () => {
  let browser;
  try {
    const extractVideoId = (inputUrl) => {
      const m =
        inputUrl.match(/[?&]v=([^&]+)/) || inputUrl.match(/youtu\.be\/([^?]+)/);
      return m ? m[1] : null;
    };

    const getWithYtdl = async (inputUrl) => {
      let cookiesEnv = null;
      try {
        if (process.env.YTDL_COOKIES_JSON) {
          cookiesEnv = JSON.parse(process.env.YTDL_COOKIES_JSON);
        }
        if (!cookiesEnv && process.env.YTDL_COOKIES_PATH) {
          const raw = fs.readFileSync(process.env.YTDL_COOKIES_PATH, 'utf8');
          cookiesEnv = JSON.parse(raw);
        }
      } catch (_) {}
      const agent = ytdl.createAgent(
        cookiesEnv && Array.isArray(cookiesEnv)
          ? cookiesEnv
          : [
              {
                domain: '.youtube.com',
                expirationDate: Math.floor(Date.now() / 1000) + 86400 * 180,
                hostOnly: false,
                httpOnly: false,
                name: 'CONSENT',
                path: '/',
                sameSite: 'no_restriction',
                secure: true,
                session: false,
                value: 'YES+',
              },
            ],
      );
      const info = await ytdl.getInfo(inputUrl, {
        agent,
        playerClients: ['WEB_EMBEDDED', 'IOS', 'ANDROID', 'TV'],
        requestOptions: {
          headers: {
            'User-Agent':
              'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept-Language': 'en-US,en;q=0.9',
            Referer: 'https://www.youtube.com/',
            Origin: 'https://www.youtube.com',
          },
        },
      });
      const sources = [];
      const prog =
        (info?.formats || []).filter(
          (f) =>
            (f.hasVideo || f.qualityLabel || f.height) &&
            (f.hasAudio ||
              (f.audioCodec || '').length > 0 ||
              (f.mimeType || '').includes('audio')) &&
            ((f.mimeType || '').includes('mp4') || f.container === 'mp4') &&
            !!f.url,
        ) || [];
      for (const f of prog) {
        const label = f.qualityLabel || (f.height ? `${f.height}p` : 'SD');
        sources.push({
          file: f.url,
          type: 'video/mp4',
          label,
          default: label === '720p',
        });
      }
      const hlsUrl =
        info?.player_response?.streamingData?.hlsManifestUrl ||
        info?.playerResponse?.streamingData?.hlsManifestUrl;
      if (hlsUrl) {
        sources.push({
          file: hlsUrl,
          type: 'application/x-mpegURL',
          label: 'HLS',
          default: sources.length === 0,
        });
      }
      return sources;
    };

    // Launch Puppeteer
    browser = await puppeteer.launch({
      headless: 'new',
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
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'en-US,en;q=0.9' });
    await page.evaluateOnNewDocument(() => {
      Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
    });
    await page.setCookie({
      name: 'CONSENT',
      value: 'YES+',
      domain: '.youtube.com',
    });
    const foundSources = [];
    page.on('response', async (response) => {
      try {
        const responseUrl = response.url();
        const headers = response.headers();
        const ct = headers['content-type'] || '';
        const isExtMedia = /\.(mp4|m3u8)(\?|$)/i.test(responseUrl);
        const isHlsCT =
          ct.includes('application/vnd.apple.mpegurl') ||
          ct.includes('application/x-mpegURL');
        const isVideoCT = ct.startsWith('video/');
        const isMedia = isExtMedia || isHlsCT || isVideoCT;
        const isInvalid =
          responseUrl.includes('generate_204') ||
          responseUrl.includes('ptracking') ||
          responseUrl.includes('ad') ||
          ct === '' ||
          ct.startsWith('text/') ||
          ct.startsWith('image/');
        if (isMedia && !isInvalid) {
          const isHls = isHlsCT || responseUrl.includes('.m3u8');
          const type = isHls ? 'application/x-mpegURL' : 'video/mp4';
          if (!foundSources.find((s) => s.file === responseUrl)) {
            foundSources.push({
              file: responseUrl,
              type,
              label: isHls ? 'HLS' : 'Auto',
            });
          }
        }
      } catch (_) {}
    });

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

    let streamingData = playerResponse.streamingData;
    if (!streamingData) {
      streamingData = await page.evaluate(() => {
        const pr =
          window.ytInitialPlayerResponse ||
          (window.ytplayer &&
            window.ytplayer.config &&
            window.ytplayer.config.args &&
            (typeof window.ytplayer.config.args.player_response === 'string'
              ? JSON.parse(window.ytplayer.config.args.player_response)
              : window.ytplayer.config.args.player_response));
        return pr && pr.streamingData ? pr.streamingData : null;
      });
    }
    if (!streamingData) {
      const m = url.match(/[?&]v=([^&]+)/) || url.match(/youtu\.be\/([^?]+)/);
      const vid = m ? m[1] : null;
      if (vid) {
        const embedUrl = `https://www.youtube.com/embed/${vid}?hl=en&has_verified=1&bpctr=9999999999`;
        await page.goto(embedUrl, {
          waitUntil: 'domcontentloaded',
          timeout: 60000,
        });
        streamingData = await page.evaluate(() => {
          const pr =
            window.ytInitialPlayerResponse ||
            (window.ytplayer &&
              window.ytplayer.config &&
              window.ytplayer.config.args &&
              (typeof window.ytplayer.config.args.player_response === 'string'
                ? JSON.parse(window.ytplayer.config.args.player_response)
                : window.ytplayer.config.args.player_response));
          return pr && pr.streamingData ? pr.streamingData : null;
        });
        if (!streamingData) {
          try {
            const params = new URLSearchParams({
              video_id: vid,
              el: 'detailpage',
              c: 'WEB',
              cver: '2.20201021.03.00',
              hl: 'en',
            });
            const text = await page.evaluate(async (q) => {
              const resp = await fetch(
                `https://www.youtube.com/get_video_info?${q}`,
                { credentials: 'include' },
              );
              return await resp.text();
            }, params.toString());
            const prMatch = text.match(/player_response=([^&]+)/);
            if (prMatch) {
              const decoded = decodeURIComponent(prMatch[1]);
              const prJson = JSON.parse(decoded);
              streamingData = prJson.streamingData || null;
            }
          } catch (_) {}
        }
      }
    }
    if (!streamingData) {
      const mweb =
        url.replace('www.youtube.com', 'm.youtube.com') +
        (url.includes('?') ? '&' : '?') +
        'bpctr=9999999999';
      await page.goto(mweb, { waitUntil: 'domcontentloaded', timeout: 60000 });
      streamingData = await page.evaluate(() => {
        const pr =
          window.ytInitialPlayerResponse ||
          (window.ytplayer &&
            window.ytplayer.config &&
            window.ytplayer.config.args &&
            (typeof window.ytplayer.config.args.player_response === 'string'
              ? JSON.parse(window.ytplayer.config.args.player_response)
              : window.ytplayer.config.args.player_response));
        return pr && pr.streamingData ? pr.streamingData : null;
      });
      if (!streamingData) {
        try {
          const vidMatch =
            url.match(/[?&]v=([^&]+)/) || url.match(/youtu\.be\/([^?]+)/);
          const vid2 = vidMatch ? vidMatch[1] : null;
          if (vid2) {
            const params = new URLSearchParams({
              video_id: vid2,
              el: 'detailpage',
              c: 'WEB',
              cver: '2.20201021.03.00',
              hl: 'en',
            });
            const text = await page.evaluate(async (q) => {
              const resp = await fetch(
                `https://www.youtube.com/get_video_info?${q}`,
                { credentials: 'include' },
              );
              return await resp.text();
            }, params.toString());
            const prMatch = text.match(/player_response=([^&]+)/);
            if (prMatch) {
              const decoded = decodeURIComponent(prMatch[1]);
              const prJson = JSON.parse(decoded);
              streamingData = prJson.streamingData || null;
            }
          }
        } catch (_) {}
      }
    }
    if (!streamingData) {
      try {
        const playBtn =
          (await page.$('button.ytp-large-play-button')) ||
          (await page.$('button.ytp-play-button'));
        if (playBtn) {
          await playBtn.click();
        } else {
          await page.keyboard.press('Space');
        }
        await new Promise((r) => setTimeout(r, 8000));
      } catch (_) {}
      if (foundSources.length > 0) {
        console.log(JSON.stringify(foundSources));
        await browser.close();
        exit(0);
      } else {
        try {
          const ytdlSources = await getWithYtdl(url);
          if (Array.isArray(ytdlSources) && ytdlSources.length > 0) {
            console.log(JSON.stringify(ytdlSources));
            await browser.close();
            exit(0);
          }
        } catch (_) {}
        console.error(
          JSON.stringify({
            error: 'No streaming data found (maybe login required?)',
          }),
        );
        await browser.close();
        exit(1);
      }
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
        default: false,
      });
    }

    if (sources.length === 0) {
      try {
        const ytdlAlt = await getWithYtdl(url);
        if (Array.isArray(ytdlAlt) && ytdlAlt.length > 0) {
          console.log(JSON.stringify(ytdlAlt));
          await browser.close();
          exit(0);
        }
      } catch (_) {}
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
