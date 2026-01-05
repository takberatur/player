
import { File } from 'megajs';
import { exit } from 'process';

// Usage: node mega_stream.js <url> <start> <end>
const url = process.argv[2];
const start = parseInt(process.argv[3] || '0', 10);
const end = process.argv[4] ? parseInt(process.argv[4], 10) : undefined;

if (!url) {
    console.error('No URL provided');
    exit(1);
}

(async () => {
    try {
        const file = File.fromURL(url);
        await file.loadAttributes();

        // If end is not specified, or it's larger than size, cap it
        const fileSize = file.size;
        const actualEnd = end !== undefined ? Math.min(end, fileSize - 1) : fileSize - 1;

        if (start >= fileSize) {
            // Range not satisfiable
            console.error('Range not satisfiable');
            exit(416); 
        }

        const stream = file.download({
            start: start,
            end: actualEnd
        });

        stream.pipe(process.stdout);

        stream.on('error', (err) => {
            console.error(err);
            exit(1);
        });

    } catch (e) {
        console.error(e);
        exit(1);
    }
})();
