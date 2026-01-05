<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/admin/settings/appearance';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Appearance settings - ${setting.value?.site_name || 'Forge Player'}`,
  description: "Update your account's appearance settings",
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Appearance settings - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: "Update your account's appearance settings",
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Appearance settings - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: "Update your account's appearance settings",
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Appearance settings',
    href: edit().url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="Appearance settings"
          description="Update your account's appearance settings"
        />
        <AppearanceTabs />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
