<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import type { Video } from '@/types';
import { useVModel } from '@vueuse/core';
import { computed } from 'vue';

const emits = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'close'): void;
}>();
const props = defineProps<{
  open?: boolean;
  video?: Video | null;
  host?: string;
}>();

const isOpen = useVModel(props, 'open', emits, {
  defaultValue: false,
  passive: true,
});
const videoData = computed(() => props.video);

const handleClose = (val: boolean) => {
  if (!val) {
    isOpen.value = false;
    emits('close');
  }
};
</script>

<template>
  <Dialog
    v-model:open="isOpen"
    class="bg-linear-to-br from-gray-900 via-purple-900 to-violet-900 text-white dark:bg-linear-to-br dark:from-gray-800 dark:via-purple-800 dark:to-violet-800 dark:text-white"
    @update:open="handleClose"
  >
    <DialogContent
      class="flex h-screen w-screen max-w-none flex-col gap-0 border-none bg-linear-to-br from-gray-900 via-purple-900 to-violet-900 p-0 text-white sm:max-w-none dark:bg-linear-to-br dark:from-gray-800 dark:via-purple-800 dark:to-violet-800 dark:text-white"
    >
      <DialogHeader class="p-6">
        <DialogTitle>{{ videoData?.title || 'Watch Video' }}</DialogTitle>
        <DialogDescription as-child>
          <div class="grid gap-2">
            <div class="text-gray-300">
              Watch streaming from
              <span class="font-bold text-green-600 uppercase">
                {{ videoData?.type.replaceAll('_', ' ') }}
              </span>
              sources
            </div>
            <div class="text-gray-300">
              Original Source :
              <a
                :href="videoData?.original_link"
                target="_blank"
                class="text-blue-400 hover:underline"
              >
                {{ videoData?.original_link }}
              </a>
            </div>
          </div>
        </DialogDescription>
      </DialogHeader>
      <div class="relative w-full flex-1 bg-black/50">
        <iframe
          :src="`${props.host}/play/${videoData?.id}`"
          class="absolute inset-0 h-full w-full"
          frameborder="0"
          allow="
            accelerometer;
            autoplay;
            encrypted-media;
            gyroscope;
            picture-in-picture;
          "
          allowfullscreen
        ></iframe>
      </div>
      <DialogFooter class="p-6">
        <DialogClose as-child>
          <Button
            type="button"
            variant="destructive"
            @click="handleClose(false)"
          >
            Close
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
