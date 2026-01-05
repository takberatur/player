import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { createHead, renderSSRHead } from '@unhead/vue/server'
import ui from '@nuxt/ui/vue-plugin'
import Vue3Toastify, { type ToastContainerOptions } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { MotionPlugin } from '@vueuse/motion'


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
  (page) => {
    const head = createHead()
    return createInertiaApp({
      page,
      render: renderToString,
      title: (title) => (title ? `${title}` : appName),
      resolve: (name) =>
        resolvePageComponent(
          `./pages/${name}.vue`,
          import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
      setup: ({ App, props, plugin }) =>
        createSSRApp({ render: () => h(App, props) })
          .use(plugin)
          .use(head)
          .use(ui)
          .use(Vue3Toastify, {
            autoClose: 3000,
          } as ToastContainerOptions)
          .use(MotionPlugin),
    }).then(async (app) => {
      const payload = await renderSSRHead(head)
      app.head.push(payload.headTags)
      return app
    });
  },
  { cluster: true },
);
