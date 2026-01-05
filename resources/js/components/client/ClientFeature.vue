<script setup lang="ts">
const sourcesItems = [
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
  <section id="feature" class="rounded-lg bg-black/20 px-2 py-16">
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div v-motion-slide-visible-bottom class="mb-12 text-center">
        <h2 class="mb-4 text-4xl font-bold md:text-5xl">Featured Sources</h2>
        <p class="text-xl text-gray-300">
          The best sources that are currently supported by Forge Player.
        </p>
      </div>

      <div
        id="cardVideo"
        class="grid grid-cols-2 gap-6 p-3 md:grid-cols-3 lg:grid-cols-4"
      >
        <div
          v-for="(item, index) in sourcesItems"
          :key="index"
          v-motion-slide-visible-bottom
          :delay="index * 100"
          class="movie-card group relative transform overflow-hidden rounded-2xl bg-gray-800 shadow-2xl transition-all duration-500 hover:scale-105 hover:shadow-purple-500/20"
        >
          <div
            :class="`aspect-2/3 bg-linear-to-br ${item.gradient} relative overflow-hidden`"
          >
            <img
              :src="item.image"
              :alt="item.title"
              class="absolute inset-0 h-full w-full object-contain object-center opacity-80 transition-opacity duration-300 group-hover:opacity-60"
              loading="lazy"
            />
            <div
              class="absolute inset-0 bg-linear-to-t from-black/80 via-transparent to-transparent"
            ></div>

            <a
              :href="item.site"
              target="_blank"
              rel="noopener noreferrer"
              class="absolute inset-0 flex items-center justify-center opacity-0 transition-all duration-300 group-hover:opacity-100"
            >
              <div
                class="flex h-16 w-16 cursor-pointer items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition-all duration-300 hover:bg-purple-500/50"
              >
                <svg
                  class="ml-1 h-8 w-8"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path d="M8 5v14l11-7z" />
                </svg>
              </div>
            </a>
            <div class="absolute right-4 bottom-4 left-4">
              <h3 class="mb-1 line-clamp-1 text-lg font-bold">
                {{ item.title }}
              </h3>
              <p class="mb-2 text-sm text-gray-300">
                {{ item.type }} • {{ item.author }}
              </p>
            </div>
            <a
              :href="item.site"
              target="_blank"
              rel="noopener noreferrer"
              class="absolute inset-0 bg-linear-to-t from-black/90 via-transparent to-transparent opacity-0 transition-all duration-300 group-hover:opacity-100"
            >
              <div class="absolute right-4 bottom-4 left-4">
                <button
                  class="w-full transform cursor-pointer rounded-lg bg-linear-to-r from-purple-500 to-pink-500 py-3 font-semibold transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600"
                >
                  Visit Site
                </button>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
