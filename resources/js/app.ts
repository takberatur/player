import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { createHead, renderDOMHead } from '@unhead/vue/client';
import ui from '@nuxt/ui/vue-plugin'
import Vue3Toastify, { type ToastContainerOptions } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { MotionPlugin } from '@vueuse/motion'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => (title ? `${title}` : appName),
  resolve: (name) =>
    resolvePageComponent(
      `./pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    const head = createHead();
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(head)
      .use(ui)
      .use(Vue3Toastify, {
        autoClose: 3000,
      } as ToastContainerOptions)
      .use(MotionPlugin)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

// This will set light / dark mode on page load...
initializeTheme();
