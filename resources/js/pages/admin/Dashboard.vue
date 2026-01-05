<script setup lang="ts">
import {
  AdminDashboardDateToggle,
  AdminDashboardFilterToolbar,
  AdminDashboardTopVideoTable,
} from '@/components/admin/dashboard';
import HeadingSmall from '@/components/HeadingSmall.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { type BreadcrumbItem } from '@/types';
import { formatDateToMySQL } from '@/utils/times';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed, ref, watchEffect } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const data = computed(() => page.props?.dashboard);
const user = computed(() => page.props?.auth?.user);
const filters = computed(() => data.value?.filters);
const stats = computed(() => data.value?.stats);
const perPage = ref(filters.value?.per_page);

useSeoMeta({
  title: `Dashboard - ${setting.value?.site_name || 'Forge Player'}`,
  description: setting.value?.site_description || 'Welcome to Forge Player',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Dashboard - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription:
    setting.value?.site_og_description || 'Welcome to Forge Player',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Dashboard - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription:
    setting.value?.site_twitter_description || 'Welcome to Forge Player',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const dateRange = ref<{ start: string; end: string }>({
  start: filters.value?.start_date || getDefaultStartDate(),
  end: filters.value?.end_date || getDefaultEndDate(),
});

const fetchData = (customParams?: {
  start_date?: string;
  end_date?: string;
  per_page?: number;
}) => {
  const params: any = {
    start_date: customParams?.start_date || dateRange.value.start,
    end_date: customParams?.end_date || dateRange.value.end,
    per_page: customParams?.per_page || perPage.value,
  };

  Object.keys(params).forEach((key) => {
    if (params[key] === undefined || params[key] === null) {
      delete params[key];
    }
  });

  router.get('/admin/dashboard', params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const handleDateRangeChange = (
  newRange: { start: string; end: string } | null,
) => {
  if (newRange) {
    dateRange.value = newRange;
    fetchData({
      start_date: newRange.start,
      end_date: newRange.end,
    });
  }
};
const handlePerPageChange = (value: number) => {
  perPage.value = value;
  fetchData({
    per_page: value,
  });
};

const handleReset = () => {
  dateRange.value = {
    start: getDefaultStartDate(),
    end: getDefaultEndDate(),
  };
  perPage.value = 20;

  router.get(
    '/admin/dashboard',
    {},
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    },
  );
};

const setLastWeek = () => {
  const start = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
  const end = new Date();

  dateRange.value = {
    start: formatDateToMySQL(start, false),
    end: formatDateToMySQL(end, true),
  };

  fetchData({
    start_date: dateRange.value.start,
    end_date: dateRange.value.end,
  });
};

const setLastMonth = () => {
  const start = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
  const end = new Date();

  dateRange.value = {
    start: formatDateToMySQL(start, false),
    end: formatDateToMySQL(end, true),
  };

  fetchData({
    start_date: dateRange.value.start,
    end_date: dateRange.value.end,
  });
};

const setLastYear = () => {
  const start = new Date(Date.now() - 365 * 24 * 60 * 60 * 1000);
  const end = new Date();

  dateRange.value = {
    start: formatDateToMySQL(start, false),
    end: formatDateToMySQL(end, true),
  };

  fetchData({
    start_date: dateRange.value.start,
    end_date: dateRange.value.end,
  });
};

watchEffect(() => {
  if (filters.value?.per_page && filters.value.per_page !== perPage.value) {
    perPage.value = filters.value.per_page;
  }

  if (
    filters.value?.start_date &&
    filters.value.start_date !== dateRange.value.start
  ) {
    dateRange.value.start = filters.value.start_date;
  }

  if (
    filters.value?.end_date &&
    filters.value.end_date !== dateRange.value.end
  ) {
    dateRange.value.end = filters.value.end_date;
  }
});

function getDefaultStartDate(): string {
  const date = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
  return formatDateToMySQL(date, false);
}

function getDefaultEndDate(): string {
  const date = new Date();
  return formatDateToMySQL(date, true);
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="scrollbar-thin flex min-h-[calc(100vh-160px)] flex-col overflow-hidden overflow-y-auto scroll-smooth scrollbar-thumb-foreground scrollbar-track-accent"
    >
      <div class="flex-none px-4 py-4 sm:px-6">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
            Dashboard
          </h1>
          <p class="text-sm text-muted-foreground">
            Wellcome Back
            <span
              class="font-semibold text-indigo-600 uppercase dark:text-indigo-400"
            >
              {{ user?.name }}
            </span>
          </p>
        </div>
      </div>
      <div class="flex-none border-b px-4 py-4 lg:px-6">
        <div class="space-y-4">
          <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
          >
            <AdminDashboardFilterToolbar
              v-model:dateRange="dateRange"
              @update:date-range="handleDateRangeChange"
              @refresh="fetchData()"
              @reset="handleReset"
            />
            <AdminDashboardDateToggle
              v-model:perPage="perPage"
              @update:perPage="handlePerPageChange"
              @setLastWeek="setLastWeek"
              @setLastMonth="setLastMonth"
              @setLastYear="setLastYear"
              @onReset="handleReset"
            />
          </div>
        </div>
      </div>
      <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
      >
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
          <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
          >
            <PlaceholderPattern />
            <div
              class="flex h-full w-full flex-col items-center justify-center gap-2 p-2"
            >
              <div
                class="flex w-full items-center justify-between rounded-xl bg-green-600 p-4 text-white shadow-md"
              >
                <div>
                  <p class="text-xl font-bold">
                    {{ stats?.total_video || 0 }}
                  </p>
                  <p class="text-sm">Videos</p>
                </div>
                <Icon icon="mdi:video" class="h-6 w-6" />
              </div>
              <div
                class="flex w-full items-center justify-between rounded-xl bg-red-600 p-4 text-white shadow-md"
              >
                <div>
                  <p class="text-xl font-bold">
                    {{ stats?.total_subtitles || 0 }}
                  </p>
                  <p class="text-sm">Subtitles</p>
                </div>
                <Icon icon="mdi:subtitles" class="h-6 w-6" />
              </div>
            </div>
          </div>
          <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
          >
            <PlaceholderPattern />
            <div
              class="flex h-full w-full flex-col items-center justify-center gap-2 p-2"
            >
              <div
                class="flex w-full items-center justify-between rounded-xl bg-yellow-600 p-4 text-white shadow-md"
              >
                <div>
                  <p class="text-xl font-bold">
                    {{ stats?.total_views || 0 }}
                  </p>
                  <p class="text-sm">Views</p>
                </div>
                <Icon icon="carbon:view-filled" class="h-6 w-6" />
              </div>
              <div
                class="flex w-full items-center justify-between rounded-xl bg-purple-600 p-4 text-white shadow-md"
              >
                <div>
                  <p class="text-xl font-bold">
                    {{ stats?.total_users || 0 }}
                  </p>
                  <p class="text-sm">Users</p>
                </div>
                <Icon icon="mdi:account" class="h-6 w-6" />
              </div>
            </div>
          </div>
        </div>
        <div
          class="relative min-h-screen flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
        >
          <HeadingSmall title="Top Videos" description="Top videos by views" />
          <AdminDashboardTopVideoTable
            :videos="data?.topVideos?.data || null"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
