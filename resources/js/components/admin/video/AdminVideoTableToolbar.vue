<script setup lang="ts">
import CommandSelectInput from '@/components/CommandSelectInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Video } from '@/types';
import type { Table } from '@tanstack/vue-table';
import { useVModel } from '@vueuse/core';
import { X } from 'lucide-vue-next';

const emits = defineEmits<{
  (e: 'update:search', search?: string): void;
  (e: 'update:type', type?: string[]): void;
  (e: 'onSearch', search?: string): void;
  (e: 'onTypeChange', type?: string[]): void;
  (e: 'onReset'): void;
  (e: 'openBulkDeleteDialog'): void;
}>();
const props = defineProps<{
  table: Table<Video>;
  search?: string;
  type?: string[];
  selectedData?: Video[];
}>();

const searchValue = useVModel(props, 'search', emits, {
  defaultValue: '',
  passive: true,
});
const typeValue = useVModel(props, 'type', emits, {
  defaultValue: [],
  passive: true,
});

const typeOptions = [
  { value: 'google_drive', label: 'Google Drive' },
  { value: 'yandex_disk', label: 'Yandex Disk' },
  { value: 'amazon_drive', label: 'Amazon Drive' },
  { value: 'archive', label: 'Archive' },
  { value: 'facebook', label: 'Facebook' },
  { value: 'google_photo', label: 'Google Photo' },
  { value: 'rumble', label: 'Rumble' },
  { value: 'dropbox', label: 'Dropbox' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'mega_disk', label: 'Mega Disk' },
  { value: 'm3u8', label: 'M3U8' },
  { value: 'mp4', label: 'MP4' },
  { value: 'mkv', label: 'MKV' },
  { value: 'ts', label: 'TS' },
  { value: 'mpd', label: 'MPD' },
];

const handleSearchChange = (e: Event) => {
  emits('onSearch', (e.target as HTMLInputElement).value);
};
const handleReset = () => {
  searchValue.value = '';
  typeValue.value = undefined;
  emits('onReset');
};
</script>

<template>
  <div class="flex items-center justify-between">
    <div class="flex w-full flex-col items-center gap-2 lg:flex-row">
      <Input
        v-model="searchValue"
        name="search"
        placeholder="Search video..."
        class="h-8 w-full lg:w-auto"
        @input="handleSearchChange"
      />

      <CommandSelectInput
        v-model="typeValue"
        :options="typeOptions"
        title="Filter By"
        @update:model-value="
          (val) => {
            emits('update:type', val);
            emits('onTypeChange', val);
          }
        "
      />

      <Button
        v-if="
          (searchValue && searchValue.trim() !== '') ||
          (typeValue && typeValue?.length > 0)
        "
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="handleReset"
      >
        Reset
        <X class="ml-2 h-4 w-4" />
      </Button>
      <Button
        v-if="selectedData && selectedData?.length > 0"
        variant="destructive"
        class="h-8 px-2 lg:px-3"
        @click="emits('openBulkDeleteDialog')"
      >
        Delete selected
      </Button>
    </div>
  </div>
</template>
