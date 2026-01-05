<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useVModel } from '@vueuse/core';
import { AlertCircleIcon } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

const emits = defineEmits<{
  (e: 'update:open', open: boolean): void;
  (e: 'close'): void;
  (e: 'onSuccess', value?: { name: string; poster: string }): void;
}>();
const props = defineProps<{
  open?: boolean;
}>();

const isOpen = useVModel(props, 'open', emits, {
  defaultValue: false,
  passive: true,
});

const form = reactive({
  tmdbValue: '',
  tmdbType: '',
});

const errorMessage = ref<string | undefined>(undefined);

const isProcessing = ref<boolean>(false);

const handleSuccess = (value?: { name: string; poster: string }) => {
  emits('onSuccess', value);
  isOpen.value = false;
};

const handleSubmit = async () => {
  isProcessing.value = true;

  try {
    const response = await fetch(
      `https://api.themoviedb.org/3/${form.tmdbType}/${form.tmdbValue}?api_key=1c58a427e276213702038d51303bdebe&language=en-US`,
      {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
      },
    );

    const result = await response.json();
    if (!response.ok) {
      throw new Error(
        result.status_message ||
          'Failed to fetch data from TMDB. Please try again.',
      );
    }
    handleSuccess({
      name: `${result.title || result.original_title || result.name} ${result.release_date?.substring(0, 4) || result.first_air_date?.substring(0, 4) || ''}`,
      poster: `https://image.tmdb.org/t/p/original/${result.backdrop_path}`,
    });
  } catch (error) {
    errorMessage.value =
      error instanceof Error
        ? error.message
        : 'Failed to fetch data from TMDB. Please try again.';
  } finally {
    isProcessing.value = false;
  }
};

const typeOption = [
  {
    label: 'Movie',
    value: 'movie',
  },
  {
    label: 'TV Show',
    value: 'tv',
  },
];
</script>

<template>
  <Button variant="default" @click="isOpen = true">
    Generate Info From TMDB
  </Button>
  <Dialog v-model:open="isOpen" @update:open="(open) => (isOpen = open)">
    <form>
      <DialogContent class="mx-auto flex flex-col gap-10 py-12 sm:max-w-106.25">
        <DialogHeader>
          <DialogTitle> Generate Video Info From TMDB </DialogTitle>
          <DialogDescription>
            Please enter the tmdb ID to generate the info from TMDB.
            <span
              ><a href="https://www.themoviedb.org/" target="_blank"
                >TMDB</a
              ></span
            >
          </DialogDescription>
        </DialogHeader>
        <div class="grid w-full gap-4">
          <Alert v-if="errorMessage" variant="destructive">
            <AlertCircleIcon />
            <AlertTitle>Error!</AlertTitle>
            <AlertDescription>
              {{ errorMessage }}
            </AlertDescription>
          </Alert>
          <div class="grid gap-2">
            <Label for="tmdb_id">TMDB ID</Label>
            <Input
              id="tmdb_id"
              v-model="form.tmdbValue"
              class="mt-1 block w-full bg-neutral-100 text-black dark:bg-neutral-800 dark:text-white"
              name="tmdb_id"
              required
              autocomplete="tmdb_id"
              placeholder="TMDB ID"
            />
          </div>
          <div class="grid gap-2">
            <Label for="tmdb_type">TMDB Type</Label>
            <Select v-model="form.tmdbType" name="tmdb_type" required>
              <SelectTrigger
                class="w-full bg-neutral-100 text-black dark:bg-neutral-800 dark:text-white"
              >
                <SelectValue placeholder="Select TMDB Type" />
              </SelectTrigger>
              <SelectContent side="top">
                <SelectGroup>
                  <SelectItem
                    v-for="item in typeOption"
                    :key="item.value"
                    :value="item.value"
                  >
                    {{ item.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <div class="flex items-center gap-4">
            <Button :disabled="isProcessing" @click="handleSubmit">
              <Spinner v-if="isProcessing" />
              Generate
            </Button>
          </div>

          <DialogClose as-child>
            <Button type="button" variant="destructive" @click="isOpen = false">
              Cancel
            </Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
