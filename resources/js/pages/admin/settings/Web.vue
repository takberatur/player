<script setup lang="ts">
import WebController from '@/actions/App/Http/Controllers/Admin/Settings/WebController';
import { UploadLogo } from '@/components/admin/setting';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
  TagsInput,
  TagsInputInput,
  TagsInputItem,
  TagsInputItemDelete,
  TagsInputItemText,
} from '@/components/ui/tags-input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import type { AcceptableInputValue } from 'reka-ui';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Web Settings - ${setting.value?.site_name || 'Forge Player'}`,
  description: "Update your site's web settings",
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Web Settings - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: "Update your site's web settings",
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Web Settings - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: "Update your site's web settings",
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const keywordInput = ref<string[]>(
  setting.value?.site_keywords
    ?.split(',')
    .map((s) => s.trim())
    .filter(Boolean) || [],
);
const siteLogo = ref<File | null>(null);
const siteFavicon = ref<File | null>(null);

const form = useForm<FormWebSetting>({
  enable_registration: setting.value?.enable_registration || false,
  enable_documentation: setting.value?.enable_documentation || false,
  site_name: setting.value?.site_name || '',
  site_description: setting.value?.site_description || '',
  site_tagline: setting.value?.site_tagline || '',
  site_keywords: setting.value?.site_keywords || '',
  site_og_image: setting.value?.site_og_image || '',
  site_og_description: setting.value?.site_og_description || '',
  site_og_title: setting.value?.site_og_title || '',
  site_twitter_image: setting.value?.site_twitter_image || '',
  site_twitter_description: setting.value?.site_twitter_description || '',
  site_twitter_title: setting.value?.site_twitter_title || '',
  site_email: setting.value?.site_email || '',
  site_logo: setting.value?.site_logo || null,
  site_favicon: setting.value?.site_favicon || null,
  site_phone: setting.value?.site_phone || '',
});

function handleKeywordInput(payload: AcceptableInputValue[]) {
  keywordInput.value = payload.map((item) => item.toString());
  form.site_keywords = siteKeywordsString.value;
}

const siteKeywordsString = computed(() =>
  keywordInput.value
    .map((s) => s.trim())
    .filter(Boolean)
    .join(','),
);

function handleUpload(file: File | null, type: 'logo' | 'favicon') {
  if (type === 'logo') {
    siteLogo.value = file;
    form.site_logo = file;
  } else if (type === 'favicon') {
    siteFavicon.value = file;
    form.site_favicon = file;
  }
}

const submit = () => {
  const route = WebController.update();

  form
    .transform((data) => ({
      ...data,
      site_logo: data.site_logo instanceof File ? data.site_logo : undefined,
      site_favicon:
        data.site_favicon instanceof File ? data.site_favicon : undefined,
    }))
    .post(route.url, {
      preserveScroll: true,
    });
};

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Web settings',
    href: WebController.index().url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <HeadingSmall
          title="Web settings"
          description="Update your account's web settings"
        />
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid gap-2">
            <div class="flex items-start gap-3">
              <Switch
                id="enable_registration"
                v-model="form.enable_registration"
                name="enable_registration"
                @update:model-value="
                  (checked) => {
                    form.enable_registration = checked;
                  }
                "
              />
              <div class="grid gap-2">
                <Label for="enable_registration">Enable registration</Label>
                <p class="text-sm text-muted-foreground">
                  This is enable or disable user registration route path.
                </p>
              </div>
            </div>
            <InputError
              class="mt-2"
              :message="form.errors.enable_registration"
            />
          </div>
          <div class="grid gap-2">
            <div class="flex items-start gap-3">
              <Switch
                id="enable_documentation"
                v-model="form.enable_documentation"
                name="enable_documentation"
                @update:model-value="
                  (checked) => {
                    form.enable_documentation = checked;
                  }
                "
              />
              <div class="grid gap-2">
                <Label for="enable_documentation">Enable documentation</Label>
                <p class="text-sm text-muted-foreground">
                  This is the documentation page for your site.
                </p>
              </div>
            </div>
            <InputError
              class="mt-2"
              :message="form.errors.enable_documentation"
            />
          </div>
          <div class="grid gap-2">
            <Label for="site_name">Site name</Label>
            <Input
              id="site_name"
              v-model="form.site_name"
              class="mt-1 block w-full"
              name="site_name"
              required
              autocomplete="name"
              placeholder="Site name"
            />
            <InputError class="mt-2" :message="form.errors.site_name" />
          </div>
          <div class="grid gap-2">
            <Label for="site_tagline">Site tagline</Label>
            <Input
              id="site_tagline"
              v-model="form.site_tagline"
              class="mt-1 block w-full"
              name="site_tagline"
              placeholder="Site tagline"
            />
            <InputError class="mt-2" :message="form.errors.site_tagline" />
          </div>
          <div class="grid gap-2">
            <Label for="site_description">Site description</Label>
            <Textarea
              id="site_description"
              v-model="form.site_description"
              class="mt-1 block w-full"
              name="site_description"
              required
              autocomplete="on"
              placeholder="Site description"
            />
            <InputError class="mt-2" :message="form.errors.site_description" />
          </div>
          <div class="grid gap-2">
            <Label for="site_keywords">Site keywords</Label>
            <TagsInput
              v-model="keywordInput"
              id="site_keywords"
              name="site_keywords"
              class="w-full"
              @update:model-value="handleKeywordInput"
            >
              <TagsInputItem
                v-for="item in keywordInput"
                :key="item"
                :value="item"
              >
                <TagsInputItemText />
                <TagsInputItemDelete />
              </TagsInputItem>
              <TagsInputInput placeholder="Keywords..." />
            </TagsInput>
            <input
              type="hidden"
              name="site_keywords"
              :value="siteKeywordsString"
            />
            <InputError class="mt-2" :message="form.errors.site_keywords" />
          </div>
          <div class="grid gap-2">
            <Label for="site_og_title">Site OG title</Label>
            <Input
              id="site_og_title"
              v-model="form.site_og_title"
              class="mt-1 block w-full"
              name="site_og_title"
              required
              autocomplete="name"
              placeholder="Site OG title"
            />
            <InputError class="mt-2" :message="form.errors.site_og_title" />
          </div>
          <div class="grid gap-2">
            <Label for="site_og_description">Site OG description</Label>
            <Textarea
              id="site_og_description"
              v-model="form.site_og_description"
              class="mt-1 block w-full"
              name="site_og_description"
              required
              autocomplete="on"
              placeholder="Site OG description"
            />
            <InputError
              class="mt-2"
              :message="form.errors.site_og_description"
            />
          </div>
          <div class="grid gap-2">
            <Label for="site_twitter_title">Site Twitter title</Label>
            <Input
              id="site_twitter_title"
              v-model="form.site_twitter_title"
              class="mt-1 block w-full"
              name="site_twitter_title"
              required
              autocomplete="name"
              placeholder="Site Twitter title"
            />
            <InputError
              class="mt-2"
              :message="form.errors.site_twitter_title"
            />
          </div>
          <div class="grid gap-2">
            <Label for="site_twitter_description"
              >Site Twitter description</Label
            >
            <Textarea
              id="site_twitter_description"
              v-model="form.site_twitter_description"
              class="mt-1 block w-full"
              name="site_twitter_description"
              required
              autocomplete="on"
              placeholder="Site Twitter description"
            />
            <InputError
              class="mt-2"
              :message="form.errors.site_twitter_description"
            />
          </div>
          <div class="grid gap-2">
            <Label for="site_email">Site email</Label>
            <Input
              id="site_email"
              v-model="form.site_email"
              class="mt-1 block w-full"
              name="site_email"
              type="email"
              :default-value="setting?.site_email || ''"
              autocomplete="email"
              placeholder="Site email"
            />
            <InputError class="mt-2" :message="form.errors.site_email" />
          </div>
          <div class="grid gap-2">
            <Label for="site_phone">Site phone</Label>
            <Input
              id="site_phone"
              v-model="form.site_phone"
              class="mt-1 block w-full"
              name="site_phone"
              type="tel"
              :default-value="setting?.site_phone || ''"
              autocomplete="tel"
              placeholder="Site phone"
            />
            <InputError class="mt-2" :message="form.errors.site_phone" />
          </div>
          <UploadLogo
            id="site_logo"
            label="Site logo"
            name="site_logo"
            :model-value="siteLogo"
            :error="form.errors.site_logo"
            type="logo"
            :setting="setting"
            @change="(file) => handleUpload(file, 'logo')"
          />
          <UploadLogo
            id="site_favicon"
            label="Site favicon"
            name="site_favicon"
            :model-value="siteFavicon"
            :error="form.errors.site_favicon"
            type="favicon"
            :setting="setting"
            @change="(file) => handleUpload(file, 'favicon')"
          />
          <div class="flex items-center gap-4">
            <Button
              :disabled="form.processing"
              data-test="update-profile-button"
            >
              Update
            </Button>

            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p
                v-show="form.recentlySuccessful"
                class="text-sm text-neutral-600"
              >
                Updated.
              </p>
            </Transition>
          </div>
        </form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
