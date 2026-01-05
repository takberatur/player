<script setup lang="ts">
import CustomLoading from '@/components/CustomLoading.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

const isLoading = ref(false);

router.on('start', () => {
  isLoading.value = true;
});

router.on('progress', (event) => {
  if (
    isLoading.value &&
    event.detail?.progress &&
    event.detail.progress.percentage
  ) {
    isLoading.value = false;
  }
});

router.on('finish', (event) => {
  if (!isLoading.value) {
    return;
  } else if (event.detail.visit.completed) {
    isLoading.value = false;
  } else if (event.detail.visit.interrupted) {
    isLoading.value = false;
  } else if (event.detail.visit.cancelled) {
    isLoading.value = false;
  }
});
</script>

<template>
  <CustomLoading v-if="isLoading" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
  </AppLayout>
</template>
