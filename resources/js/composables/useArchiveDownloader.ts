import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";

export type ArchiveResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useArchiveDownloader = () => {

  const getVideoSources = async (link: string): Promise<ArchiveResult> => {
    try {
      if (!link.includes("archive.org")) {
        return {
          success: false,
          error: "Invalid Archive.org URL",
        };
      }

      // Check if VideoController.archive exists (after we add it)
      // If not, fallback to hardcoded route for safety
      const routeUrl = VideoController.archive ? VideoController.archive().url : '/api/archive-video';

      const response = await axios.post(routeUrl, { link });
      const data = response.data;

      if (data.error) {
        return {
          success: false,
          error: data.error
        };
      }

      if (!data.success && !data.sources) { // Some scrapers return {success:false, error:..} or just sources array or {sources: []}
        // My php controller returns {sources: [...]}
      }

      const sourcesList = data.sources || data; // Handle both wrapper or direct array if changed

      if (!Array.isArray(sourcesList) || sourcesList.length === 0) {
        return {
          success: false,
          error: "No sources found",
        };
      }

      // Ensure data conforms to VideoSource
      const sources: VideoSource[] = sourcesList.map((item: any) => ({
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
      console.error("Archive API Error:", e);
      return {
        success: false,
        error: e.response?.data?.error || e.message || "Failed to fetch Archive sources"
      };
    }
  }

  return {
    getVideoSources
  }
}
