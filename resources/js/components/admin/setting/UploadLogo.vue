<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import UploadImage from '@/components/UploadImage.vue';
import { Label } from '@/components/ui/label';
import { Setting } from '@/types';
import { reactive } from 'vue';

const emits = defineEmits<{
  (e: 'change', value: File | null): void;
}>();
const props = defineProps<{
  id: string;
  label: string;
  name: string;
  modelValue: File | null;
  error?: string | null;
  type: 'logo' | 'favicon';
  setting?: Setting | null;
}>();

const UPLOAD_CONFIG = {
  maxSize: 2 * 1024 * 1024, // 2MB
  allowedMimes: ['image/jpeg', 'image/jpg', 'image/png'],
  defaultLogo: props.setting?.site_logo || '/images/logo.png',
  defaultFavicon: props.setting?.site_favicon || '/images/favicon.png',
};

const uploads = reactive({
  logo: {
    value: props.setting?.site_logo || '/images/logo.png',
    error: props.error || null,
  },
  favicon: {
    value: props.setting?.site_favicon || '/images/favicon.png',
    error: props.error || null,
  },
});
const validateFile = (file: File, type: 'logo' | 'favicon') => {
  if (file.size > UPLOAD_CONFIG.maxSize) {
    uploads[type].error = 'File size is too large, maximum 2MB Allowed';
    return false;
  }

  if (!UPLOAD_CONFIG.allowedMimes.includes(file.type)) {
    uploads[type].error = 'File type not allowed';
    return false;
  }

  uploads[type].error = null;
  return true;
};
const handleUpload = async (event: Event, type: 'logo' | 'favicon') => {
  const file = (event.target as HTMLInputElement)?.files?.[0];
  if (!file) return;

  if (!validateFile(file, type)) return;

  uploads[type].value = URL.createObjectURL(file);
  emits('change', file);
};
</script>

<template>
  <div class="grid gap-3">
    <Label>{{ props.label }}</Label>
    <UploadImage
      :id="props.id"
      v-model="uploads[props.type].value"
      :name="props.name"
      :error="uploads[props.type].error"
      @change="handleUpload($event, props.type)"
    />
    <InputError
      v-if="uploads[props.type].error"
      :message="uploads[props.type].error ?? undefined"
    />
  </div>
</template>
