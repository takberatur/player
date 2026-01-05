<script setup lang="ts">
import VideoController from '@/actions/App/Http/Controllers/Admin/VideoController';
import {
  AdminVideoDropdownCustomAdVast,
  AdminVideoListAdVastProvider,
  AdminVideoSourceExampleList,
  AdminVideoTmdbInfo,
} from '@/components/admin/video';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { create } from '@/routes/admin/video';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const openDialogTmdbInfo = ref<boolean>(false);
const openDialogExampleList = ref<boolean>(false);

useSeoMeta({
  title: `Create Video - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Create a new Video',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Create Video - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Create a new Video',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Create Video - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Create a new Video',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const form = useForm<CreateVideoForm>({
  title: '',
  original_link: '',
  type: 'google_drive',
  poster: '',
  ad_vast: '',
  enable_popunder_ad: false,
  popunder_ad_code: '',
  additional_ad_vast: '',
  enable_button_download: false,
  download_link: '',
  subtitles: [],
});

const handleSourceUrlChange = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (url.includes('drive.google.com')) {
    form.type = 'google_drive';
  } else if (url.includes('youtu.be') || url.includes('youtube.com')) {
    form.type = 'youtube';
  } else if (url.includes('facebook.com')) {
    form.type = 'facebook';
  } else if (url.includes('archive.org')) {
    form.type = 'archive';
  } else if (
    url.includes('photos.google.com') ||
    url.includes('photos.app.goo.gl')
  ) {
    form.type = 'google_photo';
  } else if (url.includes('rumble.com')) {
    form.type = 'rumble';
  } else if (url.includes('yadi.sk') || url.includes('yandex.disk')) {
    form.type = 'yandex_disk';
  } else if (url.includes('dropbox.com')) {
    form.type = 'dropbox';
  } else if (url.includes('amazon.com')) {
    form.type = 'amazon_drive';
  } else if (url.includes('mega.nz')) {
    form.type = 'mega_disk';
  } else if (/\.m3u8$/i.test(url) || /\b(hls|m3u8)\b/i.test(url)) {
    form.type = 'm3u8';
  } else if (/\.mp4$/i.test(url)) {
    form.type = 'mp4';
  } else if (/\.mkv$/i.test(url)) {
    form.type = 'mkv';
  } else if (/\.ts$/i.test(url) || /\b(ts|tls)\b/i.test(url)) {
    form.type = 'ts';
  } else if (/\.mpd$/i.test(url)) {
    form.type = 'mpd';
  } else {
    form.errors.type = 'Invalid video type';
  }
};

const isValidUrl = (value?: string) => {
  if (!value) return true;
  try {
    const url = new URL(value);
    return url.protocol === 'http:' || url.protocol === 'https:';
  } catch {
    return false;
  }
};

const validateAdVastUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('ad_vast', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('ad_vast');
  }
};

const validateAdditionalAdVastUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('additional_ad_vast', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('additional_ad_vast');
  }
};

const validatePosterUrl = (e: Event) => {
  const url = (e.target as HTMLInputElement).value;
  if (!url) return;

  if (!isValidUrl(url)) {
    form.setError('poster', 'Invalid URL. Use http(s)://');
  } else {
    form.clearErrors('poster');
  }
};

const handleTmdbInfoSuccess = (value?: { name: string; poster: string }) => {
  if (!value) return;
  form.title = value.name;
  form.poster = value.poster;
};

const addSubtitle = () => {
  if (!form.subtitles) form.subtitles = [];
  form.subtitles.push({
    language: '',
    name: '',
    file: null as unknown as File,
  });
};

const removeSubtitle = (index: number) => {
  if (form.subtitles) {
    form.subtitles.splice(index, 1);
  }
};

const handleSubtitleFileChange = (e: Event, index: number) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    if (form.subtitles) {
      form.subtitles[index].file = target.files[0];
    }
  }
};

const isValidatePopunderAdCode = (value?: string) => {
  if (!value) return true;
  return /<script\s+src=['"]\s*https?:\/\/.*?\s*['"]\s*.*?>\s*<\/script>/i.test(
    value,
  );
};

const validatePopunderAdCode = (e: Event) => {
  const value = (e.target as HTMLInputElement).value;
  if (!isValidatePopunderAdCode(value)) {
    form.setError('popunder_ad_code', 'Invalid popunder ad code');
  } else {
    form.clearErrors('popunder_ad_code');
  }
};

const handleRandomCustomAdVast = (type: 'ad_vast' | 'additional_ad_vast') => {
  if (type === 'ad_vast') {
    form.ad_vast = `${window.location.origin}/ads/vast.xml`;
  } else if (type === 'additional_ad_vast') {
    form.additional_ad_vast = `${window.location.origin}/ads/vast.xml`;
  }
};

const submit = () => {
  const route = VideoController.store();

  form.post(route.url, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (response) => {
      router.visit('/admin/video');
    },
  });
};

const typeOptions = [
  {
    value: 'google_drive',
    label: 'Google Drive',
  },
  {
    value: 'youtube',
    label: 'YouTube',
  },
  {
    value: 'yandex_disk',
    label: 'Yandex Disk',
  },
  {
    value: 'amazon_drive',
    label: 'Amazon Drive',
  },
  {
    value: 'mega_disk',
    label: 'Mega Disk',
  },
  {
    value: 'archive',
    label: 'Archive.org',
  },
  {
    value: 'facebook',
    label: 'Facebook',
  },
  {
    value: 'google_photo',
    label: 'Google Photo',
  },
  {
    value: 'rumble',
    label: 'Rumble',
  },
  {
    value: 'dropbox',
    label: 'Dropbox',
  },
  {
    value: 'm3u8',
    label: 'M3U8',
  },
  {
    value: 'mp4',
    label: 'MP4',
  },
  {
    value: 'mkv',
    label: 'MKV',
  },
  {
    value: 'ts',
    label: 'TS',
  },
  {
    value: 'mpd',
    label: 'MPD',
  },
];

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Videos',
    href: create().url,
  },
];
</script>

<template>
  <Head>
    <title>Create Video - {{ page.props.setting?.site_name ?? '' }}</title>
    <meta
      name="description"
      :content="`${page.props.setting?.site_description ?? ''} - Create Video`"
    />
    <meta name="robots" content="noindex, nofollow" />
  </Head>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="scrollbar-thin flex min-h-[calc(100vh-160px)] flex-col overflow-hidden overflow-y-auto scroll-smooth scrollbar-thumb-foreground scrollbar-track-accent"
    >
      <div class="flex-none px-4 py-4 sm:px-6">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
            Create Video
          </h1>
          <p class="text-sm text-muted-foreground">Create new video</p>
        </div>
        <div
          class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
          <div
            class="relative min-h-screen flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
          >
            <div
              class="flex flex-col items-center justify-end gap-1 md:flex-row"
            >
              <AdminVideoTmdbInfo
                v-model:open="openDialogTmdbInfo"
                @close="openDialogTmdbInfo = false"
                @on-success="handleTmdbInfoSuccess"
              />
              <AdminVideoSourceExampleList
                v-model:open="openDialogExampleList"
                @close="openDialogExampleList = false"
              />
              <AdminVideoListAdVastProvider />
            </div>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid gap-2">
                <Label for="title">Title</Label>
                <Input
                  id="title"
                  v-model="form.title"
                  class="mt-1 block w-full"
                  name="title"
                  required
                  autocomplete="title"
                  placeholder="Title"
                />
                <InputError class="mt-2" :message="form.errors.title" />
              </div>
              <div class="grid gap-2">
                <Label for="original_link">Source Video Link</Label>
                <Input
                  id="original_link"
                  v-model="form.original_link"
                  class="mt-1 block w-full"
                  name="original_link"
                  required
                  autocomplete="on"
                  placeholder="Source Video Link"
                  @input="handleSourceUrlChange"
                />
                <InputError class="mt-2" :message="form.errors.original_link" />
              </div>
              <div class="grid gap-2">
                <Label for="poster">Thumbnail / Poster URL</Label>
                <Input
                  id="poster"
                  v-model="form.poster"
                  class="mt-1 block w-full"
                  name="poster"
                  type="url"
                  autocomplete="url"
                  placeholder="Thumbnail / Poster URL"
                  @input="validatePosterUrl"
                />
                <InputError class="mt-2" :message="form.errors.poster" />
              </div>
              <div class="grid gap-2">
                <Label for="type">Video Type</Label>
                <Select v-model="form.type" name="type">
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
                <InputError class="mt-2" :message="form.errors.type" />
              </div>
              <div class="grid gap-2">
                <Label for="ad_vast">Ad VAST</Label>
                <div class="flex items-center">
                  <Input
                    id="ad_vast"
                    v-model="form.ad_vast"
                    class="mt-1 block w-full"
                    name="ad_vast"
                    type="url"
                    autocomplete="url"
                    placeholder="Example: https://example.com/ad.vast"
                    @input="validateAdVastUrl"
                  />
                  <AdminVideoDropdownCustomAdVast
                    :adVastCount="page.props.adVastCount"
                    @use-random-custom-ad-vast="
                      () => handleRandomCustomAdVast('ad_vast')
                    "
                    @selected-custom-ad-vast="(val) => (form.ad_vast = val)"
                  />
                </div>
                <InputError class="mt-2" :message="form.errors.ad_vast" />
              </div>
              <div class="grid gap-2">
                <Label for="additional_ad_vast">Additional Ad VAST</Label>
                <div class="flex items-center">
                  <Input
                    id="additional_ad_vast"
                    v-model="form.additional_ad_vast"
                    class="mt-1 block w-full"
                    name="additional_ad_vast"
                    type="url"
                    autocomplete="url"
                    placeholder="Example: https://example.com/additional_ad.vast"
                    @input="validateAdditionalAdVastUrl"
                  />
                  <AdminVideoDropdownCustomAdVast
                    :adVastCount="page.props.adVastCount"
                    @use-random-custom-ad-vast="
                      () => handleRandomCustomAdVast('additional_ad_vast')
                    "
                    @selected-custom-ad-vast="
                      (val) => (form.additional_ad_vast = val)
                    "
                  />
                </div>
                <InputError
                  class="mt-2"
                  :message="form.errors.additional_ad_vast"
                />
              </div>
              <div class="grid gap-2">
                <div class="flex items-start gap-3">
                  <Switch
                    id="enable_popunder_ad"
                    v-model="form.enable_popunder_ad"
                    name="enable_popunder_ad"
                    @update:model-value="
                      (checked) => {
                        form.enable_popunder_ad = checked;
                      }
                    "
                  />
                  <div class="grid gap-2">
                    <Label for="enable_popunder_ad">Enable popunder ad</Label>
                    <p class="text-sm text-muted-foreground">
                      By clicking this checkbox, you agree to enable popunder
                      ad.
                    </p>
                  </div>
                </div>
                <InputError
                  class="mt-2"
                  :message="form.errors.enable_popunder_ad"
                />
              </div>
              <div v-if="form.enable_popunder_ad" class="grid gap-2">
                <Label for="popunder_ad_code">Popunder Ad Code</Label>
                <Textarea
                  id="popunder_ad_code"
                  v-model="form.popunder_ad_code"
                  class="mt-1 block w-full"
                  name="popunder_ad_code"
                  autocomplete="url"
                  placeholder="Example: <script src='https://example.com/popunder.js'></script>"
                  @input="validatePopunderAdCode"
                />
                <InputError
                  class="mt-2"
                  :message="form.errors.popunder_ad_code"
                />
              </div>
              <div class="grid gap-2">
                <div class="flex items-start gap-3">
                  <Switch
                    id="enable_button_download"
                    v-model="form.enable_button_download"
                    name="enable_button_download"
                    @update:model-value="
                      (checked) => {
                        form.enable_button_download = checked;
                      }
                    "
                  />
                  <div class="grid gap-2">
                    <Label for="enable_button_download"
                      >Enable button download</Label
                    >
                    <p class="text-sm text-muted-foreground">
                      By clicking this checkbox, you agree to enable button
                      download.
                    </p>
                  </div>
                </div>
                <InputError
                  class="mt-2"
                  :message="form.errors.enable_button_download"
                />
              </div>
              <div v-if="form.enable_button_download" class="grid gap-2">
                <Label for="download_link">Download Link</Label>
                <Input
                  id="download_link"
                  v-model="form.download_link"
                  class="mt-1 block w-full"
                  name="download_link"
                  type="url"
                  autocomplete="url"
                  placeholder="Download Link"
                />
                <InputError class="mt-2" :message="form.errors.download_link" />
              </div>
              <div class="grid gap-2">
                <Label>Subtitles</Label>
                <div class="grid gap-4">
                  <div
                    v-for="(subtitle, index) in form.subtitles || []"
                    :key="index"
                    class="rounded-lg border p-4"
                  >
                    <div class="mb-4 flex items-center justify-between">
                      <Label class="text-base font-medium">
                        Subtitle #{{ index + 1 }}
                      </Label>
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        @click="removeSubtitle(index)"
                      >
                        Remove
                      </Button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                      <div class="grid gap-2">
                        <Label :for="`subtitle_language_${index}`"
                          >Language (Code)</Label
                        >
                        <Input
                          :id="`subtitle_language_${index}`"
                          v-model="subtitle.language"
                          placeholder="e.g. en, es"
                          maxlength="2"
                        />
                        <InputError
                          :message="
                            form.errors[
                              `subtitles.${index}.language` as keyof CreateVideoForm
                            ]
                          "
                        />
                      </div>

                      <div class="grid gap-2">
                        <Label :for="`subtitle_name_${index}`">Name</Label>
                        <Input
                          :id="`subtitle_name_${index}`"
                          v-model="subtitle.name"
                          placeholder="e.g. English, Spanish"
                        />
                        <InputError
                          :message="
                            form.errors[
                              `subtitles.${index}.name` as keyof CreateVideoForm
                            ]
                          "
                        />
                      </div>

                      <div class="grid gap-2">
                        <Label :for="`subtitle_file_${index}`">File</Label>
                        <Input
                          :id="`subtitle_file_${index}`"
                          type="file"
                          accept=".vtt,.srt"
                          @change="
                            (e: Event) => handleSubtitleFileChange(e, index)
                          "
                        />
                        <InputError
                          :message="
                            form.errors[
                              `subtitles.${index}.file` as keyof CreateVideoForm
                            ]
                          "
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <Button
                  type="button"
                  variant="outline"
                  class="w-full"
                  @click="addSubtitle"
                >
                  Add Subtitle
                </Button>
              </div>
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-4">
                  <Button :disabled="form.processing"> Create </Button>

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
                      Created.
                    </p>
                  </Transition>
                </div>
                <Button
                  type="button"
                  variant="outline"
                  @click="() => router.get('/admin/video')"
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
