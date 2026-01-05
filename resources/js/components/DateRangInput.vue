<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { cn } from '@/lib/utils';
import type { DateValue } from '@internationalized/date';
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
  toCalendarDateTime,
  toZoned,
} from '@internationalized/date';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import type { DateRange } from 'reka-ui';
import { computed, ref, watchEffect, type HTMLAttributes, type Ref } from 'vue';

const emit = defineEmits<{
  (e: 'update:modelValue', value: { start: string; end: string } | null): void;
}>();

const props = defineProps<{
  modelValue?: { start: string; end: string };
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}>();

const df = new DateFormatter('en-US', {
  dateStyle: 'medium',
});
const calendarDate = new CalendarDate(2023, 0, 20);
const timezone = getLocalTimeZone();

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
    // MySQL format bisa berupa:
    // 1. 'YYYY-MM-DD HH:MM:SS'
    // 2. 'YYYY-MM-DD'
    // 3. ISO format 'YYYY-MM-DDTHH:MM:SSZ'

    let dateString: string;

    // Cek jika mengandung spasi (format MySQL standard)
    if (mysqlDateTime.includes(' ')) {
      // Ambil hanya bagian date-nya
      dateString = mysqlDateTime.split(' ')[0];
    } else if (mysqlDateTime.includes('T')) {
      // ISO format
      dateString = mysqlDateTime.split('T')[0];
    } else {
      // Hanya date
      dateString = mysqlDateTime;
    }

    const [year, month, day] = dateString.split('-').map(Number);

    if (isNaN(year) || isNaN(month) || isNaN(day)) {
      console.warn('Invalid date format:', mysqlDateTime);
      return null;
    }

    // Buat CalendarDate (month dimulai dari 1)
    return new CalendarDate(year, month, day);
  } catch (error) {
    console.error('Error parsing MySQL datetime:', error, mysqlDateTime);
    return null;
  }
};

const dateValueToMySQLDateTime = (
  dateValue: DateValue,
  isEndOfDay: boolean = false,
): string => {
  let date: Date;

  if (dateValue instanceof CalendarDate) {
    date = new Date(dateValue.year, dateValue.month - 1, dateValue.day);

    if (isEndOfDay) {
      date.setHours(23, 59, 59, 999);
    } else {
      date.setHours(0, 0, 0, 0);
    }
  } else {
    try {
      const calendarDateTime = toCalendarDateTime(dateValue);
      const zonedDateTime = toZoned(calendarDateTime, timezone);
      date = zonedDateTime.toDate();

      if (isEndOfDay) {
        date.setHours(23, 59, 59, 999);
      }
    } catch (error) {
      console.error('Error converting DateValue:', error);
      date = new Date();
      if (isEndOfDay) {
        date.setHours(23, 59, 59, 999);
      } else {
        date.setHours(0, 0, 0, 0);
      }
    }
  }

  return dateToMySQLDateTime(date);
};

const selectedRange = ref({
  start: calendarDate,
  end: calendarDate.add({ days: 20 }),
}) as Ref<DateRange>;

watchEffect(() => {
  if (props.modelValue?.start && props.modelValue?.end) {
    const start = mysqlStringToDateValue(props.modelValue.start);
    const end = mysqlStringToDateValue(props.modelValue.end);

    if (start && end) {
      selectedRange.value = { start, end };
    }
  }
});

const handleDateChange = (range: DateRange) => {
  if (!range.start || !range.end) return;

  try {
    const startDateTime = dateValueToMySQLDateTime(range.start, false); // Start of day
    const endDateTime = dateValueToMySQLDateTime(range.end, true); // End of day

    emit('update:modelValue', {
      start: startDateTime,
      end: endDateTime,
    });
  } catch (error) {
    console.error('Error converting date range:', error);
  }
};

const displayText = computed(() => {
  if (!selectedRange.value.start) return 'Pick a date';

  try {
    const startDateStr = props.modelValue?.start?.split(' ')[0] || '';
    const endDateStr = props.modelValue?.end?.split(' ')[0] || '';

    if (startDateStr && endDateStr) {
      const startDate = new Date(startDateStr + 'T00:00:00');
      const endDate = new Date(endDateStr + 'T00:00:00');

      return `${df.format(startDate)} - ${df.format(endDate)}`;
    }

    const startDate = new Date(
      selectedRange.value.start.year,
      selectedRange.value.start.month - 1,
      selectedRange.value.start.day,
    );

    if (selectedRange.value.end) {
      const endDate = new Date(
        selectedRange.value.end.year,
        selectedRange.value.end.month - 1,
        selectedRange.value.end.day,
      );
      return `${df.format(startDate)} - ${df.format(endDate)}`;
    } else {
      return df.format(startDate);
    }
  } catch {
    return 'Pick a date';
  }
});

// const debugDates = () => {
//     console.log('Selected Range:', selectedRange.value);
//     console.log('Model Value:', props.modelValue);
//     console.log('Start MySQL:', props.modelValue?.start);
//     console.log('End MySQL:', props.modelValue?.end);
//     console.log(
//         'Parsed Start:',
//         mysqlStringToDateValue(props.modelValue?.start || ''),
//     );
//     console.log(
//         'Parsed End:',
//         mysqlStringToDateValue(props.modelValue?.end || ''),
//     );
// };
</script>

<template>
  <div :class="cn('grid gap-2', $attrs.class ?? '')">
    <Popover>
      <PopoverTrigger as-child>
        <Button
          id="date"
          :variant="'outline'"
          :class="
            cn(
              'justify-start text-left font-normal',
              !selectedRange.start && 'text-muted-foreground',
              props.class,
            )
          "
        >
          <CalendarIcon class="mr-2 h-4 w-4" />
          {{ displayText }}
        </Button>
      </PopoverTrigger>
      <PopoverContent class="w-auto p-0" align="end">
        <RangeCalendar
          v-model="selectedRange"
          weekday-format="short"
          :number-of-months="2"
          initial-focus
          :disabled="disabled"
          @update:model-value="handleDateChange"
        />
      </PopoverContent>
    </Popover>
  </div>
</template>
