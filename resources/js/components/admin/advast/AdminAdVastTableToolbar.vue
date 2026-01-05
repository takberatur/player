<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { CustomAdVastTag } from '@/types';
import type { Table } from '@tanstack/vue-table';
import { useVModel } from '@vueuse/core';
import { X } from 'lucide-vue-next';

const emits = defineEmits<{
  (e: 'update:search', search?: string): void;
  (e: 'update:type', type?: string[]): void;
  (e: 'onSearch', search?: string): void;
  (e: 'onReset'): void;
  (e: 'openBulkDeleteDialog'): void;
}>();
const props = defineProps<{
  table: Table<CustomAdVastTag>;
  search?: string;
  selectedData?: CustomAdVastTag[];
}>();

const searchValue = useVModel(props, 'search', emits, {
  defaultValue: '',
  passive: true,
});

const handleSearchChange = (e: Event) => {
  emits('onSearch', (e.target as HTMLInputElement).value);
};
const handleReset = () => {
  searchValue.value = '';
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

      <Button
        v-if="searchValue && searchValue.trim() !== ''"
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
