<script setup lang="ts">
import type { ButtonVariants } from '@/components/ui/button';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useVModel } from '@vueuse/core';
import { computed } from 'vue';

const emits = defineEmits<{
  (e: 'update:perPage', perPage: number): void;
  (e: 'onReset'): void;
  (e: 'setLastWeek'): void;
  (e: 'setLastMonth'): void;
  (e: 'setLastYear'): void;
}>();
const props = defineProps<{
  perPage?: number;
}>();

const perPage = useVModel(props, 'perPage', emits, {
  defaultValue: props.perPage,
  passive: (props.perPage === undefined) as false,
});

const perPageString = computed({
  get: () => (perPage.value ? String(perPage.value) : ''),
  set: (val) => {
    perPage.value = Number(val);
  },
});

const toggleItem = [
  {
    value: 'week',
    label: 'Week',
    action: () => emits('setLastWeek'),
    variant: 'outline' as ButtonVariants['variant'],
  },
  {
    value: 'month',
    label: 'Month',
    action: () => emits('setLastMonth'),
    variant: 'outline' as ButtonVariants['variant'],
  },
  {
    value: 'year',
    label: 'Year',
    action: () => emits('setLastYear'),
    variant: 'outline' as ButtonVariants['variant'],
  },
  {
    value: 'reset',
    label: 'Reset',
    action: () => emits('onReset'),
    variant: 'destructive' as ButtonVariants['variant'],
  },
];
</script>

<template>
  <div class="flex flex-col items-center gap-4 lg:flex-row lg:gap-2">
    <div class="flex items-center justify-center gap-1">
      <Select v-model="perPageString">
        <SelectTrigger class="h-8 w-auto lg:w-auto">
          <SelectValue>
            {{ perPage }}
          </SelectValue>
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Per Page</SelectLabel>
            <SelectItem value="10"> 10 </SelectItem>
            <SelectItem value="20"> 20 </SelectItem>
            <SelectItem value="50"> 50 </SelectItem>
            <SelectItem value="100"> 100 </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>

      <Button
        v-for="item in toggleItem"
        :key="item.value"
        :variant="item.variant"
        @click="item.action()"
      >
        {{ item.label }}
      </Button>
    </div>
  </div>
</template>
