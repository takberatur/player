
import { ref } from 'vue';
import axios from 'axios';
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";

export interface VideoSource {
  file: string;
  type: string;
  label: string;
  default?: boolean;
}

export interface ScraperResponse {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
}

export const useMegaDownloader = () => {
  const loading = ref(false);
  const error = ref<string | null>(null);

  const getVideoSources = async (url: string): Promise<ScraperResponse> => {
    loading.value = true;
    error.value = null;

    try {
      if (!VideoController.mega) {
        return {
          success: false,
          error: "Mega API endpoint not configured in frontend",
        };
      }

      const route = VideoController.mega();

      const response = await axios.post(route.url, { link: url });
      const data = response.data;

      if (!data.success) {
        throw new Error(data.error || 'Failed to fetch Mega video info');
      }

      return data;
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
