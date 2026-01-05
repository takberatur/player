import { exit } from 'process';
import axios from 'axios';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

(async () => {
  try {
    const res = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
      }
    });

    // Look for the base URL.
    // It usually starts with https://lh3.googleusercontent.com/ and is quite long.
    // We capture until we hit a character that shouldn't be in the URL (like ", ', space, backslash, or ])
    const regex = /https:\/\/lh3\.googleusercontent\.com\/[^"'\s\\]+/g;
    const matches = res.data.match(regex);

    if (!matches || matches.length === 0) {
        console.error(JSON.stringify({ error: 'No video URL found in page source' }));
        exit(1);
    }

    // Filter matches that look like the main content (usually contain /pw/ or are long)
    // and are not thumbnails (thumbnails usually have =w... or =h... at the end, but we strip that anyway)
    
    // We take the first match that contains /pw/ if available, otherwise just the first match.
    // The matches might be the same base URL repeated.
    const unique = [...new Set(matches)];
    
    let baseUrl = unique.find(u => u.includes('/pw/')) || unique[0];
    
    // Clean up the URL: remove any trailing flags (starting with =)
    baseUrl = baseUrl.replace(/=.*$/, '');

    const candidates = [
        { label: '1080p', url: `${baseUrl}=m37` },
        { label: '720p', url: `${baseUrl}=m22` },
        { label: '360p', url: `${baseUrl}=m18` }
    ];

    const sources = candidates.map(c => ({
        file: c.url,
        type: 'video/mp4',
        label: c.label,
        default: c.label === '720p'
    }));

    console.log(JSON.stringify({ success: true, sources }));
    exit(0);

  } catch (e) {
    console.error(JSON.stringify({ error: e.message }));
    exit(1);
  }
})();
