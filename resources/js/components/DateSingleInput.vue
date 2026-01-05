<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { DateValue } from '@internationalized/date';
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
  toCalendarDateTime,
  toZoned,
  today,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, watchEffect, type HTMLAttributes, type Ref } from 'vue';

const emit = defineEmits<{
  (e: 'update:modelValue', value?: string | null): void;
}>();

const props = defineProps<{
  modelValue?: string;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
  id?: string;
  name?: string;
}>();

const defaultPlaceholder = today(getLocalTimeZone());
const calendarDate = new CalendarDate(2023, 0, 20);
const date = ref() as Ref<DateValue>;
const selectedDate = ref(calendarDate);
const timezone = getLocalTimeZone();

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
});

const dateToMySQLDateTime = (date: Date): string => {
  // Format: YYYY-MM-DD HH:MM:SS
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

const mysqlStringToDateValue = (mysqlDateTime: string): DateValue | null => {
  try {
    if (!mysqlDateTime) return null;

    // Handle MySQL format 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD'
    let datePart = mysqlDateTime;
    if (mysqlDateTime.includes(' ')) {
      datePart = mysqlDateTime.split(' ')[0];
    } else if (mysqlDateTime.includes('T')) {
      datePart = mysqlDateTime.split('T')[0];
    }

    const [year, month, day] = datePart.split('-').map(Number);

    if (isNaN(year) || isNaN(month) || isNaN(day)) {
      return null;
    }

    return new CalendarDate(year, month, day);
  } catch (error) {
    console.error('Error parsing MySQL datetime:', error, mysqlDateTime);
    return null;
  }
};

const dateValueToMySQLDateTime = (dateValue: DateValue): string => {
  let date: Date;

  if (dateValue instanceof CalendarDate) {
    // Default to current time for new date selection
    const now = new Date();
    date = new Date(
      dateValue.year,
      dateValue.month - 1,
      dateValue.day,
      now.getHours(),
      now.getMinutes(),
      now.getSeconds(),
    );
  } else {
    try {
      const calendarDateTime = toCalendarDateTime(dateValue);
      const zonedDateTime = toZoned(calendarDateTime, timezone);
      date = zonedDateTime.toDate();
    } catch (error) {
      console.error('Error converting DateValue:', error);
      date = new Date();
    }
  }

  return dateToMySQLDateTime(date);
};

watchEffect(() => {
  if (props.modelValue) {
    const dateValue = mysqlStringToDateValue(props.modelValue);
    if (dateValue) {
      date.value = dateValue;
    }
  }
});

const handleDateChange = (dateValue: DateValue | undefined) => {
  if (!dateValue) {
    emit('update:modelValue', null);
    return;
  }

  try {
    // Preserve time if modifying existing date, otherwise use current time
    const targetDate = new Date();

    if (props.modelValue) {
      const currentDateTime = new Date(props.modelValue);
      if (!isNaN(currentDateTime.getTime())) {
        targetDate.setHours(
          currentDateTime.getHours(),
          currentDateTime.getMinutes(),
          currentDateTime.getSeconds(),
        );
      }
    }

    // Update with selected date components
    targetDate.setFullYear(dateValue.year);
    targetDate.setMonth(dateValue.month - 1);
    targetDate.setDate(dateValue.day);

    const value = dateToMySQLDateTime(targetDate);
    emit('update:modelValue', value);
    date.value = dateValue;
  } catch (error) {
    console.error('Error updating date:', error);
  }
};

const displayText = computed(() => {
  if (!props.modelValue) return 'Pick a date';

  try {
    const dateObj = new Date(props.modelValue);
    if (isNaN(dateObj.getTime())) return 'Pick a date';

    return df.format(dateObj);
  } catch {
    return 'Pick a date';
  }
});
</script>

<template>
  <Popover v-slot="{ close }">
    <PopoverTrigger as-child>
      <Button
        id="date"
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal',
            !date && 'text-muted-foreground',
            props.class,
            props.disabled && 'cursor-not-allowed opacity-50',
          )
        "
      >
        <CalendarIcon />
        {{ displayText }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <Calendar
        v-model="date"
        :default-placeholder="defaultPlaceholder"
        layout="month-and-year"
        initial-focus
        @update:model-value="
          (v) => {
            handleDateChange(v);
            close();
          }
        "
      />
      <input
        type="hidden"
        :id="props.id"
        :name="props.name"
        :value="modelValue"
      />
    </PopoverContent>
  </Popover>
</template>
