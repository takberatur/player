import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";

export type FacebookResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useFacebookDownloader = () => {

  const getVideoSources = async (link: string): Promise<FacebookResult> => {
    try {
      if (!link.includes("facebook.com")) {
        return {
          success: false,
          error: "Invalid Facebook URL",
        };
      }

      // Check if VideoController.facebook exists (it should since we updated it)
      if (!VideoController.facebook) {
        return {
          success: false,
          error: "Facebook API endpoint not configured in frontend",
        };
      }

      const route = VideoController.facebook();

      const response = await axios.post(route.url, { link });
      const data = response.data;

      if (data.error) {
        return {
          success: false,
          error: data.error
        };
      }

      if (!data.success || !data.sources || data.sources.length === 0) {
        return {
          success: false,
          error: "No sources found",
        };
      }

      // Ensure data conforms to VideoSource
      const sources: VideoSource[] = data.sources.map((item: any) => ({
        file: item.file,
        type: item.type || 'video/mp4',
        label: item.label || 'SD',
        default: !!item.default
      }));

      return {
        success: true,
        sources: sources,
        jsonString: JSON.stringify(sources)
      };

    } catch (e: any) {
      console.error("Facebook API Error:", e);
      return {
        success: false,
        error: e.response?.data?.error || e.message || "Failed to fetch Facebook sources"
      };
    }
  }

  return {
    getVideoSources
  }
}
