<script setup lang="ts"></script>

<template>
  <section class="py-16">
    <div class="container mx-auto px-6 md:max-w-6xl">
      <div v-motion-fade-visible class="mx-auto max-w-3xl text-center">
        <h2
          v-motion-slide-visible-bottom
          class="mb-6 text-4xl font-bold md:text-6xl"
        >
          Start
          <span
            class="bg-linear-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent"
            >Your</span
          >
          Streaming Adventure
        </h2>
        <p
          v-motion-slide-visible-bottom
          :delay="200"
          class="mb-8 text-xl text-gray-300"
        >
          Get Unlimited Access and Build Your Favorite Video Player Now!
        </p>
        <a
          v-motion-pop-visible
          :delay="400"
          href="https://codecanyon.net/item/forge-player-liv/30333323"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-block"
        >
          <button
            class="transform cursor-pointer rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-10 py-4 text-xl font-semibold shadow-2xl transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 hover:shadow-purple-500/25"
          >
            Buy Now
          </button>
        </a>
      </div>
    </div>
  </section>
</template>
