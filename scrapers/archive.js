import axios from 'axios';

const url = process.argv[2];

if (!url) {
  console.error('URL is required');
  process.exit(1);
}

const run = async () => {
  try {
    const { data: html } = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      },
    });

    const m = html.match(/"sources":\[(.*?)\],/);
    const arr = m ? JSON.parse('[' + m[1] + ']') : [];
    const sources = [];

    for (const v of arr) {
      const h = Number(v.height || 0);
      const label = h ? `${h}p` : 'unknown';
      const file = 'https://archive.org' + String(v.file || '');
      
      if (file.endsWith('.mp4')) {
        sources.push({
          file,
          type: 'video/mp4',
          label,
          default: h === 480,
        });
      }
    }

    // If no sources found via regex, try to find direct download links via metadata API
    if (sources.length === 0) {
        // Extract identifier from URL
        // https://archive.org/details/identifier
        const match = url.match(/\/details\/([^/?#]+)/);
        if (match) {
            const id = match[1];
            const metadataUrl = `https://archive.org/metadata/${id}`;
            const { data: metadata } = await axios.get(metadataUrl);
            
            if (metadata && metadata.files) {
                for (const file of metadata.files) {
                    if (file.format === 'MPEG4' || file.name.endsWith('.mp4')) {
                         const fileUrl = `https://${metadata.d1}${metadata.dir}/${file.name}`;
                         sources.push({
                             file: fileUrl,
                             type: 'video/mp4',
                             label: 'HD', // Metadata might not have height, default to HD
                             default: true
                         });
                    }
                }
            }
        }
    }

    if (sources.length === 0) {
        console.error('No MP4 sources found');
        process.exit(1);
    }

    console.log(JSON.stringify(sources));
  } catch (error) {
    console.error(error.message);
    process.exit(1);
  }
};

run();
