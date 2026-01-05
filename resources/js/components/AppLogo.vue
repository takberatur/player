<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
</script>

<template>
  <div
    class="flex aspect-square size-8 items-center justify-center rounded-md bg-transparent text-sidebar-primary-foreground"
  >
    <img
      :src="
        setting?.site_favicon
          ? `${setting?.site_favicon}`
          : '/images/favicon.png'
      "
      :alt="setting?.site_name || 'Forge Player'"
      class="size-6 rounded-md object-cover"
      loading="lazy"
    />
  </div>
  <div class="ml-1 grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{
      setting?.site_name || 'Forge Player'
    }}</span>
  </div>
</template>
