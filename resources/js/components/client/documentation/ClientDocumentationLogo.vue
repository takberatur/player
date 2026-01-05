<script setup lang="ts">
import type { Setting } from '@/types';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  setting?: Setting | null;
}>();
</script>

<template>
  <Link href="/" class="flex items-center gap-3">
    <div class="flex h-8 w-8 items-center justify-center rounded-lg">
      <img
        :src="props.setting?.site_logo || '/images/logo.png'"
        :alt="props.setting?.site_name || 'Logo'"
        class="h-full w-full"
      />
    </div>
    <div>
      <h1 class="text-lg font-bold">{{ props.setting?.site_name }}</h1>
      <p class="text-xs text-muted-foreground">Deployment Guide</p>
    </div>
  </Link>
</template>
