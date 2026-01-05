<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Confirm Password - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Enter your email and password below to confirm your password',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Confirm Password - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Enter your email and password below to confirm your password',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Confirm Password - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription:
    'Enter your email and password below to confirm your password',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});
</script>

<template>
  <AuthLayout
    title="Confirm your password"
    description="This is a secure area of the application. Please confirm your password before continuing."
  >
    <Form
      v-bind="store.form()"
      reset-on-success
      v-slot="{ errors, processing }"
    >
      <div class="space-y-6">
        <div class="grid gap-2">
          <Label htmlFor="password">Password</Label>
          <Input
            id="password"
            type="password"
            name="password"
            class="mt-1 block w-full"
            required
            autocomplete="current-password"
            autofocus
          />

          <InputError :message="errors.password" />
        </div>

        <div class="flex items-center">
          <Button
            class="w-full"
            :disabled="processing"
            data-test="confirm-password-button"
          >
            <Spinner v-if="processing" />
            Confirm Password
          </Button>
        </div>
      </div>
    </Form>
  </AuthLayout>
</template>
