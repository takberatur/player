import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";

export type GoogleDriveResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useGoogleDriveDownloader = () => {

  const getVideoSources = async (link: string): Promise<GoogleDriveResult> => {
    try {
      if (!link.includes("drive.google.com") && !link.includes("docs.google.com")) {
        return {
          success: false,
          error: "Invalid Google Drive URL",
        };
      }

      // Check if VideoController.googleDrive exists
      if (!VideoController.googleDrive) {
        return {
          success: false,
          error: "Google Drive API endpoint not configured in frontend",
        };
      }

      const route = VideoController.googleDrive();

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
        label: item.label || 'HD',
        default: !!item.default
      }));

      return {
        success: true,
        sources: sources,
        jsonString: JSON.stringify(sources)
      };

    } catch (e: any) {
      console.error("Google Drive API Error:", e);
      return {
        success: false,
        error: e.response?.data?.error || e.message || "Failed to fetch Google Drive sources"
      };
    }
  }

  return {
    getVideoSources
  }
}
