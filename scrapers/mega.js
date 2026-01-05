
import { File } from 'megajs';
import { exit } from 'process';

const url = process.argv[2];

if (!url) {
    console.error(JSON.stringify({ error: 'No URL provided' }));
    exit(1);
}

(async () => {
    try {
        const file = File.fromURL(url);
        await file.loadAttributes();
        
        const sources = [{
            file: url, // We will wrap this in the controller
            type: 'video/mp4', // Assuming MP4 for now, or detect from name
            label: 'Original',
            default: true
        }];

        console.log(JSON.stringify({
            success: true,
            sources: sources,
            title: file.name,
            size: file.size
        }));

    } catch (e) {
        console.error(JSON.stringify({ error: e.message || 'Failed to fetch Mega info' }));
        exit(1);
    }
})();
