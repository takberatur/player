
import { ref } from 'vue';
import axios from 'axios';
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";

export type YandexDiskResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useYandexDiskDownloader = () => {
  const loading = ref(false);
  const error = ref<string | null>(null);

  const getVideoSources = async (url: string): Promise<YandexDiskResult> => {
    loading.value = true;
    error.value = null;

    try {
      if (!url.includes("yadi.sk") && !url.includes("disk.yandex.")) {
        return {
          success: false,
          error: "Invalid Yandex Disk URL",
        };
      }

      if (!VideoController.yandexDisk) {
        return {
          success: false,
          error: "Yandex Disk API endpoint not configured in frontend",
        };
      }

      const route = VideoController.yandexDisk();

      const response = await axios.post(route.url, { link: url });
      const data = response.data;

      if (!data.success) {
        throw new Error(data.error || 'Failed to fetch Yandex Disk video info');
      }

      const sources: VideoSource[] = data.sources.map((item: any) => ({
        file: item.file,
        type: item.type || 'video/mp4',
        label: item.label || 'Original',
        default: !!item.default
      }));

      return {
        success: true,
        sources: sources,
        jsonString: JSON.stringify(sources)
      };

    } catch (err: any) {
      const msg = err.response?.data?.error || err.message || 'Unknown error';
      error.value = msg;
      return { success: false, error: msg };
    } finally {
      loading.value = false;
    }
  };

  return {
    getVideoSources,
    loading,
    error,
  };
};
