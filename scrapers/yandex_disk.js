import axios from 'axios';
import { exit } from 'process';

const url = process.argv[2];

if (!url) {
  console.error(JSON.stringify({ error: 'No URL provided' }));
  exit(1);
}

(async () => {
  try {
    const api = `https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key=${encodeURIComponent(url)}`;

    const res = await axios.get(api);
    const href = res.data.href;

    if (!href) {
      console.error(
        JSON.stringify({
          error: 'No download href found in Yandex API response',
        }),
      );
      exit(1);
    }

    const sources = [
      {
        file: href,
        type: 'video/mp4',
        label: 'HD',
        default: true,
      },
    ];

    console.log(JSON.stringify({ success: true, sources }));
    exit(0);
  } catch (error) {
    const msg =
      error.response?.data?.message || error.message || 'Unknown error';
    console.error(
      JSON.stringify({
        error: msg,
        details: error.response ? error.response.data : null,
      }),
    );
    exit(1);
  }
})();
