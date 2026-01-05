<script setup lang="ts">
import DateRangInput from '@/components/DateRangInput.vue';
import { Button } from '@/components/ui/button';
import { isMobile } from '@/lib/is-mobile';
import { Icon } from '@iconify/vue';
import { useVModel } from '@vueuse/core';
import { computed } from 'vue';

const emits = defineEmits<{
  (e: 'update:dateRange', dateRange: { start: string; end: string }): void;
  (e: 'refresh'): void;
  (e: 'reset'): void;
}>();
const props = defineProps<{
  dateRange: {
    start: string;
    end: string;
  };
}>();

const dateValue = useVModel(props, 'dateRange', emits, {
  defaultValue: props.dateRange,
  passive: (props.dateRange === undefined) as false,
});

const mobile = computed(() => isMobile.value);

const handleDateChange = (dateRange: { start: string; end: string }) => {
  if (!dateRange.start || !dateRange.end) {
    return;
  }
  dateValue.value = dateRange;
  emits('update:dateRange', dateRange);
};
</script>

<template>
  <div class="flex w-auto flex-col items-center gap-2 lg:flex-row">
    <DateRangInput
      v-model="dateValue"
      @update:model-value="(value) => value && handleDateChange(value)"
    />
    <div class="flex items-center gap-2">
      <Button
        variant="outline"
        :size="mobile ? 'sm' : 'icon'"
        @click="emits('refresh')"
      >
        <Icon icon="material-symbols:refresh" />
        <span class="not-sr-only text-xs lg:sr-only">Refresh</span>
      </Button>
      <Button
        variant="destructive"
        :size="mobile ? 'sm' : 'icon'"
        @click="emits('reset')"
      >
        <Icon icon="material-symbols:clear-all" />
        <span class="not-sr-only text-xs lg:sr-only">Reset</span>
      </Button>
    </div>
  </div>
</template>
