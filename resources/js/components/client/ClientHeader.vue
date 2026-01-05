<script setup lang="ts">
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { login, register } from '@/routes';
import { dashboard } from '@/routes/admin';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    canRegister: boolean;
  }>(),
  {
    canRegister: true,
  },
);

const page = usePage<SharedData>();
const setting = computed(() => page.props.setting);
const { scrollToAnchor } = useSmoothScroll();
const isMobileMenuOpen = ref(false);

const handleScroll = (e: Event, id: string) => {
  if (page.url === '/' || page.url.startsWith('/#')) {
    e.preventDefault();
    scrollToAnchor(id);
  }
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const handleMobileNavClick = (e: Event, id: string) => {
  handleScroll(e, id);
  isMobileMenuOpen.value = false;
};

const logo = computed(
  () => setting.value?.site_logo || '/images/logo-small.png',
);
</script>
<template>
  <nav
    class="fixed z-50 w-full border-b border-white/10 bg-black/20 backdrop-blur-md"
  >
    <div class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <Link href="/" class="flex items-center space-x-2">
          <div class="flex h-10 w-10 items-center justify-center rounded-full">
            <img
              :src="logo"
              :alt="setting?.site_name || 'Forge Player'"
              class="h-10 w-10 rounded-full object-cover select-none"
            />
          </div>
          <span
            class="bg-linear-to-r from-purple-400 to-pink-400 bg-clip-text text-2xl font-bold text-transparent"
            >{{ setting?.site_name || 'Forge Player' }}</span
          >
        </Link>

        <div class="hidden items-center gap-8 md:flex">
          <a
            href="#home"
            class="cursor-pointer transition-colors hover:text-purple-400"
            @click="handleScroll($event, '#home')"
          >
            Home
          </a>
          <a
            href="#feature"
            class="cursor-pointer transition-colors hover:text-purple-400"
            @click="handleScroll($event, '#feature')"
          >
            Featured Sources
          </a>
          <a
            href="#benefits"
            class="cursor-pointer transition-colors hover:text-purple-400"
            @click="handleScroll($event, '#benefits')"
          >
            Benefits
          </a>
          <a
            href="#example-player"
            class="cursor-pointer transition-colors hover:text-purple-400"
            @click="handleScroll($event, '#example-player')"
          >
            Example Player
          </a>
          <Link
            v-if="setting?.enable_documentation"
            href="/documentation"
            class="cursor-pointer transition-colors hover:text-purple-400"
          >
            Documentation
          </Link>
        </div>

        <div class="flex items-center justify-center">
          <div
            v-if="page.props.auth.user"
            class="hidden items-center gap-4 md:flex"
          >
            <Link :href="dashboard()">
              <button
                class="transform cursor-pointer rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600"
              >
                Dashboard
              </button>
            </Link>
          </div>
          <div v-else class="hidden items-center gap-4 md:flex">
            <Link :href="login()">
              <button
                class="inline-flex transform cursor-pointer items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 md:text-sm"
              >
                <Icon icon="icon-park-outline:login" class="md:mr-2" />
                <span class="hidden md:inline-block">Sign In</span>
              </button>
            </Link>
            <Link v-if="setting?.enable_registration" :href="register()">
              <button
                v-if="props.canRegister"
                class="inline-flex transform cursor-pointer items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 md:text-sm"
              >
                <Icon icon="icon-park-outline:user" class="md:mr-2" />
                <span class="hidden md:inline-block">Sign Up</span>
              </button>
            </Link>
          </div>
          <button
            class="ml-2 cursor-pointer text-white transition-colors hover:text-purple-400 md:hidden"
            @click="toggleMobileMenu"
          >
            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
            <X v-else class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Menu Overlay -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-show="isMobileMenuOpen"
          class="absolute top-full left-0 h-screen w-full bg-black/70 backdrop-blur-md md:hidden"
        >
          <div class="flex flex-col items-center gap-8 pt-10">
            <a
              href="#home"
              class="text-xl font-medium text-white transition-colors hover:text-purple-400"
              @click="handleMobileNavClick($event, '#home')"
            >
              Home
            </a>
            <a
              href="#feature"
              class="text-xl font-medium text-white transition-colors hover:text-purple-400"
              @click="handleMobileNavClick($event, '#feature')"
            >
              Featured Sources
            </a>
            <a
              href="#example-player"
              class="text-xl font-medium text-white transition-colors hover:text-purple-400"
              @click="handleMobileNavClick($event, '#example-player')"
            >
              Example Player
            </a>
            <Link
              v-if="setting?.enable_documentation"
              href="/documentation"
              class="cursor-pointer text-xl font-medium text-white transition-colors hover:text-purple-400"
            >
              Documentation
            </Link>
            <div class="flex items-center justify-center">
              <div
                v-if="page.props.auth.user"
                class="flex items-center gap-4 md:hidden"
              >
                <Link :href="dashboard()">
                  <button
                    class="transform cursor-pointer rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600"
                  >
                    Dashboard
                  </button>
                </Link>
              </div>
              <div v-else class="flex items-center gap-4 md:hidden">
                <Link :href="login()">
                  <button
                    class="inline-flex transform cursor-pointer items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 md:text-sm"
                  >
                    <Icon icon="icon-park-outline:login" class="md:mr-2" />
                    <span class="inline-block md:hidden">Sign In</span>
                  </button>
                </Link>
                <Link v-if="setting?.enable_registration" :href="register()">
                  <button
                    v-if="props.canRegister"
                    class="inline-flex transform cursor-pointer items-center justify-center rounded-full bg-linear-to-r from-purple-500 to-pink-500 px-6 py-2 transition-all duration-300 hover:scale-105 hover:from-purple-600 hover:to-pink-600 md:text-sm"
                  >
                    <Icon icon="icon-park-outline:user" class="md:mr-2" />
                    <span class="inline-block md:hidden">Sign Up</span>
                  </button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </nav>
</template>
