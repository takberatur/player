import { spawn } from 'child_process';
import { File } from 'megajs';
import { Readable } from 'node:stream';

export const useFfmpeg = () => {
  const resolveFfmpeg = (): string => {
    try {
      // eslint-disable-next-line @typescript-eslint/no-require-imports
      const ff = require('ffmpeg-static');
      if (typeof ff === 'string' && ff) return ff;
    } catch (error) {
      const errorMsg = error instanceof Error ? error.message : 'Unknown error';
      console.error(`Error resolving ffmpeg path: ${errorMsg}`);
    }
    return 'ffmpeg';
  };

  const encodeWithFfmpeg = async (opts: EncodeOptions): Promise<{ success: boolean; error?: string }> => {
    const ffmpegPath = resolveFfmpeg();
    const args: string[] = [
      ...(opts.headers && Object.keys(opts.headers).length
        ? [
          '-headers',
          Object.entries(opts.headers)
            .map(([k, v]) => `${k}: ${v}`)
            .join('\r\n'),
        ]
        : []),
      '-i',
      opts.inputUrl,
      '-y',
    ];
    const vcodec = opts.videoCodec || 'libx264';
    const acodec = opts.audioCodec || 'aac';
    args.push('-c:v', vcodec, '-c:a', acodec);
    if (typeof opts.crf === 'number') args.push('-crf', String(opts.crf));
    if (opts.preset) args.push('-preset', opts.preset);
    if (opts.extraArgs && opts.extraArgs.length) args.push(...opts.extraArgs);
    args.push(opts.outputPath);

    return await new Promise((resolve) => {
      const proc = spawn(ffmpegPath, args, { stdio: 'pipe' });
      proc.stdout.on('data', (d) => console.debug(String(d)));
      proc.stderr.on('data', (d) => console.info(String(d)));
      proc.on('error', (err) => resolve({ success: false, error: err.message }));
      proc.on('close', (code) => {
        if (code === 0) resolve({ success: true });
        else
          resolve({ success: false, error: `ffmpeg exited with code ${code}` });
      });
    });
  };

  const encodeFromStream = async (
    stream: Readable,
    opts: Omit<EncodeOptions, 'inputUrl'>,
  ): Promise<{ success: boolean; error?: string }> => {
    const ffmpegPath = resolveFfmpeg();
    const args: string[] = [
      ...(opts.headers && Object.keys(opts.headers).length
        ? [
          '-headers',
          Object.entries(opts.headers)
            .map(([k, v]) => `${k}: ${v}`)
            .join('\r\n'),
        ]
        : []),
      '-i',
      'pipe:0',
      '-y',
    ];
    const vcodec = opts.videoCodec || 'libx264';
    const acodec = opts.audioCodec || 'aac';
    args.push('-c:v', vcodec, '-c:a', acodec);
    if (typeof opts.crf === 'number') args.push('-crf', String(opts.crf));
    if (opts.preset) args.push('-preset', opts.preset);
    if (opts.extraArgs && opts.extraArgs.length) args.push(...opts.extraArgs);
    args.push(opts.outputPath);

    return await new Promise((resolve) => {
      const proc = spawn(ffmpegPath, args, {
        stdio: ['pipe', 'pipe', 'pipe'],
      });
      stream.pipe(proc.stdin);
      proc.stdout.on('data', (d) => console.debug(String(d)));
      proc.stderr.on('data', (d) => console.info(String(d)));
      proc.on('error', (err) => resolve({ success: false, error: err.message }));
      proc.on('close', (code) => {
        if (code === 0) resolve({ success: true });
        else
          resolve({ success: false, error: `ffmpeg exited with code ${code}` });
      });
    });
  };

  const encodeMegaFile = async (
    megaUrl: string,
    opts: Omit<EncodeOptions, 'inputUrl'>,
  ): Promise<{ success: boolean; error?: string }> => {
    try {
      const file = File.fromURL(megaUrl);
      await file.loadAttributes();
      const stream = file.download({});
      return await encodeFromStream(stream as unknown as Readable, opts);
    } catch (e) {
      const msg = e instanceof Error ? e.message : 'Mega encode error';
      return { success: false, error: msg };
    }
  };

  return {
    resolveFfmpeg,
    encodeWithFfmpeg,
    encodeFromStream,
    encodeMegaFile,
  };
};
