<script setup lang="ts">
import { AdminDashboardFilterToolbar } from '@/components/admin/dashboard';
import { AdminVideoTable } from '@/components/admin/video';
import AppDialog from '@/components/AppDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as videoIndex } from '@/routes/admin/video';
import { BreadcrumbItem, Video } from '@/types';
import { formatDateToMySQL } from '@/utils/times';
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const videos = computed(() => page.props.videoPaginateProps);
const filters = computed(() => page.props.videoPaginateProps?.filters);

const dateRange = ref<{ start: string; end: string }>({
  start: filters.value?.start_date || getDefaultStartDate(),
  end: filters.value?.end_date || getDefaultEndDate(),
});
const perPage = ref(filters.value?.per_page);
const searchTerm = ref<string | undefined>(undefined);
const type = ref<string[]>([]);
const sortField = ref<string[]>([]);
const sortDirection = ref<string[]>([]);
const openDialogDelete = ref(false);
const videoToDelete = ref<Video | null>(null);
const selectedVideos = ref<Video[]>([]);
const openBulkDeleteDialog = ref(false);

useSeoMeta({
  title: `Videos - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Manage your videos',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Videos - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Manage your videos',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Videos - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Manage your videos',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

function handleProcessDeleteVideo() {
  if (!videoToDelete.value) {
    return;
  }
  router.delete(`/admin/video/${videoToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
  openDialogDelete.value = false;
  videoToDelete.value = null;
}
const fetchData = (customParams?: {
  start_date?: string;
  end_date?: string;
  per_page?: number;
  search?: string;
  type?: string;
  page?: number;
  sort_field?: string;
  sort_direction?: string;
}) => {
  const params: any = {
    page: customParams?.page,
    start_date: customParams?.start_date ?? dateRange.value.start,
    end_date: customParams?.end_date ?? dateRange.value.end,
    per_page: customParams?.per_page ?? perPage.value,
    search: customParams?.search ?? searchTerm.value,
    type: customParams?.type ? type.value.join(',') : undefined,
    sort_field:
      customParams?.sort_field ??
      (sortField.value.length ? sortField.value.join(',') : undefined),
    sort_direction:
      customParams?.sort_direction ??
      (sortDirection.value.length ? sortDirection.value.join(',') : undefined),
  };

  if (params.start_date === getDefaultStartDate()) {
    delete params.start_date;
  }
  if (params.end_date === getDefaultEndDate()) {
    delete params.end_date;
  }
  if (params.per_page === 10) {
    delete params.per_page;
  }
  if (params.page === 1) {
    delete params.page;
  }

  Object.keys(params).forEach((key) => {
    if (params[key] === undefined || params[key] === null) {
      delete params[key];
    }
  });

  router.get('/admin/video', params, {
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
  perPage.value = 10;
  type.value = [];
  searchTerm.value = undefined;
  sortField.value = [];
  sortDirection.value = [];

  fetchData({
    type: undefined,
    search: undefined,
    start_date: getDefaultStartDate(),
    end_date: getDefaultEndDate(),
    per_page: 20,
    sort_field: undefined,
    sort_direction: undefined,
  });
};

watch(
  () => filters.value,
  (newFilters) => {
    if (!newFilters) return;

    if (newFilters.per_page && newFilters.per_page !== perPage.value) {
      perPage.value = newFilters.per_page;
    }

    if (
      newFilters.start_date &&
      newFilters.start_date !== dateRange.value.start
    ) {
      dateRange.value.start = newFilters.start_date;
    }

    if (newFilters.end_date && newFilters.end_date !== dateRange.value.end) {
      dateRange.value.end = newFilters.end_date;
    }
    if (newFilters.search && newFilters.search !== searchTerm.value) {
      searchTerm.value = newFilters.search;
    }
    if (newFilters.type) {
      const filterType = String(newFilters.type);
      const currentType = type.value.join(',');
      if (filterType !== currentType) {
        type.value = filterType.split(',');
      }
    }
    if (newFilters.sort_field) {
      const filterSortField = newFilters.sort_field;
      const currentSortField = sortField.value.join(',');
      if (filterSortField !== currentSortField) {
        sortField.value = [filterSortField];
      }
    }
    if (newFilters.sort_direction) {
      const filterSortDirection = newFilters.sort_direction;
      const currentSortDirection = sortDirection.value.join(',');
      if (filterSortDirection !== currentSortDirection) {
        sortDirection.value = [filterSortDirection];
      }
    }
  },
  { deep: true, immediate: true },
);

watch([type, sortField, sortDirection], () => {
  fetchData({
    type: type.value?.length ? type.value.join(',') : undefined,
    search: searchTerm.value,
    sort_field: sortField.value?.length ? sortField.value.join(',') : undefined,
    sort_direction: sortDirection.value?.length
      ? sortDirection.value.join(',')
      : undefined,
  });
});

watchDebounced(
  searchTerm,
  () => {
    fetchData({
      type: type.value?.length ? type.value.join(',') : undefined,
      search: searchTerm.value,
    });
  },
  { debounce: 500 },
);

function getDefaultStartDate(): string {
  const date = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
  return formatDateToMySQL(date, false);
}

function getDefaultEndDate(): string {
  const date = new Date();
  return formatDateToMySQL(date, true);
}

function handleSelectVideos(value: Video[]) {
  selectedVideos.value = value;
}

function handleBulkDeleteVideos() {
  if (selectedVideos.value.length === 0) {
    return;
  }
  router.post(
    '/admin/video/bulk-destroy',
    {
      ids: selectedVideos.value.map((video) => video.id),
    },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    },
  );
  openBulkDeleteDialog.value = false;
  selectedVideos.value = [];
}
function handleView(video?: Video) {
  if (!video) return;

  window.open(`/play/${video.id}`, '_blank');
}
function handleCopy(video?: Video) {
  try {
    if (!video) throw new Error('Video not found');
    navigator.clipboard.writeText(`${window.location.origin}/play/${video.id}`);
    toast.success('Video copied to clipboard');
  } catch (error) {
    toast.error(
      error instanceof Error ? error.message : 'Failed to copy video',
    );
  }
}
function handleIframe(video?: Video) {
  try {
    if (!video) throw new Error('Video not found');
    navigator.clipboard.writeText(
      `<iframe src="${window.location.origin}/play/${video.id}" frameborder="0" allowfullscreen width="100%" height="100%" frameborder="0"></iframe>`,
    );
    toast.success('Iframe copied to clipboard');
  } catch (error) {
    toast.error(
      error instanceof Error ? error.message : 'Failed to copy iframe',
    );
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Videos',
    href: videoIndex().url,
  },
];
</script>

<template>
  <Head>
    <title>Videos - {{ page.props.setting?.site_name ?? '' }}</title>
    <meta
      name="description"
      :content="`${page.props.setting?.site_description ?? ''} - Videos`"
    />
    <meta name="robots" content="noindex, nofollow" />
  </Head>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="scrollbar-thin flex min-h-[calc(100vh-160px)] flex-col overflow-hidden overflow-y-auto scroll-smooth scrollbar-thumb-foreground scrollbar-track-accent"
    >
      <div class="flex-none px-4 py-4 sm:px-6">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Videos</h1>
          <p class="text-sm text-muted-foreground">Manage your videos</p>
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
          <div class="flex items-center justify-end">
            <Link href="/admin/video/create">
              <Button variant="default" size="default">
                <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
                Add Video
              </Button>
            </Link>
          </div>
          <AdminVideoTable
            :data="videos?.videos"
            :filters="filters"
            @on-view="handleView"
            @on-edit="(video) => router.get(`/admin/video/${video.id}/edit`)"
            @on-copy="handleCopy"
            @on-iframe="handleIframe"
            @on-delete="
              (video) => {
                openDialogDelete = true;
                videoToDelete = video;
              }
            "
            @update:selected-data="handleSelectVideos"
            @on-reset="handleReset"
            @open-bulk-delete-dialog="() => (openBulkDeleteDialog = true)"
          />
        </div>
      </div>
    </div>

    <AppDialog
      v-model:open="openDialogDelete"
      type="warning"
      title="Delete Video"
      :message="`Are you sure you want to delete this video: ${videoToDelete?.title ?? ''}?`"
      label-close="Cancel"
      :is-action="true"
      label-action="Delete"
      @onclose="() => (openDialogDelete = false)"
      @onaction="handleProcessDeleteVideo"
    />
    <AppDialog
      v-model:open="openBulkDeleteDialog"
      type="warning"
      title="Delete Videos"
      :message="`Are you sure you want to delete ${selectedVideos.length} videos?`"
      label-close="Cancel"
      :is-action="true"
      label-action="Delete"
      @onclose="() => (openBulkDeleteDialog = false)"
      @onaction="handleBulkDeleteVideos"
    />
  </AppLayout>
</template>
