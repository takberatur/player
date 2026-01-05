import { useYoutubeDownloader, type VideoSource } from './useYoutubeDownloader';
import { useRumbleDownloader } from './useRumbleDownloader';
import { useFacebookDownloader } from './useFacebookDownloader';
import { useGooglePhotoDownloader } from './useGooglePhotoDownloader';
import { useGoogleDriveDownloader } from './useGoogleDriveDownloader';
import { useArchiveDownloader } from './useArchiveDownloader';
import { useMegaDownloader } from './useMegaDownloader';

type JWSource = { file: string; type: string; label?: string; default?: boolean };
type JWItem = { title?: string; image?: string; sources: JWSource[] };
type JWResult = { success: boolean; jw?: JWItem; jsonString?: string; error?: string };


export const useJwPlayer = () => {

  const { getVideoSources: getYoutubeVideoSources } = useYoutubeDownloader();
  const { getVideoSources: getRumbleVideoSources } = useRumbleDownloader();
  const { getVideoSources: getFacebookVideoSources } = useFacebookDownloader();
  const { getVideoSources: getGooglePhotoVideoSources } = useGooglePhotoDownloader();
  const { getVideoSources: getGoogleDriveVideoSources } = useGoogleDriveDownloader();
  const { getVideoSources: getArchiveVideoSources } = useArchiveDownloader();
  const { getVideoSources: getMegaVideoSources } = useMegaDownloader();


  const toJWSource = (src: VideoSource): JWSource => {
    const isHls = src.file.endsWith('.m3u8');
    return { file: src.file, type: isHls ? 'hls' : 'mp4', label: src.label, default: src.default };
  }

  const amazonHeaders = (): Record<string, string> => {
    const h: Record<string, string> = {};
    const ua = import.meta.env.VITE_AMAZON_USER_AGENT || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';
    h['User-Agent'] = ua;
    const ck = import.meta.env.VITE_AMAZON_COOKIE;
    if (ck) h['Cookie'] = ck;
    return h;
  }

  const resolveGoogleDrive = async (link: string): Promise<string | null> => {
    try {
      const idMatch = link.match(/\/file\/d\/([^/]+)/) || link.match(/id=([^&]+)/) || link.match(/open\?id=([^&]+)/);
      const id = idMatch ? idMatch[1] : '';
      const base = id ? `https://drive.google.com/uc?id=${id}&export=download` : link;
      const ua = import.meta.env.VITE_GOOGLE_DRIVE_USER_AGENT || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';
      const ck = import.meta.env.VITE_GOOGLE_DRIVE_COOKIE || '';
      const res = await fetch(base, { headers: { 'User-Agent': ua, ...(ck ? { 'Cookie': ck } : {}) } });
      const ct = (res.headers.get('content-type') || '').toLowerCase();
      if (ct.includes('text/html')) {
        const html = await res.text();
        const m = html.match(/confirm=([0-9A-Za-z-_]+)/);
        if (m && id) {
          const url = `https://drive.google.com/uc?id=${id}&export=download&confirm=${m[1]}`;
          return url;
        }
        return base;
      }
      return base;
    } catch {
      return null;
    }
  }

  const convert = async (link: string): Promise<JWResult> => {
    if (/rumble\.com/i.test(link)) {
      if (!import.meta.env.VITE_RUMBLE_USE_PUPPETEER) import.meta.env.VITE_RUMBLE_USE_PUPPETEER = '1';
      if (!import.meta.env.VITE_RUMBLE_HEADFUL) import.meta.env.VITE_RUMBLE_HEADFUL = '1';
      const res = await getRumbleVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No sources available' };
      }
      const sources = res.sources.map(toJWSource);
      const jw: JWItem = { title: undefined, image: undefined, sources };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/youtube\.com|youtu\.be/i.test(link)) {
      const res = await getYoutubeVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No sources available' };
      }
      const sources = res.sources.map(toJWSource);
      const jw: JWItem = { title: undefined, image: undefined, sources };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/facebook\.com|fb\.watch/i.test(link)) {
      const res = await getFacebookVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No MP4 sources found' };
      }
      const jw: JWItem = { sources: res.sources.map(toJWSource) };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/dropbox\.com/i.test(link)) {
      const dl = link.replace('?dl=0', '').replace(/\?dl=1$/, '') + '?dl=1';
      const jw: JWItem = { sources: [{ file: dl, type: 'mp4', label: 'HD', default: true }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/drive\.google\.com|docs\.google\.com\/uc\?/i.test(link)) {
      const res = await getGoogleDriveVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No sources found' };
      }
      const jw: JWItem = { sources: res.sources.map(toJWSource) };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/mega\.nz\/file\//i.test(link)) {
      const res = await getMegaVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No Mega sources found' };
      }
      const jw: JWItem = { sources: res.sources.map(toJWSource) };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/amazon\.com\/(drive|clouddrive)\//i.test(link)) {
      try {
        const shareId = (link.match(/share\/(.+)$/)?.[1]) || '';
        const sharesUrl = `https://www.amazon.com/drive/v1/shares/${shareId}?resourceVersion=V2&ContentType=JSON&asset=ALL`;
        const res1 = await fetch(sharesUrl, { headers: amazonHeaders() });
        const json1 = await res1.json();
        const nodeId = String(json1?.nodeInfo?.id || '');
        if (!nodeId) throw new Error('No node id');
        const childrenUrl = `https://www.amazon.com/drive/v1/nodes/${nodeId}/children?resourceVersion=V2&ContentType=JSON&limit=200&sort=%5B%22kind+DESC%22%2C+%22modifiedDate+DESC%22%5D&asset=ALL&tempLink=true&shareId=${shareId}`;
        const res2 = await fetch(childrenUrl, { headers: amazonHeaders() });
        const json2 = await res2.json();
        const tempLink = String(json2?.data?.[0]?.tempLink || '');
        const linkOut = tempLink || 'undefined';
        const jw: JWItem = { sources: [{ file: linkOut, type: 'mp4', label: 'HD', default: true }] };
        return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
      } catch (e) {
        return { success: false, error: e instanceof Error ? e.message : 'Amazon Drive error' };
      }
    }
    if (/archive\.org/i.test(link)) {
      const res = await getArchiveVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No MP4 sources found' };
      }
      const replaced = res.sources.map((s) => ({ ...s, file: s.file.replace('lh3.googleusercontent.com', '3.bp.blogspot.com') }));
      const jw: JWItem = { sources: replaced.map(toJWSource) };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/photos\.app\.goo\.gl|photos\.google\.com/i.test(link)) {
      const res = await getGooglePhotoVideoSources(link);
      if (!res.success || !res.sources || res.sources.length === 0) {
        return { success: false, error: res.error || 'No sources found' };
      }
      const jw: JWItem = { sources: res.sources.map(toJWSource) };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/yandex\.net|yadi\.sk/i.test(link)) {
      const api = `https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key=${encodeURIComponent(link)}`;
      const res = await fetch(api);
      const data = await res.json();
      const href = String(data.href || '');
      if (!href) return { success: false, error: 'No download href' };
      const jw: JWItem = { sources: [{ file: href, type: 'mp4', label: 'HD', default: true }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/\.m3u8($|\?)/i.test(link) || /\b(hls|m3u8)\b/i.test(link)) {
      const jw: JWItem = { sources: [{ file: link, type: 'hls', label: 'HLS', default: true }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/\.mp4($|\?)/i.test(link)) {
      const jw: JWItem = { sources: [{ file: link, type: 'mp4', label: 'HD', default: true }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/\.mkv($|\?)/i.test(link)) {
      const jw: JWItem = { sources: [{ file: link, type: 'mp4', label: 'MKV' }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/\.ts($|\?)/i.test(link) || /\b(ts|tls)\b/i.test(link)) {
      const jw: JWItem = { sources: [{ file: link, type: 'mp4', label: 'TS' }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    if (/\.mpd($|\?)/i.test(link)) {
      const jw: JWItem = { sources: [{ file: link, type: 'dash', label: 'HD', default: true }] };
      return { success: true, jw, jsonString: JSON.stringify(jw, null, 2) };
    }
    return { success: false, error: 'Provider not supported' };
  }

  return {
    toJWSource,
    amazonHeaders,
    resolveGoogleDrive,
    convert
  }
}
