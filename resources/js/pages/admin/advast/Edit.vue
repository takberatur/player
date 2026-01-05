<script setup lang="ts">
import CustomAdVastTagController from '@/actions/App/Http/Controllers/Admin/CustomAdVastTagController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { edit } from '@/routes/admin/custom-ads';
import { BreadcrumbItem } from '@/types';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const adVastTag = computed(() => page.props.adVastTag);
const videoType = ref<'url' | 'file'>('url');
const UPLOAD_CONFIG = {
  maxSize: 50 * 1024 * 1024, // 50MB
  allowedMimes: [
    'video/mp4',
    'video/webm',
    'video/ogg',
    'video/mov',
    'video/qt',
  ],
};

useSeoMeta({
  title: `Edit Ad Vast Tag ${adVastTag.value?.name || ''} - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Edit an Ad Vast Tag',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Edit Ad Vast Tag ${adVastTag.value?.name || ''} - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Edit an Ad Vast Tag',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Edit Ad Vast Tag ${adVastTag.value?.name || ''} - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Edit an Ad Vast Tag',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const form = useForm<CreateAdVastForm>({
  name: adVastTag.value?.name || '',
  video_url: adVastTag.value?.video_url
    ? adVastTag.value.video_url.startsWith('http')
      ? adVastTag.value.video_url
      : `${window.location.origin}${adVastTag.value.video_url.startsWith('/') ? '' : '/'}${adVastTag.value.video_url}`
    : '',
  video_file: null,
  direct_link_ad: adVastTag.value?.direct_link_ad || '',
  duration: adVastTag.value?.duration || '00:00:15',
  additional_direct_link_ad: adVastTag.value?.additional_direct_link_ad || '',
  tracking_url:
    adVastTag.value?.tracking_url || `${window.location.origin}/ads/track`,
});

const isValidUrl = (value?: string) => {
  if (!value) return true;
  try {
    const url = new URL(value);
    return url.protocol === 'http:' || url.protocol === 'https:';
  } catch {
    return false;
  }
};

const validateVideoUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('video_url', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('video_url');
  }
};

const validateDirectLinkUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url)
    return form.setError('direct_link_ad', 'Direct link ad is required');

  if (!isValidUrl(url)) {
    form.setError('direct_link_ad', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('direct_link_ad');
  }
};

const validateAdditionalDirectLinkUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('additional_direct_link_ad', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('additional_direct_link_ad');
  }
};

const validateTrackingUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('tracking_url', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('tracking_url');
  }
};

const handleUploadChange = (e: any) => {
  let file: File | undefined;

  // Handle native event
  if (e?.target instanceof HTMLInputElement && e.target.files?.length) {
    file = e.target.files[0];
  }
  // Handle direct File object or Array of files (custom components)
  else if (e instanceof File) {
    file = e;
  } else if (Array.isArray(e) && e.length && e[0] instanceof File) {
    file = e[0];
  } else if (e instanceof FileList && e.length) {
    file = e[0];
  }
  // Fallback to form value if already updated via v-model
  else if (form.video_file instanceof File) {
    file = form.video_file;
  }

  if (!file) {
    // If file is cleared or invalid input, clear the form value
    form.video_file = null;
    return;
  }

  if (file.size > UPLOAD_CONFIG.maxSize) {
    return form.setError(
      'video_file',
      `File size must be less than ${UPLOAD_CONFIG.maxSize / 1024 / 1024}MB`,
    );
  }

  if (!UPLOAD_CONFIG.allowedMimes.includes(file.type)) {
    return form.setError(
      'video_file',
      `File type must be ${UPLOAD_CONFIG.allowedMimes.join(', ')}`,
    );
  }

  form.clearErrors('video_file');
  form.video_file = file;
};

const submit = () => {
  const route = CustomAdVastTagController.update(adVastTag.value?.id || '');

  form
    .transform((data) => {
      // Jika tipe 'file', kosongkan 'video_url'
      // Jika tipe 'url', kosongkan 'video_file'
      const payload = { ...data };

      if (videoType.value === 'file') {
        payload.video_url = undefined;
      } else {
        payload.video_file = null;
      }

      return payload;
    })
    .put(route.url, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: (response) => {
        router.visit('/admin/custom-ads');
      },
    });
};

const typeOptions = [
  {
    value: 'url',
    label: 'URL',
  },
  {
    value: 'file',
    label: 'File',
  },
];

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: `Edit Ad Vast Tag ${adVastTag.value?.name || ''}`,
    href: edit(adVastTag.value?.id || '').url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="scrollbar-thin flex min-h-[calc(100vh-160px)] flex-col overflow-hidden overflow-y-auto scroll-smooth scrollbar-thumb-foreground scrollbar-track-accent"
    >
      <div class="flex-none px-4 py-4 sm:px-6">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
            Edit Custom Ad VAST Tag
          </h1>
          <p class="text-sm text-muted-foreground">
            Edit custom ad vast tag {{ adVastTag?.name || '' }}
          </p>
        </div>
        <div
          class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
          <div
            class="relative min-h-screen flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
          >
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid gap-2">
                <Label for="name"
                  >Name <span class="text-red-500">*</span></Label
                >
                <Input
                  id="name"
                  v-model="form.name"
                  class="mt-1 block w-full"
                  name="name"
                  required
                  autocomplete="name"
                  placeholder="Name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>
              <div class="grid gap-2">
                <Label for="duration">
                  Duration <span class="text-red-500">*</span>
                </Label>
                <Input
                  id="duration"
                  v-model="form.duration"
                  class="mt-1 block w-full"
                  name="duration"
                  type="text"
                  required
                  autocomplete="duration"
                  placeholder="00:00:15"
                  pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                />
                <p class="text-xs text-gray-500">
                  Format: HH:MM:SS (e.g. 00:00:15)
                </p>
                <InputError class="mt-2" :message="form.errors.duration" />
              </div>
              <div class="grid gap-2">
                <Label
                  >Ad Vast Video Type <span class="text-red-500">*</span></Label
                >
                <Select v-model="videoType">
                  <SelectTrigger class="mt-1 w-full">
                    <SelectValue placeholder="Select video type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectItem
                        v-for="option in typeOptions"
                        :key="option.value"
                        :value="option.value"
                      >
                        {{ option.label }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
              </div>
              <div v-if="videoType === 'url'" class="grid gap-2">
                <Label for="video_url"
                  >Video URL <span class="text-red-500">*</span></Label
                >
                <Input
                  id="video_url"
                  v-model="form.video_url"
                  class="mt-1 block w-full"
                  name="video_url"
                  autocomplete="video_url"
                  placeholder="Video URL"
                  @input="validateVideoUrl"
                />
                <InputError class="mt-2" :message="form.errors.video_url" />
              </div>
              <div v-if="videoType === 'file'" class="grid gap-2">
                <Label for="video_file"
                  >Video File <span class="text-red-500">*</span></Label
                >
                <UFileUpload
                  id="video_file"
                  v-model="form.video_file"
                  class="mt-1 block w-full cursor-pointer"
                  name="video_file"
                  dropzone
                  accept="video/*"
                  label="Drag and drop video file or click to select"
                  description="Only video files are allowed."
                  icon="mdi:video-outline"
                  color="neutral"
                  highlight
                  size="xl"
                  layout="list"
                  :multiple="false"
                  :disabled="form.processing"
                  @change="handleUploadChange"
                />
                <InputError class="mt-2" :message="form.errors.video_file" />
              </div>
              <div class="grid gap-2">
                <Label for="direct_link_ad"
                  >Direct Link Ad <span class="text-red-500">*</span></Label
                >
                <Input
                  id="direct_link_ad"
                  v-model="form.direct_link_ad"
                  class="mt-1 block w-full"
                  name="direct_link_ad"
                  required
                  autocomplete="url"
                  placeholder="Example: https://example.com/direct-link-ad"
                  @input="validateDirectLinkUrl"
                />
                <InputError
                  class="mt-2"
                  :message="form.errors.direct_link_ad"
                />
              </div>
              <div class="grid gap-2">
                <Label for="additional_direct_link_ad">
                  Additional Direct Link Ad
                  <span class="text-muted">(optional)</span>
                </Label>
                <Input
                  id="additional_direct_link_ad"
                  v-model="form.additional_direct_link_ad"
                  class="mt-1 block w-full"
                  name="additional_direct_link_ad"
                  autocomplete="url"
                  placeholder="Example: https://example.com/direct-link-ad"
                  @input="validateAdditionalDirectLinkUrl"
                />
                <InputError
                  class="mt-2"
                  :message="form.errors.additional_direct_link_ad"
                />
              </div>
              <div class="grid gap-2">
                <Label for="tracking_url">
                  Tracking URL
                  <span class="text-red-500">*</span>
                </Label>
                <Input
                  id="tracking_url"
                  v-model="form.tracking_url"
                  class="mt-1 block w-full"
                  name="tracking_url"
                  autocomplete="url"
                  readonly
                  required
                  placeholder="Example: https://example.com/tracking-url"
                  @input="validateTrackingUrl"
                />
                <InputError class="mt-2" :message="form.errors.tracking_url" />
              </div>
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-4">
                  <Button :disabled="form.processing"> Update </Button>

                  <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                  >
                    <p
                      v-if="form.recentlySuccessful"
                      class="text-sm text-gray-600"
                    >
                      Updated.
                    </p>
                  </Transition>
                </div>
                <Button
                  type="button"
                  variant="outline"
                  @click="() => router.get('/admin/custom-ads')"
                >
                  Back
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
