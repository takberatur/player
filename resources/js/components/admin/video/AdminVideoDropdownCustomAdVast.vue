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
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Spinner } from '@/components/ui/spinner';
import type { CustomAdVastTag } from '@/types';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { AlertCircleIcon, PlusCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const emits = defineEmits<{
  (e: 'use-random-custom-ad-vast'): void;
  (e: 'selected-custom-ad-vast', url: string): void;
}>();

const props = defineProps<{
  adVastCount?: number | null;
}>();

const openModalSearchCustomAdVast = ref(false);
const adVastTags = ref<CustomAdVastTag[]>([]);
const searchTerm = ref<string | undefined>(undefined);
const errorMessage = ref<string | null>(null);
const origin = ref(window.location.origin);
const isLoading = ref(false);

const handleSearch = useDebounceFn(async () => {
  if (!searchTerm.value) {
    adVastTags.value = [];
    return;
  }

  isLoading.value = true;
  try {
    const response = await axios.get<{
      success: boolean;
      message: string;
      data: CustomAdVastTag[] | null;
    }>('/admin/api/custom-ads/search', {
      params: {
        name: searchTerm.value,
      },
    });
    if (response.data?.success) {
      adVastTags.value = response.data?.data || [];
    } else {
      errorMessage.value = response.data?.message || 'An error occurred';
    }
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'An unknown error occurred';
  } finally {
    isLoading.value = false;
  }
});

const handleCloseModal = () => {
  openModalSearchCustomAdVast.value = false;
  searchTerm.value = undefined;
  adVastTags.value = [];
  errorMessage.value = null;
};

const handleSelectedCustomAdVast = (tag: CustomAdVastTag) => {
  emits('selected-custom-ad-vast', `${origin.value}/ads/vast/${tag.id}.xml`);
  handleCloseModal();
};
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="default" size="icon-lg">
        <PlusCircle class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="w-56" align="center">
      <DropdownMenuGroup>
        <DropdownMenuItem
          @click="
            () => {
              if (!props.adVastCount || props.adVastCount <= 0) {
                toast.error(
                  `Currently you don't have any custom ad vast. Please add one first.`,
                );
                return;
              }
              emits('use-random-custom-ad-vast');
            }
          "
        >
          Use Random Custom Ad Vast
        </DropdownMenuItem>
        <DropdownMenuItem @click="openModalSearchCustomAdVast = true">
          Choose Custom Ad Vast
        </DropdownMenuItem>
      </DropdownMenuGroup>
    </DropdownMenuContent>
  </DropdownMenu>
  <Dialog
    v-if="openModalSearchCustomAdVast"
    v-model:open="openModalSearchCustomAdVast"
    @update:open="
      (val) => {
        if (!val) {
          handleCloseModal();
        }
      }
    "
  >
    <DialogContent class="mx-auto w-[calc(100%-2rem)] max-w-md">
      <DialogHeader>
        <DialogTitle>Search Custom Ad Vast</DialogTitle>
        <DialogDescription>
          Enter the name of the custom ad vast to search for.
        </DialogDescription>
      </DialogHeader>
      <Alert v-if="errorMessage" variant="destructive">
        <AlertCircleIcon />
        <AlertTitle>Error!</AlertTitle>
        <AlertDescription>
          {{ errorMessage }}
        </AlertDescription>
      </Alert>
      <div class="grid gap-4">
        <Label for="name" class="text-left">Name</Label>
        <Input
          id="name"
          v-model="searchTerm"
          name="name"
          type="text"
          class="w-full"
          @input="handleSearch"
        />
      </div>

      <div
        v-if="isLoading"
        class="flex h-24 w-full flex-col items-center justify-center rounded-md border border-border"
      >
        <div class="m-auto">
          <Spinner class="size-8" />
        </div>
      </div>

      <ScrollArea
        v-else-if="adVastTags.length > 0 && !isLoading"
        class="h-72 w-full overflow-hidden rounded-md border border-border"
      >
        <div class="space-y-3 p-3">
          <div
            v-for="tag in adVastTags"
            :key="tag.id"
            class="flex cursor-pointer flex-col gap-1 overflow-hidden rounded-md border border-border p-2.5 transition-colors hover:bg-muted"
            @click="handleSelectedCustomAdVast(tag)"
          >
            <div class="w-full truncate text-sm font-medium sm:text-base">
              {{ tag.name }}
            </div>
            <p
              class="w-full truncate text-xs text-muted-foreground sm:text-sm"
              :title="`${origin}/ads/vast/${tag.id}.xml`"
            >
              {{ `${origin}/ads/vast/${tag.id}.xml` }}
            </p>
          </div>
        </div>
      </ScrollArea>

      <div
        v-else
        class="flex h-24 w-full flex-col items-center justify-center rounded-md border border-border"
      >
        <div class="m-auto">
          <p class="text-sm text-muted-foreground">No custom ad vast found</p>
        </div>
      </div>

      <DialogFooter class="flex-col-reverse gap-2 sm:flex-row">
        <DialogClose as-child>
          <Button
            variant="destructive"
            class="w-full sm:w-auto"
            @click="handleCloseModal"
          >
            Cancel
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
