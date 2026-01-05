<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Video } from '@/types';
import type { Row } from '@tanstack/vue-table';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

const emits = defineEmits<{
  (e: 'view', video: Video): void;
  (e: 'edit', video: Video): void;
  (e: 'delete', video: Video): void;
  (e: 'copy', video: Video): void;
}>();
const props = defineProps<{
  row: Row<Video>;
}>();

const video = computed(() => props.row.original);
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        class="flex h-8 w-8 p-0 data-[state=open]:bg-muted"
      >
        <MoreHorizontal class="h-4 w-4" />
        <span class="sr-only">Open menu</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="center" class="w-auto">
      <DropdownMenuItem @click="emits('view', video)"> View </DropdownMenuItem>
      <DropdownMenuItem @click="emits('edit', video)"> Edit </DropdownMenuItem>
      <DropdownMenuItem @click="emits('copy', video)"> Copy </DropdownMenuItem>
      <DropdownMenuItem variant="destructive" @click="emits('delete', video)">
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
