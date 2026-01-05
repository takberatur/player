<script setup lang="ts">
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { dashboard } from '@/routes/admin';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const { scrollToAnchor } = useSmoothScroll();

const handleScroll = (e: Event, id: string) => {
  if (page.url === '/' || page.url.startsWith('/#')) {
    e.preventDefault();
    scrollToAnchor(id);
  }
};
</script>

<template>
  <section
    ref="sectionContainer"
    id="home"
    class="flex min-h-screen items-center pt-20 pb-16"
  >
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div class="mx-auto max-w-4xl text-center">
        <h1
          v-motion-slide-visible-bottom
          class="mb-6 text-5xl leading-tight font-bold md:text-7xl"
        >
          {{ setting?.site_name || 'Forge Player' }}
          <span
            class="bg-linear-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent"
          >
            Video Player Multi-Platform
          </span>
        </h1>
        <p
          v-motion-slide-visible-bottom
          :delay="200"
          class="mb-8 text-xl leading-relaxed text-gray-300 md:text-2xl"
        >
          {{ setting?.site_description }}
        </p>
        <div
          v-motion-pop-visible
          :delay="400"
          class="flex flex-col items-center justify-center gap-4 sm:flex-row"
        >
          <Link :href="dashboard()">
            <button
              class="inline-flex transform cursor-pointer items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-8 py-4 text-lg font-semibold shadow-2xl transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 hover:shadow-purple-500/25"
            >
              <Icon icon="icon-park-outline:play" class="mr-2" />
              Start Build Your Player
            </button>
          </Link>
          <a
            href="#example-player"
            @click="handleScroll($event, '#example-player')"
            class="transform rounded-full border-2 border-purple-400 px-8 py-4 text-lg font-semibold transition-all duration-300 hover:scale-105 hover:bg-purple-400/10"
          >
            See Example
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
