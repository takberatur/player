<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Register - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Enter your email and password below to register',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'index, follow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Register - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Enter your email and password below to register',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Register - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Enter your email and password below to register',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});
</script>

<template>
  <AuthBase
    title="Create an account"
    description="Enter your details below to create your account"
  >
    <Form
      v-bind="store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="name">Name</Label>
          <Input
            id="name"
            type="text"
            required
            autofocus
            :tabindex="1"
            autocomplete="name"
            name="name"
            placeholder="Full name"
          />
          <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="email">Email address</Label>
          <Input
            id="email"
            type="email"
            required
            :tabindex="2"
            autocomplete="email"
            name="email"
            placeholder="email@example.com"
          />
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            type="password"
            required
            :tabindex="3"
            autocomplete="new-password"
            name="password"
            placeholder="Password"
          />
          <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation">Confirm password</Label>
          <Input
            id="password_confirmation"
            type="password"
            required
            :tabindex="4"
            autocomplete="new-password"
            name="password_confirmation"
            placeholder="Confirm password"
          />
          <InputError :message="errors.password_confirmation" />
        </div>

        <Button
          type="submit"
          class="mt-2 w-full"
          tabindex="5"
          :disabled="processing"
          data-test="register-user-button"
        >
          <Spinner v-if="processing" />
          Create account
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Already have an account?
        <TextLink
          :href="login()"
          class="underline underline-offset-4"
          :tabindex="6"
          >Log in</TextLink
        >
      </div>
    </Form>
  </AuthBase>
</template>
