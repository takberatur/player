import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";
import { useRequestVideo } from "./useRequestVideo";
import { ref } from "vue";

export type RumbleResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useRumbleDownloader = () => {
  const { request } = useRequestVideo()

  const QUALITY_MAP = ref({
    360: "360p",
    480: "480p",
    720: "720p",
    1080: "1080p",
    1440: "1440p",
    2160: "2160p",
  })

  const getVideoSources = async (link: string): Promise<RumbleResult> => {
    try {
      if (!link.includes("rumble.com")) {
        return {
          success: false,
          error: "Invalid Rumble URL",
        };
      }

      const route = VideoController.rumble();

      const response = await axios.post(route.url, { link });
      const data = response.data;

      if (data.error) {
        return {
          success: false,
          error: data.error
        };
      }

      if (!Array.isArray(data) || data.length === 0) {
        return {
          success: false,
          error: "No sources found",
        };
      }

      // Ensure data conforms to VideoSource
      const sources: VideoSource[] = data.map((item: any) => ({
        file: item.file,
        type: item.type || 'video/mp4',
        label: item.label || 'HD',
        default: !!item.default
      }));

      return {
        success: true,
        sources: sources,
        jsonString: JSON.stringify(sources)
      };

    } catch (e: any) {
      const msg = e.response?.data?.error || e.message || "Unknown error";
      return {
        success: false,
        error: msg,
      };
    }
  }

  return {
    getVideoSources,
  };
}
