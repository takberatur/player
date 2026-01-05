<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import { CheckIcon, CirclePlusIcon } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

interface Option {
  label: string;
  value: string;
}

interface Props {
  modelValue?: string[];
  options: Option[];
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  title: '',
});

const emit = defineEmits<{
  'update:modelValue': [value: string[]];
}>();

const selectedValue = useVModel(props, 'modelValue', emit, {
  defaultValue: props.modelValue,
});

const openFilter = ref(false);

const closeAndFocusTrigger = (triggerId: string) => {
  openFilter.value = false;
  nextTick().then(() => {
    document.getElementById(triggerId)?.focus();
  });
};

const handleSelect = (optionValue: string) => {
  const newSelected = [...selectedValue.value];
  const index = newSelected.indexOf(optionValue);

  if (index > -1) {
    newSelected.splice(index, 1);
  } else {
    newSelected.push(optionValue);
  }

  selectedValue.value = newSelected;
  closeAndFocusTrigger('command-filter-trigger');

  emit('update:modelValue', newSelected);
};

const isSelected = (value: string) => {
  return selectedValue.value.includes(value);
};

const selectedOptions = computed(() => {
  return props.options.filter((opt) => selectedValue.value.includes(opt.value));
});
</script>

<template>
  <Popover v-model:open="openFilter">
    <PopoverTrigger as-child>
      <Button
        id="command-filter-trigger"
        variant="outline"
        size="sm"
        class="h-8 w-full border-dashed px-2 lg:w-auto"
      >
        <CirclePlusIcon class="mr-2 h-4 w-4" />
        {{ title || 'Filter' }}

        <template v-if="selectedValue.length > 0">
          <Separator orientation="vertical" class="mx-2 h-4" />
          <Badge
            variant="secondary"
            class="rounded-sm px-1 font-normal lg:hidden"
          >
            {{ selectedValue.length }}
          </Badge>

          <div class="hidden space-x-1 lg:flex">
            <template v-if="selectedValue.length > 2">
              <Badge variant="secondary" class="rounded-sm px-1 font-normal">
                {{ selectedValue.length }} selected
              </Badge>
            </template>
            <template v-else>
              <Badge
                v-for="option in selectedOptions"
                :key="option.value"
                variant="secondary"
                class="rounded-sm px-1 font-normal"
              >
                {{ option.label }}
              </Badge>
            </template>
          </div>
        </template>
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-full p-0 lg:w-50" align="center">
      <Command class="w-full">
        <CommandInput placeholder="Filter..." class="h-8 w-full" />
        <CommandList>
          <CommandEmpty>No results</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              @select="handleSelect(option.value)"
            >
              <div
                :class="
                  cn(
                    'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                    isSelected(option.value)
                      ? 'bg-primary text-primary-foreground'
                      : 'opacity-50 [&_svg]:invisible',
                  )
                "
              >
                <CheckIcon class="h-4 w-4" />
              </div>
              <span>{{ option.label }}</span>
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
