<script setup lang="ts">
import {
  AdminVideoTableColumnHeader,
  AdminVideoTablePagination,
  AdminVideoTableRowAction,
  AdminVideoTableToolbar,
} from '@/components/admin/video';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/lib/utils';
import { PaginatedData, Video } from '@/types';
import { formatTimeAgo } from '@/utils/times';
import { router } from '@inertiajs/vue3';
import type {
  ColumnDef,
  ColumnFiltersState,
  PaginationState,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table';
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  useVueTable,
} from '@tanstack/vue-table';
import { useDebounceFn, useVModel } from '@vueuse/core';
import { computed, h, ref, watch } from 'vue';

const emits = defineEmits<{
  (e: 'update:search', search?: string): void;
  (e: 'update:type', type?: string[]): void;
  (e: 'onSearch', search?: string): void;
  (e: 'onReset'): void;
  (e: 'onView', video: Video): void;
  (e: 'onEdit', video: Video): void;
  (e: 'onCopy', video: Video): void;
  (e: 'onDelete', video: Video): void;
  (e: 'update:selectedData', value: Video[]): void;
  (e: 'openBulkDeleteDialog'): void;
}>();

const props = defineProps<{
  data?: PaginatedData<Video> | null;
  filters?: {
    search?: string;
    type?: string;
    start_date: string;
    end_date: string;
    per_page: number;
    page: number;
  };
  selectedData?: Video[];
  openBulkDeleteDialog?: boolean;
}>();

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});
const pagination = ref<PaginationState>({
  pageIndex: (props.filters?.page || 1) - 1,
  pageSize: Number(props.filters?.per_page) || 20,
});
const selectedVideoData = useVModel(props, 'selectedData', emits, {
  passive: true,
  defaultValue: props.selectedData,
});

const columns: ColumnDef<Video>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        modelValue:
          table.getIsAllPageRowsSelected() ||
          (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:modelValue': (value) =>
          table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all',
        class: 'translate-y-0.5',
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        modelValue: row.getIsSelected(),
        'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
        ariaLabel: 'Select row',
        class: 'translate-y-0.5',
      }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) =>
      h(AdminVideoTableColumnHeader, { column, title: 'Title' }),

    cell: ({ row }) => {
      const title = row.original.title;
      const image = row.original.poster || '/images/default-thumbnail.jpeg';

      return h('div', { class: 'flex items-center gap-2' }, [
        image
          ? h('img', {
              src: image,
              alt: title,
              class: 'h-8 w-8 rounded-md',
            })
          : null,
        h('span', { class: 'max-w-[300px] truncate font-medium' }, title),
      ]);
    },
  },
  {
    accessorKey: 'type',
    header: ({ column }) =>
      h(AdminVideoTableColumnHeader, { column, title: 'Type' }),

    cell: ({ row }) => {
      const typeValue = row.original.type.replaceAll('_', ' ');
      return h(
        Badge,
        { variant: 'default', class: 'capitalize' },
        () => typeValue,
      );
    },
  },
  {
    accessorKey: 'subtitle',
    header: 'Total Subtitle',
    cell: ({ row }) => {
      const count = row.original.subtitles?.length || 0;
      return h('div', { class: 'capitalize' }, count);
    },
  },
  {
    accessorKey: 'views',
    header: ({ column }) =>
      h(AdminVideoTableColumnHeader, { column, title: 'Views' }),
    cell: ({ row }) => h('div', { class: 'capitalize' }, row.original.views),
  },
  {
    accessorKey: 'created_at',
    header: ({ column }) =>
      h(AdminVideoTableColumnHeader, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const createdAt = row.original.created_at;
      return h('div', { class: 'capitalize' }, formatTimeAgo(createdAt));
    },
  },
  {
    id: 'actions',
    cell: ({ row }) =>
      h(AdminVideoTableRowAction, {
        row,
        onView: (video: Video) => emits('onView', video),
        onEdit: (video: Video) => emits('onEdit', video),
        onCopy: (video: Video) => emits('onCopy', video),
        onDelete: (video: Video) => emits('onDelete', video),
      }),
  },
];

const table = useVueTable({
  data: computed(() => props.data?.data || []),
  columns,
  pageCount: props.data?.last_page || 0,
  getCoreRowModel: getCoreRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  manualPagination: true,
  manualSorting: true,
  manualFiltering: true,
  onSortingChange: (updaterOrValue) => {
    valueUpdater(updaterOrValue, sorting);
    updateParams();
  },
  onColumnFiltersChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => {
    valueUpdater(updaterOrValue, rowSelection);
  },
  onPaginationChange: (updaterOrValue) => {
    valueUpdater(updaterOrValue, pagination);
    updateParams();
  },
  getRowId: (row) => row.id.toString(),
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get rowSelection() {
      return rowSelection.value;
    },
    get pagination() {
      return pagination.value;
    },
  },
});

const updateParams = () => {
  const params = {
    ...props.filters,
    page: pagination.value.pageIndex + 1,
    per_page: pagination.value.pageSize,
    sort_field: sorting.value.length ? sorting.value[0].id : undefined,
    sort_direction: sorting.value.length
      ? sorting.value[0].desc
        ? 'desc'
        : 'asc'
      : undefined,
  };

  router.get('/admin/video', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const handleSearch = useDebounceFn((search: string) => {
  router.get(
    '/admin/video',
    {
      ...props.filters,
      search: search,
      page: 1,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
}, 300);

const handleTypeChange = (type: string[]) => {
  router.get(
    '/admin/video',
    {
      ...props.filters,
      type: type.join(','),
      page: 1,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
};

const handleReset = () => {
  emits('onReset');
};

watch(rowSelection, () => {
  const selectedRows = table.getSelectedRowModel().rows;
  selectedVideoData.value = selectedRows.map((row) => row.original);
});
</script>

<template>
  <div class="space-y-4">
    <AdminVideoTableToolbar
      :table="table"
      :search="filters?.search"
      :type="filters?.type ? filters.type.split(',') : []"
      :selected-data="selectedVideoData"
      @on-search="(search) => handleSearch(search ?? '')"
      @on-type-change="(type) => handleTypeChange(type ?? [])"
      @on-reset="handleReset"
      @open-bulk-delete-dialog="() => emits('openBulkDeleteDialog')"
    />
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <AdminVideoTablePagination :table="table" />
  </div>
</template>
