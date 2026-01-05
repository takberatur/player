import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import ui from '@nuxt/ui/vite'
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig(({ command, mode, isSsrBuild }) => {
  return {
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './resources/js'),
      },
    },
    define: {
      'process.env': {},
    },
    plugins: [
      laravel({
        input: ['resources/js/app.ts'],
        ssr: 'resources/js/ssr.ts',
        refresh: true,
      }),
      tailwindcss(),
      !isSsrBuild && wayfinder({
        formVariants: true,
      }),
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
      ui({
        router: 'inertia'
      })
    ],
    build: {
      rollupOptions: {
        external: [
          'puppeteer',
          'puppeteer-core',
          'cheerio',
          'fluent-ffmpeg',
          'ytdl-core',
          '@distube/ytdl-core',
          'youtubei.js',
          'megajs',
          'ffmpeg-static'
        ]
      }
    }
  };
});

