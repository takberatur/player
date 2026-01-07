<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const versions = ref([
  {
    title: `${setting.value?.site_name} v1.0.0`,
    description: `${setting.value?.site_name} v1.0.0 is out - Forge Player v1.0.0 has been released. The first release version featuring the jwplayer generator application with a new built-in component, better warnings, and performance improvements!`,
    image: '/images/cover-logo.png',
    date: page.props?.release_date
      ? new Date(page.props.release_date).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        })
      : 'N/A',
    to: window.location.origin,
    target: '_blank',
    ui: {
      container: 'max-w-lg',
    },
  },
]);
</script>

<template>
  <UChangelogVersions :versions="versions">
    <template #image="slotProps">
      <img
        :src="slotProps.version.image"
        :alt="slotProps.version.title"
        class="h-full w-full rounded-md object-cover"
      />
    </template>
  </UChangelogVersions>
</template>
