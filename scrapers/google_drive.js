import axios from 'axios';
import { exit } from 'process';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

(async () => {
  try {
    // Extract ID from URL
    const idMatch =
      url.match(/\/file\/d\/([^/]+)/) ||
      url.match(/id=([^&]+)/) ||
      url.match(/open\?id=([^&]+)/);
    const id = idMatch ? idMatch[1] : null;

    if (!id) {
      console.error(
        JSON.stringify({ error: 'Could not extract Google Drive ID' }),
      );
      exit(1);
    }

    // Try a different approach: Use the /uc endpoint with export=download directly
    // but follow redirects MANUALLY to get the final link.

    // Sometimes Google requires a cookie or confirmation token.
    const downloadUrl = `https://drive.google.com/uc?id=${id}&export=download`;

    // First, just try to get the headers to see if it redirects
    try {
      const res = await axios.get(downloadUrl, {
        maxRedirects: 0,
        validateStatus: (status) => status >= 200 && status < 400,
        headers: {
          'User-Agent':
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        },
      });

      if (res.status >= 300 && res.status < 400 && res.headers.location) {
        // It redirected immediately! This is the link we want.
        const sources = [
          {
            file: res.headers.location,
            type: 'video/mp4',
            label: 'HD',
            default: true,
          },
        ];
        console.log(JSON.stringify({ success: true, sources }));
        exit(0);
      } else if (
        res.headers['content-type'] &&
        res.headers['content-type'].includes('text/html')
      ) {
        // It didn't redirect, maybe it's the confirmation page (virus warning)
        const confirmMatch = res.data.match(/confirm=([0-9A-Za-z-_]+)/);
        if (confirmMatch) {
          const confirmUrl = `https://drive.google.com/uc?id=${id}&export=download&confirm=${confirmMatch[1]}`;

          // Now try this confirm URL, it should redirect to the final file
          const res2 = await axios.get(confirmUrl, {
            maxRedirects: 0,
            validateStatus: (status) => status >= 200 && status < 400,
            headers: {
              'User-Agent':
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            },
          });

          if (
            res2.status >= 300 &&
            res2.status < 400 &&
            res2.headers.location
          ) {
            const sources = [
              {
                file: res2.headers.location,
                type: 'video/mp4',
                label: 'HD',
                default: true,
              },
            ];
            console.log(JSON.stringify({ success: true, sources }));
            exit(0);
          }
        }
      }
    } catch (e) {
      // console.error(e);
    }

    // Fallback: If the above failed, try to construct a direct link proxy via our own server?
    // No, that would require streaming.

    // Let's try one more trick: Google Drive sometimes returns a "download warning" page which contains the link in a form action or anchor.
    // But usually the confirm= param handles that.

    console.error(
      JSON.stringify({ error: 'Could not resolve direct download link' }),
    );
    exit(1);
  } catch (e) {
    console.error(JSON.stringify({ error: e.message }));
    exit(1);
  }
})();
