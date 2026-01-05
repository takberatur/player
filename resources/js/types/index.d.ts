import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
  icon?: LucideIcon;
  isActive?: boolean;
  child?: NavItem[];
}

export type AppPageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  sidebarOpen: boolean;
};

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  role: 'user' | 'editor' | 'admin';
  two_factor_secret?: string | null;
  two_factor_recovery_codes?: string | null;
  two_factor_confirmed_at?: string | null;
  remember_token?: string | null;
  created_at: string;
  updated_at: string;
}

export interface Setting {
  site_name?: string | null;
  site_description?: string | null;
  site_tagline?: string | null;
  site_keywords?: string | null;
  site_og_image?: string | null;
  site_og_description?: string | null;
  site_og_title?: string | null;
  site_twitter_image?: string | null;
  site_twitter_description?: string | null;
  site_twitter_title?: string | null;
  site_email?: string | null;
  site_logo?: string | null;
  site_favicon?: string | null;
  site_phone?: string | null;
  enable_registration: boolean;
  enable_documentation: boolean
}

export interface Video {
  id: string;
  title: string;
  original_link: string;
  encode_link?: string | null;
  poster: string
  type: VideoType
  ad_vast?: string | null;
  enable_popunder_ad: boolean;
  popunder_ad_code?: string | null;
  additional_ad_vast?: string | null;
  enable_button_download: boolean;
  download_link?: string | null;
  sources_json?: Record<string, any> | null;
  views: number;
  created_at: string;
  updated_at: string;
  subtitles?: Subtitle[] | null;
  viewHistory?: VideoViewHistory[] | null;
}

export interface Subtitle {
  id: string;
  video_id: string;
  name: string;
  language: string;
  url: string;
  type: SubtitleType
  created_at: string;
  updated_at: string;
}

export interface VideoViewHistory {
  id: string;
  video_id: string;
  view_date: Date | string;
  view_count: number;
  video?: Video | null;
  created_at: string;
  updated_at: string;
  video?: Video | null;
}

export interface CustomAdVastTag {
  id: string;
  name: string;
  video_url: string;
  direct_link_ad: string;
  duration: string;
  file_url: string;
  additional_direct_link_ad?: string | null;
  tracking_url?: string | null;
  created_at: string;
  updated_at: string;
  analytics?: AdVastTagAnalytic[];
}

export interface AdVastTagAnalytic {
  id: string;
  custom_ad_vast_tag_id: string;
  date: Date | string
  impressions: number;
  clicks: number;
  starts: number;
  completes: number;
  created_at: string;
  updated_at: string;
  adVastTag?: CustomAdVastTag;
}

export type VideoType = 'google_drive' | 'yandex_disk' | 'amazon_drive' | 'archive' | 'facebook' | 'google_photo' | 'rumble' | 'dropbox' | 'youtube' | 'mega_disk' | 'm3u8' | 'mp4' | 'mkv' | 'ts' | 'mpd'

export type SubtitleType = 'vtt' | 'srt'

export interface PaginatedData<T> {
  data: T[];
  current_page?: number | null;
  first_page_url?: string | null;
  from?: number | null;
  last_page?: number | null;
  links: Array<{
    url: string | null;
    page: number | null;
    label: string;
    active: boolean;
  }>;
  next_page_url?: string | null;
  path?: string | null;
  per_page?: number | null;
  prev_page_url?: string | null;
  to?: number | null;
  total?: number | null;
}

export interface VideoProps {
  videos: PaginatedData<Video>;
  filters: {
    search?: string;
    type?: string;
    start_date: string;
    end_date: string;
    per_page: number;
    page: number;
    sort_field?: string;
    sort_direction?: string;
  };
}

export interface SubtitleProps {
  subtitles: PaginatedData<Subtitle>;
  filters: {
    search?: string;
    type?: string;
    start_date: string;
    end_date: string;
    per_page: number;
    page: number;
    sort_field?: string;
    sort_direction?: string;
  };
}


export interface Dashboard {
  stats: {
    total_video: number;
    total_views: number;
    total_users: number;
    total_subtitles: number;
  };
  topVideos: PaginatedData<Video>;
  filters: {
    start_date: string;
    end_date: string;
    per_page: number;
  };
}

export interface Analytics {
  charts: ChartData[];
  top_videos: VideoViewHistory[];
  summary: {
    total_views: number;
    total_videos: number;
    unique_videos_viewed: number;
  };
  filters: {
    start_date: string;
    end_date: string;
    days: number;
  };
}

export interface CustomAdVastTagProps {
  adVastTags: PaginatedData<CustomAdVastTag>;
  filters: {
    search?: string;
    start_date: string;
    end_date: string;
    per_page: number;
    page: number;
  };
}

export interface AdVastTagAnalyticsProps {
  charts: AdVastChartData[];
  top_ad_vast: CustomAdVastTag[];
  summary: {
    total_impressions: number;
    total_clicks: number;
    total_starts: number;
    total_completes: number;
  };
  filters: {
    start_date: string;
    end_date: string;
    days: number;
  };
}

export type BreadcrumbItemType = BreadcrumbItem;

interface CountryItem {
  name: string;
  code: string;
  emoji: string;
  unicode: string;
  image: string;
  dial_code: string;
  minLength: number;
  maxLength: number;
  regexPattern: string;
}

interface ChartData {
  date: Date;
  total_views: number;
  unique_videos_viewed: number;
  new_videos: number;
}

interface AdVastChartData {
  date: Date;
  total_impressions: number;
  total_clicks: number;
  total_starts: number;
  total_completes: number;
}
