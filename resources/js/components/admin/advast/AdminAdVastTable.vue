<script setup lang="ts">
import {
  AdminAdVastTablePagination,
  AdminAdVastTableRowAction,
  AdminAdVastTableToolbar,
} from '@/components/admin/advast';
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
import { CustomAdVastTag, PaginatedData } from '@/types';
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
  (e: 'onSearch', search?: string): void;
  (e: 'onReset'): void;
  (e: 'onEdit', video: CustomAdVastTag): void;
  (e: 'onCopy', video: CustomAdVastTag): void;
  (e: 'onDelete', video: CustomAdVastTag): void;
  (e: 'update:selectedData', value: CustomAdVastTag[]): void;
  (e: 'openBulkDeleteDialog'): void;
}>();

const props = defineProps<{
  data?: PaginatedData<CustomAdVastTag> | null;
  filters?: {
    search?: string;
    start_date: string;
    end_date: string;
    per_page: number;
    page: number;
  };
  selectedData?: CustomAdVastTag[];
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

const columns: ColumnDef<CustomAdVastTag>[] = [
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
    accessorKey: 'name',
    header: 'Name',
    cell: ({ row }) => {
      const name = row.original.name;
      return h('div', { class: 'max-w-[300px] truncate font-medium' }, name);
    },
  },
  {
    accessorKey: 'file_url',
    header: 'File URL',
    cell: ({ row }) => {
      const fileUrl = `${window.location.origin}/ads/vast/${row.original.id}.xml`;
      return h('div', { class: 'max-w-[300px] truncate font-medium' }, fileUrl);
    },
  },
  {
    accessorKey: 'created_at',
    header: 'Created At',
    cell: ({ row }) => {
      const createdAt = row.original.created_at;
      return h('div', { class: 'capitalize' }, formatTimeAgo(createdAt));
    },
  },
  {
    id: 'actions',
    cell: ({ row }) =>
      h(AdminAdVastTableRowAction, {
        row,
        onEdit: (video: CustomAdVastTag) => emits('onEdit', video),
        onCopy: (video: CustomAdVastTag) => emits('onCopy', video),
        onDelete: (video: CustomAdVastTag) => emits('onDelete', video),
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

  router.get('/admin/custom-ads', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const handleSearch = useDebounceFn((search: string) => {
  router.get(
    '/admin/custom-ads',
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
    <AdminAdVastTableToolbar
      :table="table"
      :search="filters?.search"
      :selected-data="selectedVideoData"
      @on-search="(search) => handleSearch(search ?? '')"
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

    <AdminAdVastTablePagination :table="table" />
  </div>
</template>
