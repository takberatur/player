<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItemType } from '@/types';
import { Icon } from '@iconify/vue';
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

const emits = defineEmits<{
  (e: 'cacheClear'): void;
}>();
withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    onCacheClear?: () => void;
  }>(),
  {
    breadcrumbs: () => [],
    onCacheClear: () => {},
  },
);
const { appearance, updateAppearance } = useAppearance();

const switchState = computed({
  get: () => appearance.value === 'dark',
  set: (val) => {
    updateAppearance(val ? 'dark' : 'light');
  },
});
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex w-full items-center justify-between">
      <div class="flex items-center gap-2">
        <SidebarTrigger class="-ml-1" />
        <template v-if="breadcrumbs && breadcrumbs.length > 0">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </template>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="ghost" size="icon" @click="emits('cacheClear')">
          <Icon icon="mdi:broom" class="text-sm" />
        </Button>
        <Button
          variant="ghost"
          size="icon"
          class="bg-transparent outline-none hover:bg-transparent active:bg-transparent dark:bg-transparent dark:outline-none dark:hover:bg-transparent dark:active:bg-transparent"
          @click="switchState = !switchState"
        >
          <Sun v-if="switchState" class="flex h-3 w-3 text-yellow-400" />
          <Moon v-else class="flex h-3 w-3 text-sky-500" />
        </Button>
      </div>
    </div>
  </header>
</template>
