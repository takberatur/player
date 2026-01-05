<script setup lang="ts">
import { Icon } from '@iconify/vue';
</script>
<template>
  <div class="fixed top-0 left-0 z-9999 bg-black/50">
    <div
      class="flex h-screen w-screen flex-col items-center justify-center gap-2 overflow-hidden"
    >
      <div class="m-auto space-y-2">
        <Icon
          icon="eos-icons:bubble-loading"
          class="h-20 w-full text-center text-red-600"
        />
        <div class="loading-text">
          <span class="loading-text-words">L</span>
          <span class="loading-text-words">O</span>
          <span class="loading-text-words">A</span>
          <span class="loading-text-words">D</span>
          <span class="loading-text-words">I</span>
          <span class="loading-text-words">N</span>
          <span class="loading-text-words">G</span>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="css" scoped>
@reference "tailwindcss";

.loading-text {
  @apply flex items-center gap-2 text-center text-white;
  span {
    display: inline-block;
    font-family: 'Quattrocento Sans', sans-serif;
    &:nth-child(1) {
      filter: blur(0px);
      animation: blur-text 0.5s 0s infinite linear alternate;
    }
    &:nth-child(2) {
      filter: blur(0px);
      animation: blur-text 0.5s 0.2s infinite linear alternate;
    }
    &:nth-child(3) {
      filter: blur(0px);
      animation: blur-text 0.5s 0.4s infinite linear alternate;
    }
    &:nth-child(4) {
      filter: blur(0px);
      animation: blur-text 0.5s 0.6s infinite linear alternate;
    }
    &:nth-child(5) {
      filter: blur(0px);
      animation: blur-text 0.5s 0.8s infinite linear alternate;
    }
    &:nth-child(6) {
      filter: blur(0px);
      animation: blur-text 0.5s 1s infinite linear alternate;
    }
    &:nth-child(7) {
      filter: blur(0px);
      animation: blur-text 0.5s 1.2s infinite linear alternate;
    }
  }
}
@keyframes blur-text {
  0% {
    filter: blur(0px);
  }
  100% {
    filter: blur(4px);
  }
}
</style>
