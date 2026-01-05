<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
</script>
<template>
  <section id="benefits" class="bg-black/20 py-16">
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div v-motion-slide-visible-bottom class="mb-12 text-center">
        <h2 class="mb-4 text-4xl font-bold md:text-5xl">
          Why Choose {{ setting?.site_name }} ?
        </h2>
        <p class="text-xl text-gray-300">
          Experience streaming like never before with {{ setting?.site_name }}
        </p>
      </div>

      <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
        <div
          v-motion-slide-visible-left
          class="rounded-2xl border border-white/10 bg-linear-to-br from-purple-500/10 to-pink-500/10 p-8 text-center backdrop-blur-sm transition-all duration-300 hover:border-purple-500/50"
        >
          <div
            class="animate-float mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500"
          >
            <Icon icon="lucide:video" class="h-8 w-8 text-white" />
          </div>
          <h3 class="mb-4 text-2xl font-bold">Quality 4K Ultra HD</h3>
          <p class="text-gray-300">
            Enjoy the best viewing experience with 4K Ultra HD quality supported
            by the latest HDR technology.
          </p>
        </div>

        <div
          v-motion-slide-visible-bottom
          :delay="200"
          class="rounded-2xl border border-white/10 bg-linear-to-br from-purple-500/10 to-pink-500/10 p-8 text-center backdrop-blur-sm transition-all duration-300 hover:border-purple-500/50"
        >
          <div
            class="animate-float mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500"
            style="animation-delay: 1s"
          >
            <Icon icon="lineicons:phone" class="h-8 w-8 text-white" />
          </div>
          <h3 class="mb-4 text-2xl font-bold">Multi-Platform</h3>
          <p class="text-gray-300">
            Stream on TV, smartphone, tablet, or laptop. Seamless
            synchronization across all your devices.
          </p>
        </div>

        <div
          v-motion-slide-visible-right
          :delay="400"
          class="rounded-2xl border border-white/10 bg-linear-to-br from-purple-500/10 to-pink-500/10 p-8 text-center backdrop-blur-sm transition-all duration-300 hover:border-purple-500/50"
        >
          <div
            class="animate-float mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500"
            style="animation-delay: 2s"
          >
            <Icon icon="mdi:code" class="h-8 w-8 text-white" />
          </div>
          <h3 class="mb-4 text-2xl font-bold">TMDB Integration</h3>
          <p class="text-gray-300">
            Access detailed movie and TV show information from The Movie
            Database (TMDB).
          </p>
        </div>

        <div
          v-motion-slide-visible-right
          :delay="600"
          class="rounded-2xl border border-white/10 bg-linear-to-br from-purple-500/10 to-pink-500/10 p-8 text-center backdrop-blur-sm transition-all duration-300 hover:border-purple-500/50"
        >
          <div
            class="animate-float mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500"
            style="animation-delay: 2s"
          >
            <Icon
              icon="hugeicons:save-money-dollar"
              class="h-8 w-8 text-white"
            />
          </div>
          <h3 class="mb-4 text-2xl font-bold">Ad Monetization</h3>
          <p class="text-gray-300">
            Earn revenue by Monetize video with ads. Get paid for each view of
            your content.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
