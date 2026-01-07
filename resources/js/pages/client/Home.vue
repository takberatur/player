<script setup lang="ts">
import {
  ClientBenefits,
  ClientCTA,
  ClientExamplePlayer,
  ClientFeature,
  ClientHero,
} from '@/components/client';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Video } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useHead, useSeoMeta } from '@unhead/vue';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    canRegister: boolean;
    videos?: Video[];
    host?: string;
  }>(),
  {
    canRegister: true,
  },
);

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `${setting.value?.site_name || 'Forge Player'} - ${setting.value?.site_tagline || 'Welcome to Forge Player'}`,
  description: setting.value?.site_description || 'Welcome to Forge Player',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'index, follow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: setting.value?.site_og_title || 'Forge Player',
  ogDescription:
    setting.value?.site_og_description || 'Welcome to Forge Player',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: setting.value?.site_twitter_title || 'Forge Player',
  twitterDescription:
    setting.value?.site_twitter_description || 'Welcome to Forge Player',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});
useHead({
  link: [
    {
      rel: 'canonical',
      href: import.meta.env.VITE_APP_URL || (typeof window !== 'undefined' ? window.location.href : ''),
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
    { property: 'og:type', content: 'website' },
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
        headline: setting.value?.site_og_title,
        description: setting.value?.site_og_description,
        image: setting.value?.site_logo ?? '/images/logo.png',
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
</script>

<template>
  <ClientLayout :canRegister="props.canRegister">
    <ClientHero />
    <ClientFeature />
    <ClientBenefits />
    <ClientExamplePlayer :videos="props.videos" :host="props.host" />
    <ClientCTA />
  </ClientLayout>
</template>
