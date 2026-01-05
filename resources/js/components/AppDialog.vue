<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { useVModel } from '@vueuse/core';

interface Props {
  open?: boolean;
  type?: 'success' | 'error' | 'warning' | 'info';
  title?: string;
  message?: string;
  isAction?: boolean;
  labelAction?: string;
  labelClose?: string;
}
const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'onclose'): void;
  (e: 'onaction'): void;
}>();
const props = withDefaults(defineProps<Props>(), {
  open: false,
  type: 'info',
  title: 'Modal Title',
  message: 'Modal Message',
  isAction: false,
  labelAction: 'OK',
  labelClose: 'CLOSE',
});

const isOpen = useVModel(props, 'open', emit, {
  passive: true,
});

const close = () => {
  emit('onclose');
};
const action = () => {
  emit('onaction');
};

function getIcon(type: Props['type']) {
  switch (type) {
    case 'success':
      return 'fluent-color:checkmark-circle-48';
    case 'error':
      return 'fluent-color:dismiss-circle-48';
    case 'warning':
      return 'fluent-color:warning-48';
    case 'info':
      return 'flat-color-icons:info';
    default:
      return 'flat-color-icons:info';
  }
}
</script>

<template>
  <AlertDialog v-model:open="isOpen" @update:open="(value) => (isOpen = value)">
    <AlertDialogContent
      class="mx-auto flex w-md flex-col items-center justify-center gap-10 bg-neutral-50 py-12 dark:bg-neutral-900"
    >
      <AlertDialogHeader>
        <AlertDialogTitle class="flex w-full flex-col items-center">
          <div
            class="flex h-16 w-16 items-center justify-center rounded-full p-px"
          >
            <Icon :icon="getIcon(type)" class="h-full w-full" />
          </div>
        </AlertDialogTitle>
        <AlertDialogDescription>
          <div class="flex w-full flex-col items-center">
            <div
              :class="
                cn(
                  'text-center text-xl font-bold',
                  type === 'warning'
                    ? 'text-yellow-500'
                    : type === 'info'
                      ? 'text-blue-600'
                      : type === 'error'
                        ? 'text-red-600'
                        : type === 'success'
                          ? 'text-green-600'
                          : 'text-blue-600',
                )
              "
            >
              {{ title }}
            </div>
            <div
              class="line-clamp-3 max-w-[320px] text-center text-sm text-neutral-600 dark:text-neutral-200"
            >
              {{ message }}
            </div>
          </div>
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter class="max-w-md">
        <div class="flex max-w-md items-center justify-center gap-2">
          <AlertDialogCancel
            :class="
              buttonVariants({
                variant: 'destructive',
                class: 'w-full text-xs font-semibold',
              })
            "
            @click="close"
            >{{ labelClose }}
          </AlertDialogCancel>
          <AlertDialogAction
            v-if="isAction"
            :class="
              buttonVariants({
                variant: 'default',
                class: 'w-full text-xs font-semibold',
              })
            "
            @click="action"
            >{{ labelAction }}
          </AlertDialogAction>
        </div>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
