import { AppPageProps } from '@/types/index';
import type { PageProps } from '@inertiajs/core';
import type { User, Setting, Video, Subtitle, VideoProps, SubtitleProps, Dashboard, Analytics, VideoType, CustomAdVastTag, CustomAdVastTagProps, AdVastTagAnalytic, AdVastTagAnalyticsProps } from '@/types';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    [key: string]: string | boolean | undefined;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
  }
}

declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps, AppPageProps { }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }
}
declare global {
  interface Window {
    gtag: (...args: any[]) => void;
    axios: AxiosInstance;
    Pusher: typeof Pusher;
    Echo: Echo;
  }
  interface SharedData extends PageProps {
    status: string | null;
    success: string | null;
    error: string | null;
    flash?: {
      reload?: boolean;
      status?: string | null;
      success?: string | null;
      error?: string | null;
    };
    release_date: string | null;
    setting: Setting | null;
    videos?: Video[] | null;
    video?: Video | null;
    subtitles?: Subtitle[] | null;
    subtitle?: Subtitle | null;
    adVastTags?: CustomAdVastTag[] | null;
    adVastTag?: CustomAdVastTag | null;
    adVastTagAnalytic?: AdVastTagAnalytic | null;
    adVastTagAnalytics?: AdVastTagAnalytic[] | null;
    videoPaginateProps?: VideoProps | null;
    subtitlePaginateProps?: SubtitleProps | null;
    adVastTagPaginateProps?: CustomAdVastTagProps | null;
    dashboard?: Dashboard | null;
    analytics?: Analytics | null;
    adVastTagAnalyticProps?: AdVastTagAnalyticsProps | null
    adVastCount?: number | null
  }

  type FormWebSetting = {
    enable_registration: boolean;
    enable_documentation: boolean
    site_name?: string;
    site_description?: string;
    site_tagline?: string;
    site_keywords?: string;
    site_og_image?: string;
    site_og_description?: string;
    site_og_title?: string;
    site_twitter_image?: string;
    site_twitter_description?: string;
    site_twitter_title?: string;
    site_email?: string;
    site_logo?: File | string | null | undefined;
    site_favicon?: File | string | null | undefined;
    site_phone?: string;
  };

  type CreateVideoForm = {
    title: string;
    original_link: string;
    type: VideoType;
    poster?: string;
    ad_vast?: string;
    enable_popunder_ad: boolean;
    popunder_ad_code?: string;
    additional_ad_vast?: string;
    enable_button_download: boolean;
    download_link?: string;
    subtitles?: SubtitleForm[];
  };

  type SubtitleForm = {
    id?: string;
    language: string;
    name: string;
    file: File;
  };

  type CreateAdVastForm = {
    name: string;
    video_url?: string;
    video_file?: File | null;
    direct_link_ad: string;
    duration: string;
    additional_direct_link_ad?: string;
    tracking_url?: string;
  }


  type RequestVideoResponse<T> = {
    success: boolean;
    message?: string;
    data?: T;
    error?: string;
  };

  interface EncodeOptions {
    inputUrl: string;
    outputPath: string;
    videoCodec?: string;
    audioCodec?: string;
    crf?: number;
    preset?: string;
    headers?: Record<string, string>;
    extraArgs?: string[];
  }

  interface VideoSource {
    file: string;
    type: string;
    label: string;
    default?: boolean;
  }
  interface RumbleResult {
    success: boolean;
    sources?: VideoSource[];
    jsonString?: string;
    error?: string;
  }
}

export { }
