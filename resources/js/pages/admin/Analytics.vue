<script setup lang="ts">
import { AdminAnalyticChart } from '@/components/admin/analytics';
import { AdminDashboardFilterToolbar } from '@/components/admin/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
import { analytics } from '@/routes/admin';
import { BreadcrumbItem } from '@/types';
import { formatDateToMySQL } from '@/utils/times';
import { router, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed, ref, watch } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const charts = computed(() => page.props.analytics?.charts);
const summary = computed(() => page.props.analytics?.summary);
const filters = computed(() => page.props.analytics?.filters);

useSeoMeta({
  title: `Analytics - ${setting.value?.site_name || 'Forge Player'}`,
  description: setting.value?.site_description || 'Welcome to Forge Player',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Analytics - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription:
    setting.value?.site_og_description || 'Welcome to Forge Player',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Analytics - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription:
    setting.value?.site_twitter_description || 'Welcome to Forge Player',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const dateRange = ref<{ start: string; end: string }>({
  start: filters.value?.start_date || getDefaultStartDate(),
  end: filters.value?.end_date || getDefaultEndDate(),
});

const rangeDescription = computed(() => {
  if (dateRange.value.start === getDefaultStartDate()) {
    return `Last 30 Days`;
  }
  if (dateRange.value.end === getDefaultEndDate()) {
    return `From ${dateRange.value.start} to Now`;
  }
  return `From ${dateRange.value.start} to ${dateRange.value.end}`;
});

const fetchData = (customParams?: {
  start_date?: string;
  end_date?: string;
}) => {
  const params: any = {
    start_date: customParams?.start_date ?? dateRange.value.start,
    end_date: customParams?.end_date ?? dateRange.value.end,
  };

  if (params.start_date === getDefaultStartDate()) {
    delete params.start_date;
  }
  if (params.end_date === getDefaultEndDate()) {
    delete params.end_date;
  }

  Object.keys(params).forEach((key) => {
    if (params[key] === undefined || params[key] === null) {
      delete params[key];
    }
  });

  router.get('/admin/analytics', params, {
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
const handleReset = () => {
  dateRange.value = {
    start: getDefaultStartDate(),
    end: getDefaultEndDate(),
  };

  fetchData({
    start_date: getDefaultStartDate(),
    end_date: getDefaultEndDate(),
  });
};

watch(
  () => filters.value,
  (newFilters) => {
    if (!newFilters) return;
    if (
      newFilters.start_date &&
      newFilters.start_date !== dateRange.value.start
    ) {
      dateRange.value.start = newFilters.start_date;
    }

    if (newFilters.end_date && newFilters.end_date !== dateRange.value.end) {
      dateRange.value.end = newFilters.end_date;
    }
  },
  { deep: true, immediate: true },
);

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
    title: 'Analytics',
    href: analytics().url,
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
            Analytics
          </h1>
          <p class="text-sm text-muted-foreground">
            Manage your stats analytics
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
          </div>
        </div>
      </div>
      <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
      >
        <div
          class="relative min-h-screen flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
        >
          <AdminAnalyticChart
            :charts="charts"
            :summary="summary"
            :range-description="rangeDescription"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
