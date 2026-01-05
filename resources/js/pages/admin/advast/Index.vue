<script setup lang="ts">
import { AdminAdVastTable } from '@/components/admin/advast';
import { AdminDashboardFilterToolbar } from '@/components/admin/dashboard';
import AppDialog from '@/components/AppDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as advastIndex } from '@/routes/admin/custom-ads';
import { BreadcrumbItem, CustomAdVastTag } from '@/types';
import { formatDateToMySQL } from '@/utils/times';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const adVastTags = computed(() => page.props.adVastTagPaginateProps);
const filters = computed(() => page.props.adVastTagPaginateProps?.filters);

const dateRange = ref<{ start: string; end: string }>({
  start: filters.value?.start_date || getDefaultStartDate(),
  end: filters.value?.end_date || getDefaultEndDate(),
});
const perPage = ref(filters.value?.per_page);
const searchTerm = ref<string | undefined>(undefined);
const type = ref<string[]>([]);
const openDialogDelete = ref(false);
const adVastTagToDelete = ref<CustomAdVastTag | null>(null);
const selectedAdVastTags = ref<CustomAdVastTag[]>([]);
const openBulkDeleteDialog = ref(false);

useSeoMeta({
  title: `Ad Vast Tags - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Manage Ad Vast Tags',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Ad Vast Tags - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Manage Ad Vast Tags',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Ad Vast Tags - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Manage Ad Vast Tags',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

function handleProcessDeleteAdVastTag() {
  if (!adVastTagToDelete.value) {
    return;
  }
  router.delete(`/admin/custom-ads/${adVastTagToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
  openDialogDelete.value = false;
  adVastTagToDelete.value = null;
}
const fetchData = (customParams?: {
  start_date?: string;
  end_date?: string;
  per_page?: number;
  search?: string;
  page?: number;
}) => {
  const params: any = {
    page: customParams?.page,
    start_date: customParams?.start_date ?? dateRange.value.start,
    end_date: customParams?.end_date ?? dateRange.value.end,
    per_page: customParams?.per_page ?? perPage.value,
    search: customParams?.search ?? searchTerm.value,
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

  router.get('/admin/custom-ads', params, {
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

  fetchData({
    search: undefined,
    start_date: getDefaultStartDate(),
    end_date: getDefaultEndDate(),
    per_page: 20,
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
  },
  { deep: true, immediate: true },
);

watchDebounced(
  searchTerm,
  () => {
    fetchData({
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

function handleSelectAdVastTags(value: CustomAdVastTag[]) {
  selectedAdVastTags.value = value;
}

function handleBulkDeleteAdVastTags() {
  if (selectedAdVastTags.value.length === 0) {
    return;
  }
  router.post(
    '/admin/custom-ads/bulk-destroy',
    {
      ids: selectedAdVastTags.value.map((adVastTag) => adVastTag.id),
    },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    },
  );
  openBulkDeleteDialog.value = false;
  selectedAdVastTags.value = [];
}

function handleCopy(adVastTag?: CustomAdVastTag) {
  try {
    if (!adVastTag) throw new Error('Ad Vast Tag not found');
    navigator.clipboard.writeText(
      `${window.location.origin}/ads/vast/${adVastTag.id}.xml`,
    );
    toast.success('Ad Vast Tag copied to clipboard');
  } catch (error) {
    toast.error(
      error instanceof Error ? error.message : 'Failed to copy Ad Vast Tag',
    );
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Ad Vast Tags',
    href: advastIndex().url,
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
            Ad Vast Tags
          </h1>
          <p class="text-sm text-muted-foreground">Manage your Ad Vast Tags</p>
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
            <Link href="/admin/custom-ads/create">
              <Button variant="default" size="default">
                <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
                Add Ad Vast Tag
              </Button>
            </Link>
          </div>
          <AdminAdVastTable
            :data="adVastTags?.adVastTags"
            :filters="filters"
            @on-edit="
              (adVastTag) =>
                router.get(`/admin/custom-ads/${adVastTag.id}/edit`)
            "
            @on-copy="handleCopy"
            @on-delete="
              (adVastTag) => {
                openDialogDelete = true;
                adVastTagToDelete = adVastTag;
              }
            "
            @update:selected-data="handleSelectAdVastTags"
            @on-reset="handleReset"
            @open-bulk-delete-dialog="() => (openBulkDeleteDialog = true)"
          />
        </div>
      </div>
    </div>

    <AppDialog
      v-model:open="openDialogDelete"
      type="warning"
      title="Delete Ad Vast Tag"
      :message="`Are you sure you want to delete this ad vast tag: ${adVastTagToDelete?.name ?? ''}?`"
      label-close="Cancel"
      :is-action="true"
      label-action="Delete"
      @onclose="() => (openDialogDelete = false)"
      @onaction="handleProcessDeleteAdVastTag"
    />
    <AppDialog
      v-model:open="openBulkDeleteDialog"
      type="warning"
      title="Delete Ad Vast Tags"
      :message="`Are you sure you want to delete ${selectedAdVastTags.length} ad vast tags?`"
      label-close="Cancel"
      :is-action="true"
      label-action="Delete"
      @onclose="() => (openBulkDeleteDialog = false)"
      @onaction="handleBulkDeleteAdVastTags"
    />
  </AppLayout>
</template>
