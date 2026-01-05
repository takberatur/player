<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
  title?: string;
  description?: string;
}>();

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
</script>

<template>
  <div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-linear-to-br from-gray-900 via-purple-900 to-violet-900 p-6 text-white md:p-10 dark:bg-linear-to-br dark:from-gray-800 dark:via-purple-800 dark:to-violet-800 dark:text-white"
  >
    <div class="flex w-full max-w-md flex-col gap-6">
      <Link
        :href="home()"
        class="flex items-center gap-2 self-center font-medium"
      >
        <div class="flex h-16 w-auto items-center justify-center">
          <img
            :src="
              setting?.site_logo
                ? `${setting?.site_logo}`
                : '/images/logo-small.png'
            "
            :alt="setting?.site_name || 'Forge Player'"
            class="h-16 w-auto rounded-md object-cover"
            loading="lazy"
          />
        </div>
      </Link>

      <div class="flex flex-col gap-6">
        <Card class="rounded-xl">
          <CardHeader class="px-10 pt-8 pb-0 text-center">
            <CardTitle class="text-xl">{{ title }}</CardTitle>
            <CardDescription>
              {{ description }}
            </CardDescription>
          </CardHeader>
          <CardContent class="px-10 py-8">
            <slot />
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
