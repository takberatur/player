<script setup lang="ts">
import { ClientDialogPlayer } from '@/components/client';
import { Badge } from '@/components/ui/badge';
import { Video } from '@/types';
import { DefaultExampleVideos } from '@/utils/default-example-player';
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
  videos?: Video[];
  host?: string;
}>();

const videosData = computed(() => props.videos || DefaultExampleVideos);
const videoToView = ref<Video | null>(null);
const openVIdeoPlayer = ref(false);

const handleVideoClick = (video?: Video) => {
  if (!video) return;
  videoToView.value = video;
  openVIdeoPlayer.value = true;
};
</script>

<template>
  <section id="example-player" class="py-16">
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div v-motion-slide-visible-bottom class="mb-12 text-center">
        <h2 class="mb-4 text-4xl font-bold md:text-5xl">
          Example Beautiful Player
        </h2>
        <p class="text-xl text-gray-300">
          Check out our beautiful player example
        </p>
      </div>

      <div
        id="seriesGrid"
        class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
      >
        <div
          v-for="(video, index) in videosData"
          :key="video.id || index"
          v-motion-pop-visible
          :delay="index * 100"
          class="group relative overflow-hidden rounded-xl bg-gray-800 shadow-lg transition-all duration-300 hover:shadow-purple-500/20"
        >
          <div :class="`relative aspect-video overflow-hidden bg-linear-to-br`">
            <img
              :src="video.poster"
              :alt="video.title"
              class="absolute inset-0 h-full w-full object-cover opacity-80 transition-opacity duration-300 group-hover:opacity-60"
            />
            <div
              class="absolute inset-0 bg-linear-to-t from-black/80 via-transparent to-transparent"
            ></div>

            <div
              class="absolute inset-0 z-30 flex items-center justify-center opacity-0 transition-all duration-300 group-hover:opacity-100"
            >
              <button
                type="button"
                class="flex h-16 w-16 cursor-pointer items-center justify-center rounded-full backdrop-blur-sm transition-all duration-300"
                @click="handleVideoClick(video)"
              >
                <Icon icon="el:play-alt" class="h-16 w-16 text-red-600" />
              </button>
            </div>

            <div class="absolute right-4 bottom-4 left-4">
              <h3 class="mb-1 line-clamp-1 text-lg font-bold">
                {{ video.title }}
              </h3>
              <Badge class="text-xs font-semibold uppercase">
                {{ video.type.replace('_', ' ').toLowerCase() }}
              </Badge>
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <span class="ml-2 text-sm font-semibold">{{
                    new Date(video.created_at).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                    })
                  }}</span>
                </div>
                <span class="text-xs text-gray-300"
                  >{{ video.views }} views</span
                >
              </div>
            </div>

            <div
              class="absolute inset-0 bg-linear-to-t from-black/90 via-transparent to-transparent opacity-0 transition-all duration-300 group-hover:opacity-100"
            >
              <div class="absolute right-4 bottom-4 left-4 z-30">
                <button
                  class="w-full transform cursor-pointer rounded-lg bg-linear-to-r from-purple-500 to-pink-500 py-1.5 text-sm font-semibold transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600"
                  @click="handleVideoClick(video)"
                >
                  Watch Now
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <ClientDialogPlayer
    v-model:open="openVIdeoPlayer"
    :video="videoToView"
    :host="props.host"
    @close="
      () => {
        videoToView = null;
        openVIdeoPlayer = false;
      }
    "
  />
</template>
