<script setup lang="ts">
import { useJwPlayer } from '@/composables/useJwPlayer';
import type { Subtitle, Video } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useHead, useSeoMeta } from '@unhead/vue';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
  video?: Video | null;
}>();

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const jwPlayerHelper = useJwPlayer();
const videoData = computed(() => page.props.video || props.video);
const jwPlayerKey = ref('64HPbvSQorQcd52B8XFuhMtEoitbvY/EXJmMBfKcXZQU2Rnn');
const loaderContainer = ref<HTMLDivElement | null>(null);
const loader = ref<HTMLDivElement | null>(null);
const popunderScriptSrc = ref<string | null>(
  extractScriptSrc(videoData.value?.popunder_ad_code),
);

useSeoMeta({
  title: `Watch ${videoData.value?.title || 'Forge Player'} Free Streaming- ${setting.value?.site_name || 'Forge Player'}`,
  description: setting.value?.site_description || 'Welcome to Forge Player',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'index, follow',
  ogType: 'video.other',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Watch ${videoData.value?.title || 'Forge Player'} Free Streaming- ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription:
    setting.value?.site_og_description || 'Welcome to Forge Player',
  ogImage: videoData.value?.poster || '/images/default-thumbnail.jpg',
  twitterCard: 'summary_large_image',
  twitterTitle: `Watch ${videoData.value?.title || 'Forge Player'} Free Streaming- ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription:
    setting.value?.site_twitter_description || 'Welcome to Forge Player',
  twitterImage: videoData.value?.poster || '/images/default-thumbnail.jpg',
});
useHead({
  link: [
    {
      rel: 'canonical',
      href:
        import.meta.env.VITE_APP_URL ||
        (typeof window !== 'undefined' ? window.location.href : ''),
    },
    {
      rel: 'icon',
      type: 'image/x-icon',
      href: setting.value?.site_favicon ?? '/images/logo.png',
    },
    {
      rel: 'apple-touch-icon',
      href: setting.value?.site_favicon ?? '/apple-touch-icon.png',
    },
    {
      rel: 'shortcut icon',
      type: 'image/x-icon',
      href: setting.value?.site_favicon ?? '/apple-touch-icon.png',
    },
  ],
  meta: [
    { property: 'og:type', content: 'video.other' },
    { property: 'og:url', content: `${page.url}` },
    { name: 'author', content: setting.value?.site_name },
    { name: 'publisher', content: setting.value?.site_name },
  ],
  script: [
    {
      type: 'application/ld+json',
      innerHTML: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'HomePage',
        headline: `Watch ${videoData.value?.title || 'Forge Player'} Free Streaming- ${setting.value?.site_name || 'Forge Player'}`,
        description: setting.value?.site_og_description,
        image: videoData.value?.poster || '/images/default-thumbnail.jpg',
        url: `${page.url}`,
        author: {
          '@type': 'Organization',
          name: setting.value?.site_name,
        },
        publisher: {
          '@type': 'Organization',
          name: setting.value?.site_name,
          logo: {
            '@type': 'ImageObject',
            url: setting.value?.site_logo ?? '/images/logo.png',
          },
        },
        keywords: setting.value?.site_keywords,
      }),
    },
  ],
});

const loadExternalScripts = () => {
  const scripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js',
    'https://ssl.p.jwpcdn.com/player/v/8.8.6/jwplayer.js',
  ];
  if (popunderScriptSrc.value && videoData.value?.enable_popunder_ad)
    scripts.push(popunderScriptSrc.value);

  scripts.forEach((src) => {
    if (!document.querySelector(`script[src="${src}"]`)) {
      const script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = src;
      script.async = false; // Ensure scripts load in order if needed, or remove for async
      document.head.appendChild(script);
    }
  });
};

const handlePreloader = () => {
  setTimeout(() => {
    if (loader.value) {
      setTimeout(() => {
        if (loader.value) {
          loader.value.style.transition = 'opacity 0.6s ease-out';
          loader.value.style.opacity = '0';
          setTimeout(() => {
            if (loader.value) loader.value.style.display = 'none';
          }, 600);
        }
      }, 1000);
    }

    if (loaderContainer.value) {
      setTimeout(() => {
        if (loaderContainer.value) {
          loaderContainer.value.style.transition = 'opacity 0.6s ease-out';
          loaderContainer.value.style.opacity = '0';
          setTimeout(() => {
            if (loaderContainer.value)
              loaderContainer.value.style.display = 'none';
          }, 600);
        }
      }, 1500);
    }
  }, 2000);
};

const setupPlayer = () => {
  if (!videoData.value) return;

  const checkJwPlayer = setInterval(async () => {
    if ((window as any).jwplayer) {
      clearInterval(checkJwPlayer);
      if (!(window as any).jwplayer.key) {
        (window as any).jwplayer.key = jwPlayerKey.value;
      }
      await initializePlayer();
    }
  }, 100);
};

const initializePlayer = async () => {
  try {
    const data = videoData.value;
    if (!data?.original_link) return;

    const originalLink = data.original_link.trim();
    const result = await jwPlayerHelper.convert(originalLink);

    let sources = [];
    if (result.success && result.jw && result.jw.sources) {
      sources = result.jw.sources;
    } else {
      // Fallback if source not found or provider not supported
      // Use a valid placeholder or just show error
      sources = [
        {
          label: 'Error',
          type: 'video/mp4',
          file: '', // Empty file to trigger error immediately rather than 404 on dead domain
        },
      ];
      if ((window as any).swal) {
        (window as any).swal(
          'Source Not Supported',
          'We cannot play this video format automatically. Please try downloading it.',
          'warning',
        );
      }
    }

    // Force type to 'hls' if link ends with m3u8 or if it's explicitly set
    sources = sources.map((s: any) => {
      // Trim file url
      if (s.file) s.file = s.file.trim();

      if (s.file) {
        if (s.file.includes('.m3u8')) {
          return { ...s, type: 'hls' };
        }
        if (s.file.includes('.mpd')) {
          return { ...s, type: 'dash' };
        }
      }
      return s;
    });

    const tracks = (data.subtitles || []).map(
      (sub: Subtitle, index: number) => {
        // let fileUrl = sub.url || sub.file_url || sub.file;
        let fileUrl = sub.url;
        // Trim subtitle url
        if (fileUrl) fileUrl = fileUrl.trim();

        if (fileUrl && !fileUrl.startsWith('http')) {
          fileUrl = `${window.location.origin}${fileUrl.startsWith('/') ? '' : '/'}${fileUrl}`;
        }

        // Ensure file extension is treated correctly by JW Player if it's .srt
        // JW Player sometimes needs explicit type if extension isn't clear or to be safe
        return {
          file: fileUrl,
          label: sub.name || sub.language,
          kind: 'captions',
          type: fileUrl.endsWith('.srt') || sub.type === 'srt' ? 'srt' : 'vtt',
          default: index === 0,
        };
      },
    );

    // console.log('Subtitle Tracks:', tracks);

    const playerInstance = (window as any).jwplayer('forge-player-container');

    const config: any = {
      sources: sources,
      cast: {},
      width: '100%',
      height: '100%',
      stretching: 'uniform',
      controls: true,
      startparam: 'start',
      primary: 'html5',
      autostart: false,
      preload: 'auto',
      title: data.title,
      displaytitle: true,
      floating: true,
      image: data.poster ? data.poster.trim() : '',
      events: {
        onPause: function () {
          // Force play on pause (anti-pause) as per original PHP logic
          // Using 'forge-player-container' ID instead of 'mediaspace'
          (window as any).jwplayer('forge-player-container').play();
        },
        setupError: function () {
          if ((window as any).swal) {
            (window as any).swal(
              'Server Error!',
              'Please contact us to fix it asap. Thank you!',
              'error',
            );
          }
        },
        error: function () {
          if ((window as any).swal) {
            (window as any).swal(
              'Video Error Occurred!',
              'Possible Video Link or Try To Refresh Player Page. Thank You!',
              'error',
            );
          }
        },
      },
      sharing: {
        sites: ['facebook', 'twitter', 'linkedin', 'pinterest'],
      },
      playbackRateControls: true,
      captions: {
        color: '#fff',
        fontSize: 15,
        backgroundOpacity: 0,
        fontfamily: 'Helvetica',
        edgeStyle: 'raised',
      },
      tracks: tracks,
    };

    const fisrtAdVast = data.ad_vast;
    const additionalAdVast = data.additional_ad_vast;

    // Check if one of the ad VAST tags is present
    if (fisrtAdVast || additionalAdVast) {
      const schedule: any = {};

      // Configure main pre-roll + Mid-roll ad break
      if (fisrtAdVast) {
        schedule.adbreak_pre = {
          offset: 'pre',
          tag: fisrtAdVast,
          skipoffset: 10,
        };

        // Generate mid-roll ads every 10 minutes (600 seconds)
        // Covering up to 5 hours (30 intervals) to ensure coverage for long movies
        for (let i = 1; i <= 30; i++) {
          schedule[`adbreak_mid_${i}`] = {
            offset: i * 600,
            tag: fisrtAdVast,
            skipoffset: 10,
          };
        }
      }

      // Configure post-roll ad break if additional VAST tag is present
      if (additionalAdVast) {
        schedule.adbreak_post = {
          offset: 'post',
          tag: additionalAdVast,
          skipoffset: 10,
        };
      }

      config.advertising = {
        client: 'vast',
        schedule: schedule,
      };
    }

    // if (data.ad_vast) {
    //   config.advertising = {
    //     client: 'vast',
    //     schedule: {
    //       adbreak1: {
    //         offset: 'pre',
    //         tag: data.ad_vast,
    //         skipoffset: 10,
    //       },
    //       adbreak2: {
    //         offset: '50%',
    //         tag: data.ad_vast,
    //         skipoffset: 10,
    //       },
    //     },
    //   };
    // }

    playerInstance.setup(config);

    // Add Buttons
    if (data.enable_button_download && data.download_link) {
      playerInstance.addButton(
        '/images/download.svg',
        'Download File',
        function () {
          if (!data.download_link) return;
          window.open(data.download_link, '_blank');
        },
        'download',
      );
    }

    playerInstance.addButton(
      '/images/logo-small.png',
      'Powered By Forge Player',
      function () {
        const win = window.open('https://forge-player.com', '_blank');
        if (win) win.focus();
      },
      'Brand Logo',
    );
  } catch (e) {
    console.error('Player setup failed:', e);
  }
};

onMounted(() => {
  loadExternalScripts();
  handlePreloader();
  setupPlayer();
});

function extractScriptSrc(htmlString?: string | null): string | null {
  if (!htmlString) return null;
  // const regex = /<script[^>]*src=["']([^"']+)["'][^>]*>/i;
  const scriptMatch = htmlString.match(
    /<script\s+src=['"]\s*([^'"]+)\s*['"]\s*.*?>\s*<\/script>/i,
  );
  return scriptMatch ? scriptMatch[1] : null;
}
</script>

<template>
  <div class="fixed inset-0 h-full w-full bg-black">
    <div id="forge-player-container"></div>
  </div>
  <div id="loader-wrapper" ref="loaderContainer">
    <div id="loader" ref="loader"></div>
    <div class="circle-line">
      <div class="circle-blue">&nbsp;</div>
      <div class="circle-red">&nbsp;</div>
      <div class="circle-green">&nbsp;</div>
      <div class="circle-yellow">&nbsp;</div>
    </div>
  </div>
</template>

<style scoped>
@import '../../../../css/player.css';
</style>
