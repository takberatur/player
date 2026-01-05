<script setup lang="ts">
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();
const { scrollToAnchor } = useSmoothScroll();
const setting = computed(() => page.props.setting);

const logo = computed(
  () => setting.value?.site_logo || '/images/logo-small.png',
);

const handleScroll = (e: Event, id: string) => {
  if (page.url === '/' || page.url.startsWith('/#')) {
    e.preventDefault();
    scrollToAnchor(id);
  }
};

const featuredSources = [
  {
    title: 'Google Drive',
    author: 'Google',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Google_Drive_icon_%282020%29.svg/2295px-Google_Drive_icon_%282020%29.svg.png',
    site: 'https://drive.google.com',
    type: 'Partial Free',
    gradient: 'from-blue-600 to-cyan-600',
  },
  {
    title: 'Google Photos',
    author: 'Google',
    image:
      'https://static.vecteezy.com/system/resources/thumbnails/022/484/502/small_2x/google-photo-icon-logo-symbol-free-png.png',
    site: 'https://photos.google.com',
    type: 'Partial Free',
    gradient: 'from-red-600 to-orange-600',
  },
  {
    title: 'Youtube',
    author: 'Google',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/e/ef/Youtube_logo.png',
    site: 'https://youtube.com',
    type: 'Free',
    gradient: 'from-purple-600 to-pink-600',
  },
  {
    title: 'Yandex Disk',
    author: 'Yandex',
    image: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/YandexDisk.png',
    site: 'https://360.yandex.com/disk/',
    type: 'Partial Free',
    gradient: 'from-gray-700 to-black',
  },
  {
    title: 'Amazon Drive',
    author: 'Amazon',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/5/57/Amazon_Drive_icon.png',
    site: 'https://www.amazon.com/',
    type: 'Paid',
    gradient: 'from-indigo-600 to-purple-600',
  },
  {
    title: 'Rumble',
    author: 'Rumble',
    image: 'https://companieslogo.com/img/orig/RUM-79ca46cb.png?t=1720244493',
    site: 'https://rumble.com',
    type: 'Free',
    gradient: 'from-red-500 to-blue-500',
  },
  {
    title: 'Dropbox',
    author: 'Dropbox',
    image:
      'https://cdn.freebiesupply.com/logos/large/2x/dropbox-1-logo-png-transparent.png',
    site: 'https://www.dropbox.com',
    type: 'Partial Free',
    gradient: 'from-yellow-600 to-orange-600',
  },
  {
    title: 'Facebook',
    author: 'Meta',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/6/6c/Facebook_Logo_2023.png',
    site: 'https://www.facebook.com',
    type: 'Free',
    gradient: 'from-blue-600 to-cyan-600',
  },
  {
    title: 'Archive',
    author: 'archive',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/9/93/Internet_Archive_logotype.png',
    site: 'https://archive.org',
    type: 'Free',
    gradient: 'from-neutral-600 to-neutral-500',
  },
  {
    title: 'Mega',
    author: 'Mega',
    image:
      'https://cdn.freebiesupply.com/logos/large/2x/mega-icon-logo-png-transparent.png',
    site: 'https://mega.io',
    type: 'Partial Free',
    gradient: 'from-red-600 to-orange-600',
  },
  {
    title: 'M3U8',
    author: 'M3U8',
    image: 'https://cdn-icons-png.flaticon.com/256/9404/9404997.png',
    site: 'https://en.wikipedia.org/wiki/M3U8',
    type: 'Free',
    gradient: 'from-sky-600 to-blue-600',
  },
  {
    title: 'MP4',
    author: 'MP4',
    image: 'https://cdn-icons-png.freepik.com/512/8300/8300515.png',
    site: 'https://en.wikipedia.org/wiki/MP4',
    type: 'Free',
    gradient: 'from-amber-600 to-yellow-600',
  },
  {
    title: 'MKV',
    author: 'MKV',
    image: 'https://cdn-icons-png.freepik.com/512/8243/8243009.png',
    site: 'https://en.wikipedia.org/wiki/MKV',
    type: 'Free',
    gradient: 'from-fuchsia-600 to-pink-600',
  },
  {
    title: 'TS',
    author: 'TS',
    image: 'https://cdn-icons-png.flaticon.com/256/9078/9078069.png',
    site: 'https://en.wikipedia.org/wiki/TS',
    type: 'Free',
    gradient: 'from-fuchsia-600 to-pink-600',
  },
  {
    title: 'MPD',
    author: 'MPD',
    image:
      'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/MpD_Logo.svg/500px-MpD_Logo.svg.png',
    site: 'https://en.wikipedia.org/wiki/MPD',
    type: 'Free',
    gradient: 'from-purple-600 to-sky-600',
  },
];
</script>
<template>
  <footer class="border-t border-white/10 bg-black/40 py-12 backdrop-blur-sm">
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
        <div>
          <Link href="/" class="mb-6 flex items-center space-x-2">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full"
            >
              <img
                :src="logo"
                :alt="setting?.site_name || 'Forge Player'"
                class="h-10 w-10 rounded-full object-cover"
              />
            </div>
            <span
              class="bg-linear-to-r from-purple-400 to-pink-400 bg-clip-text text-2xl font-bold text-transparent"
              >{{ setting?.site_name || 'Forge Player' }}</span
            >
          </Link>
          <p class="mb-4 text-gray-400">
            {{ setting?.site_description }}
          </p>
          <div class="flex space-x-4">
            <a
              href="https://www.facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-purple-500/20 transition-all hover:bg-purple-500/40"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
              >
                <path
                  fill="currentColor"
                  d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95"
                />
              </svg>
            </a>
            <a
              href="https://twitter.com"
              target="_blank"
              rel="noopener noreferrer"
              class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-purple-500/20 transition-all hover:bg-purple-500/40"
            >
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"
                />
              </svg>
            </a>
            <a
              href="https://www.instagram.com"
              target="_blank"
              rel="noopener noreferrer"
              class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-purple-500/20 transition-all hover:bg-purple-500/40"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
              >
                <path
                  fill="currentColor"
                  d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4zm9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8A1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5a5 5 0 0 1-5 5a5 5 0 0 1-5-5a5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3"
                />
              </svg>
            </a>
          </div>
        </div>

        <div>
          <h3 class="mb-4 text-lg font-semibold">Featured Sources</h3>
          <ul class="space-y-2 text-gray-400">
            <li v-for="source in featuredSources" :key="source.title">
              <a
                :href="source.site"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-1 transition-colors hover:text-white"
              >
                <img
                  :src="source.image"
                  :alt="source.title"
                  class="user-select-none h-5 w-5 rounded-full object-cover select-none"
                />
                {{ source.title }}
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="mb-4 text-lg font-semibold">Quick Links</h3>
          <ul class="space-y-2 text-gray-400">
            <li>
              <a
                href="#home"
                class="transition-colors hover:text-white"
                @click="handleScroll($event, '#home')"
                >Home</a
              >
            </li>
            <li>
              <a
                href="#feature"
                class="transition-colors hover:text-white"
                @click="handleScroll($event, '#feature')"
                >Featured Sources</a
              >
            </li>
            <li>
              <a
                href="#benefits"
                class="transition-colors hover:text-white"
                @click="handleScroll($event, '#benefits')"
                >Benefits</a
              >
            </li>
            <li>
              <a
                href="#example-player"
                class="transition-colors hover:text-white"
                @click="handleScroll($event, '#example-player')"
                >Example Player</a
              >
            </li>
            <li v-if="setting?.enable_documentation">
              <Link
                href="/documentation"
                class="transition-colors hover:text-white"
              >
                Documentation
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="mb-4 text-lg font-semibold">Get Started</h3>
          <a
            href="https://codecanyon.net/item/forge-player-liv/30333323"
            target="_blank"
            rel="noopener noreferrer"
          >
            <button
              class="transform cursor-pointer rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-5 py-2 text-sm font-semibold shadow-2xl transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 hover:shadow-purple-500/25"
            >
              Buy Now
            </button>
          </a>
        </div>
      </div>

      <div class="mt-8 border-t border-white/10 pt-8 text-center text-gray-400">
        <p>
          &copy; {{ new Date().getFullYear() }} {{ setting?.site_name }}. All
          rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>
