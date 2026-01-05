import axios from 'axios';

export type VideoSource = {
    file: string;
    type: string;
    label: string;
    default?: boolean;
};

export const useYoutubeDownloader = () => {
  const getVideoSources = async (link: string): Promise<{ success: boolean; sources?: VideoSource[]; error?: string; jsonString?: string }> => {
    try {
        const response = await axios.post('/api/youtube-video', { link });
        const sources = response.data;
        
        if (!Array.isArray(sources) || sources.length === 0) {
             return { success: false, error: 'No sources found' };
        }
        
        return { 
            success: true, 
            sources: sources,
            jsonString: JSON.stringify(sources)
        };
    } catch (e: any) {
        const msg = e.response?.data?.error || e.message || 'Unknown error';
        return { 
            success: false, 
            error: msg 
        };
    }
  }

  return {
    getVideoSources
  }
}
