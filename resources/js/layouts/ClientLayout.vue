<script setup lang="ts">
import { ClientFooter, ClientHeader } from '@/components/client';

const props = withDefaults(
  defineProps<{
    canRegister: boolean;
  }>(),
  {
    canRegister: true,
  },
);
</script>

<template>
  <div
    class="min-h-screen w-full overflow-x-hidden bg-linear-to-br from-gray-900 via-purple-900 to-violet-900 text-white dark:bg-linear-to-br dark:from-gray-800 dark:via-purple-800 dark:to-violet-800 dark:text-white"
  >
    <ClientHeader :canRegister="props.canRegister" />
    <slot />
    <ClientFooter />
  </div>
</template>
