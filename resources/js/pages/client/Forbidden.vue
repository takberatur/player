<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useSeoMeta } from '@unhead/vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
  code?: string;
  error?: string;
  message?: string;
}>();

useSeoMeta({
  title: 'Forbidden',
  description: 'Access Forbidden',
  robots: 'noindex, nofollow',
});

const isVisible = ref(false);

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 100);
});
</script>

<template>
  <div
    class="relative flex min-h-screen items-center justify-center overflow-hidden bg-linear-to-br from-slate-900 via-purple-900 to-slate-900 p-4"
  >
    <div class="absolute inset-0 overflow-hidden">
      <div
        class="absolute -top-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-purple-500/10 blur-3xl"
      ></div>
      <div
        class="absolute -right-1/2 -bottom-1/2 h-full w-full animate-pulse rounded-full bg-blue-500/10 blur-3xl delay-1000"
      ></div>
    </div>

    <div
      class="relative z-10 w-full max-w-2xl transform transition-all duration-1000"
      :class="
        isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'
      "
    >
      <div
        class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl md:p-12"
      >
        <div class="mb-6 flex justify-center">
          <div class="relative">
            <div
              class="absolute inset-0 rounded-full bg-red-500/20 blur-2xl"
            ></div>
            <div
              class="relative flex h-20 w-20 rotate-12 transform items-center justify-center rounded-2xl bg-linear-to-br from-red-500 to-pink-600 transition-transform duration-300 hover:rotate-0 md:h-24 md:w-24"
            >
              <svg
                class="h-10 w-10 text-white md:h-12 md:w-12"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
            </div>
          </div>
        </div>

        <div class="mb-4 text-center">
          <h1
            class="mb-2 bg-linear-to-r from-red-400 via-pink-500 to-purple-500 bg-clip-text text-7xl font-black text-transparent md:text-8xl"
          >
            {{ code ?? '  403' }}
          </h1>
          <div
            class="mx-auto h-1 w-24 rounded-full bg-linear-to-r from-red-500 to-purple-500"
          ></div>
        </div>

        <div class="mb-8 space-y-4 text-center">
          <h2 class="text-2xl font-bold text-white md:text-3xl">
            {{ error ?? 'Access denied' }}
          </h2>
          <p class="text-lg text-gray-300">
            {{
              message ||
              'Sorry, You do not have permission to access this page.'
            }}
          </p>
        </div>

        <div class="flex flex-col justify-center gap-4 sm:flex-row">
          <Link
            href="/"
            class="group flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-linear-to-r from-purple-600 to-pink-600 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-purple-500/50"
          >
            <svg
              class="h-5 w-5 transition-transform group-hover:-translate-x-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Back to home
          </Link>

          <button
            @click="$inertia.visit('/')"
            class="cursor-pointer rounded-xl border border-white/20 bg-white/10 px-8 py-3 font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:bg-white/20"
          >
            Contact support
          </button>
        </div>

        <div class="mt-8 border-t border-white/10 pt-6">
          <p class="text-center text-sm text-gray-400">
            If you believe this is an error, please contact the system
            administrator.
          </p>
        </div>
      </div>

      <div
        class="animate-blob absolute top-1/2 left-0 -z-10 h-72 w-72 rounded-full bg-purple-500/20 opacity-70 blur-3xl filter"
      ></div>
      <div
        class="animate-blob animation-delay-2000 absolute top-1/2 right-0 -z-10 h-72 w-72 rounded-full bg-pink-500/20 opacity-70 blur-3xl filter"
      ></div>
    </div>
  </div>
</template>

<style scoped>
@keyframes blob {
  0%,
  100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
