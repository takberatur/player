<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppDialog from '@/components/AppDialog.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { cacheClear } from '@/routes/admin';
import type { BreadcrumbItemType } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

const page = usePage<SharedData>();

const successMessage = ref(false);
const errorMessage = ref(false);

const handleCacheClear = () => {
  router.post(
    cacheClear.url(),
    {},
    {
      preserveScroll: true,
    },
  );
};

const clearPagePropsMessages = () => {
  if (page.props.success || page.props.error) {
    router.replace({
      props: {
        ...page.props,
        success: null,
        error: null,
      },
      preserveState: true,
      preserveScroll: true,
    });
  }
};

const clearError = () => {
  errorMessage.value = false;
  clearPagePropsMessages();
};

const clearSuccess = () => {
  successMessage.value = false;
  clearPagePropsMessages();
};

watch(
  () => page.props.success,
  (newSuccess) => {
    if (newSuccess && !successMessage.value) {
      successMessage.value = true;

      setTimeout(() => {
        clearSuccess();
      }, 5000);
    }

    if (newSuccess === null && successMessage.value !== false) {
      successMessage.value = false;
    }
  },
  { immediate: true },
);

watch(
  () => page.props.error,
  (newError) => {
    if (newError && !errorMessage.value) {
      errorMessage.value = true;
    }

    if (newError === null && errorMessage.value !== false) {
      errorMessage.value = false;
    }
  },
  { immediate: true },
);

watch(
  () => ({ ...page.props }),
  (newProps, oldProps) => {
    // console.log('All props changed:', { newProps, oldProps });
  },
  { deep: true },
);
</script>

<template>
  <AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-hidden">
      <AppSidebarHeader
        :breadcrumbs="breadcrumbs"
        @cacheClear="handleCacheClear"
      />
      <slot />
      <AppDialog
        v-if="errorMessage"
        :open="true"
        type="error"
        title="Error!"
        :message="page.props.error || 'An error occurred'"
        @close="clearError"
      />
      <AppDialog
        v-if="successMessage"
        :open="true"
        type="success"
        title="Success!"
        :message="page.props.success || 'Operation completed successfully'"
        @close="clearSuccess"
      />
    </AppContent>
  </AppShell>
</template>
