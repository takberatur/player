<script setup lang="ts">
import {
  ClientDocChangeLogSection,
  ClientDocInstallDockerSection,
  ClientDocInstallNginxManualSection,
  ClientDocIntroductionSection,
  ClientDocNginxReverseProxySection,
  ClientDocRequirementSection,
  ClientDocumentationHeader,
  ClientDocumentationSidebar,
} from '@/components/client/documentation';
import { usePage } from '@inertiajs/vue3';
import { useHead, useSeoMeta } from '@unhead/vue';
import { Book, CircleAlert, FileClock, Layers, Rocket } from 'lucide-vue-next';
import { computed, ref, watchEffect } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Documentation - ${setting.value?.site_name || 'Forge Player'}`,
  description: setting.value?.site_description || 'Welcome to Forge Player',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'index, follow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Documentation - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription:
    setting.value?.site_og_description || 'Welcome to Forge Player',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Documentation - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription:
    setting.value?.site_twitter_description || 'Welcome to Forge Player',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

useHead({
  link: [
    {
      rel: 'canonical',
      href: import.meta.env.VITE_APP_URL || window.location.href,
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
        '@type': 'Documentation',
        headline: `Documentation - ${setting.value?.site_name || 'Forge Player'}`,
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

const activeSection = ref('introduction');
const mobileMenuOpen = ref(false);
const expandedSections = ref<Record<string, boolean>>({
  'get-started': true,
  deployment: false,
});

watchEffect(() => {});

const menuItems = [
  {
    id: 'introduction',
    title: 'Introduction',
    icon: Book,
  },
  {
    id: 'requirements',
    title: 'Requirements',
    icon: CircleAlert,
  },
  {
    id: 'installation',
    title: 'Installation',
    icon: Rocket,
    hasSubmenu: true,
    submenu: [
      {
        id: 'installation-nginx-manual',
        title: 'Ubuntu + Nginx (Manual)',
        icon: Layers,
      },
      {
        id: 'installation-docker',
        title: 'Ubuntu + Docker',
        icon: Layers,
      },
      {
        id: 'installation-nginx-proxy',
        title: 'Nginx Reverse Proxy',
        icon: Layers,
      },
    ],
  },
  {
    id: 'change-log',
    title: 'Change Log',
    icon: FileClock,
  },
];

const toggleSection = (sectionId: string) => {
  expandedSections.value[sectionId] = !expandedSections.value[sectionId];
};

const selectMenuItem = (itemId: string) => {
  activeSection.value = itemId;
  mobileMenuOpen.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-background">
    <ClientDocumentationHeader
      v-model:mobileMenuOpen="mobileMenuOpen"
      :setting="setting"
      :menu-items="menuItems"
      :expanded-sections="expandedSections"
      :active-section="activeSection"
      @toggle-section="toggleSection"
      @select-menu-item="selectMenuItem"
    />

    <div class="flex">
      <ClientDocumentationSidebar
        :menu-items="menuItems"
        :expanded-sections="expandedSections"
        :active-section="activeSection"
        @toggle-section="toggleSection"
        @select-menu-item="selectMenuItem"
      />

      <main class="flex-1">
        <div class="container max-w-4xl px-6 py-8">
          <ClientDocIntroductionSection
            v-if="activeSection === 'introduction'"
          />
          <ClientDocRequirementSection
            v-else-if="activeSection === 'requirements'"
          />
          <ClientDocInstallNginxManualSection
            v-else-if="activeSection === 'installation-nginx-manual'"
          />
          <ClientDocInstallDockerSection
            v-else-if="activeSection === 'installation-docker'"
          />
          <ClientDocNginxReverseProxySection
            v-else-if="activeSection === 'installation-nginx-proxy'"
          />
          <ClientDocChangeLogSection
            v-else-if="activeSection === 'change-log'"
          />
        </div>
      </main>
    </div>
  </div>
</template>

<style lang="css" scoped>
@reference "../../../../css/app.css";
/* Custom prose styles for better markdown rendering */
.prose :deep(h1) {
  @apply mt-8 mb-4 text-3xl font-bold;
}

.prose :deep(h2) {
  @apply mt-6 mb-3 text-2xl font-semibold;
}

.prose :deep(h3) {
  @apply mt-4 mb-2 text-xl font-semibold;
}

.prose :deep(pre) {
  @apply overflow-x-auto rounded-lg border bg-muted p-4;
}

.prose :deep(code) {
  @apply rounded bg-muted px-1 py-0.5 font-mono text-sm;
}

.prose :deep(ul) {
  @apply my-4 ml-6 list-disc;
}

.prose :deep(li) {
  @apply my-1;
}
</style>
