<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/admin/settings/two-factor';
import { disable, enable } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, usePage } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { computed, onUnmounted, ref } from 'vue';

interface Props {
  requiresConfirmation?: boolean;
  twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
  requiresConfirmation: false,
  twoFactorEnabled: false,
});

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);

useSeoMeta({
  title: `Two-Factor Authentication - ${setting.value?.site_name || 'Forge Player'}`,
  description: 'Manage your two-factor authentication settings',
  keywords:
    setting.value?.site_keywords || 'Forge Player, StreamVibe, Live Streaming',
  robots: 'noindex, nofollow',
  ogType: 'website',
  ogSiteName: setting.value?.site_name || 'Forge Player',
  ogTitle: `Two-Factor Authentication - ${setting.value?.site_name || 'Forge Player'}`,
  ogDescription: 'Manage your two-factor authentication settings',
  ogImage: setting.value?.site_og_image || '/images/logo.png',
  twitterCard: 'summary_large_image',
  twitterTitle: `Two-Factor Authentication - ${setting.value?.site_name || 'Forge Player'}`,
  twitterDescription: 'Manage your two-factor authentication settings',
  twitterImage: setting.value?.site_twitter_image || '/images/logo.png',
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Two-Factor Authentication',
    href: show.url(),
  },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
  clearTwoFactorAuthData();
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="Two-Factor Authentication"
          description="Manage your two-factor authentication settings"
        />

        <div
          v-if="!twoFactorEnabled"
          class="flex flex-col items-start justify-start space-y-4"
        >
          <Badge variant="destructive">Disabled</Badge>

          <p class="text-muted-foreground">
            When you enable two-factor authentication, you will be prompted for
            a secure pin during login. This pin can be retrieved from a
            TOTP-supported application on your phone.
          </p>

          <div>
            <Button v-if="hasSetupData" @click="showSetupModal = true">
              <ShieldCheck />Continue Setup
            </Button>
            <Form
              v-else
              v-bind="enable.form()"
              @success="showSetupModal = true"
              #default="{ processing }"
            >
              <Button type="submit" :disabled="processing">
                <ShieldCheck />Enable 2FA</Button
              ></Form
            >
          </div>
        </div>

        <div v-else class="flex flex-col items-start justify-start space-y-4">
          <Badge variant="default">Enabled</Badge>

          <p class="text-muted-foreground">
            With two-factor authentication enabled, you will be prompted for a
            secure, random pin during login, which you can retrieve from the
            TOTP-supported application on your phone.
          </p>

          <TwoFactorRecoveryCodes />

          <div class="relative inline">
            <Form v-bind="disable.form()" #default="{ processing }">
              <Button
                variant="destructive"
                type="submit"
                :disabled="processing"
              >
                <ShieldBan />
                Disable 2FA
              </Button>
            </Form>
          </div>
        </div>

        <TwoFactorSetupModal
          v-model:isOpen="showSetupModal"
          :requiresConfirmation="requiresConfirmation"
          :twoFactorEnabled="twoFactorEnabled"
        />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
