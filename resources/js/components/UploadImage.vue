<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';

const emit = defineEmits<{
  (e: 'change', event: Event): void;
}>();

defineProps<{
  id: string;
  name: string;
  modelValue: string | null;
  error?: string | null;
}>();
</script>

<template>
  <div class="grid gap-2">
    <Label
      :for="id"
      class="flex w-full cursor-pointer items-center justify-center rounded-lg border"
    >
      <div
        v-if="modelValue"
        class="flex flex-col items-center justify-center py-6"
      >
        <img
          :src="modelValue"
          class="h-20 w-20 rounded-full object-cover object-center"
          loading="lazy"
          decoding="auto"
        />
      </div>
      <div v-else class="flex flex-col items-center justify-center py-6">
        <Icon
          icon="material-symbols:upload-rounded"
          class="mb-4 h-8 w-8 text-neutral-500 dark:text-neutral-400"
        />
        <p class="mb-2 text-sm text-neutral-500 dark:text-neutral-400">
          <span class="font-semibold">Click to upload</span> or drag and drop
        </p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
          Allowed formats: .jpg .png .svg (MAX Size: 2MB)
        </p>
      </div>

      <input
        :id="id"
        type="file"
        class="hidden"
        :name="name"
        accept="image/*"
        @change="emit('change', $event)"
      />
    </Label>
    <InputError v-if="error" class="mt-2" :message="error" />
  </div>
</template>
