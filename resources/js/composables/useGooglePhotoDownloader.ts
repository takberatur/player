import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";

export type GooglePhotoResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useGooglePhotoDownloader = () => {

  const getVideoSources = async (link: string): Promise<GooglePhotoResult> => {
    try {
      if (!link.includes("photos.google.com") && !link.includes("photos.app.goo.gl")) {
        return {
          success: false,
          error: "Invalid Google Photos URL",
        };
      }

      // Check if VideoController.googlePhoto exists
      if (!VideoController.googlePhoto) {
        return {
          success: false,
          error: "Google Photo API endpoint not configured in frontend",
        };
      }

      const route = VideoController.googlePhoto();

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
      console.error("Google Photo API Error:", e);
      return {
        success: false,
        error: e.response?.data?.error || e.message || "Failed to fetch Google Photo sources"
      };
    }
  }

  return {
    getVideoSources
  }
}
