<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed } from 'vue';

defineProps<{
  status?: string;
}>();

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Verify Email - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Enter your email below to verify your email address',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Verify Email - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Enter your email below to verify your email address',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Verify Email - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Enter your email below to verify your email address',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});
</script>

<template>
  <AuthLayout
    title="Verify email"
    description="Please verify your email address by clicking on the link we just emailed to you."
  >
    <Head title="Email verification" />

    <div
      v-if="status === 'verification-link-sent'"
      class="mb-4 text-center text-sm font-medium text-green-600"
    >
      A new verification link has been sent to the email address you provided
      during registration.
    </div>

    <Form
      v-bind="send.form()"
      class="space-y-6 text-center"
      v-slot="{ processing }"
    >
      <Button :disabled="processing" variant="secondary">
        <Spinner v-if="processing" />
        Resend verification email
      </Button>

      <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
        Log out
      </TextLink>
    </Form>
  </AuthLayout>
</template>
