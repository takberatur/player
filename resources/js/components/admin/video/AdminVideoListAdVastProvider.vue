<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';

const providerItems = [
  {
    name: 'Google Ad Manager',
    logo: 'https://storage.googleapis.com/gweb-uniblog-publish-prod/images/logo_admanager_192px_MuAH4Zs.max-800x800.png',
    url: 'https://admanager.google.com/home/capabilities/video/',
  },
  {
    name: 'PubMatic',
    logo: 'https://cdn-1.webcatalog.io/catalog/pubmatic-publishers/pubmatic-publishers-icon-unplated.png?v=1714775572574',
    url: 'https://pubmatic.com/solutions/mobile/',
  },
  {
    name: 'InMobi',
    logo: 'https://www.pngkey.com/png/full/223-2234049_inmobi-logo-png.png',
    url: 'https://support.inmobi.com/monetize',
  },
  {
    name: 'Magnite',
    logo: 'https://images.seeklogo.com/logo-png/38/1/magnite-logo-png_seeklogo-389138.png',
    url: 'https://www.magnite.com/',
  },
  {
    name: 'HilltopAds',
    logo: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRToQAvkjCfHvfhGFrvKHDExKL9EmJp8R_fAw&s',
    url: 'https://hilltopads.com/',
  },
  {
    name: 'TripleLift',
    logo: 'https://mma.prnewswire.com/media/1476042/TripleLift_Logo.jpg',
    url: 'https://triplelift.com/',
  },
  {
    name: 'Broadstreet',
    logo: 'https://broadstreetads.com/wp-content/uploads/2017/07/square-high-res.png',
    url: 'https://broadstreetads.com/',
  },
  {
    name: 'VOD.AI',
    logo: 'https://assets.vdo.ai/images/global/logo/logoBlack.svg',
    url: 'https://vod.ai/',
  },
];
</script>

<template>
  <Sheet>
    <SheetTrigger as-child>
      <Button
        variant="ghost"
        class="bg-green-500 text-white hover:bg-green-700 hover:text-white dark:bg-green-600 dark:hover:bg-green-700 dark:hover:text-white"
      >
        Ad Vast Provider
      </Button>
    </SheetTrigger>
    <SheetContent class="flex w-full flex-col p-0 sm:max-w-md md:max-w-lg">
      <SheetHeader class="px-6 pt-6">
        <SheetTitle class="text-left">
          List Supported Ad Vast Provider</SheetTitle
        >
        <SheetDescription class="text-left">
          List of Ad Vast Providers That Support and Provide In-Video Ads for
          Video Monetization
        </SheetDescription>
      </SheetHeader>

      <ScrollArea class="h-[70%] flex-1 px-6 py-4">
        <div class="space-y-4 pr-4">
          <div
            v-for="item in providerItems"
            :key="item.name"
            class="flex items-center gap-3 rounded-md border border-neutral-300 p-3 dark:border-neutral-700"
          >
            <img
              :src="item.logo"
              alt="Ad Vast Provider Logo"
              class="h-10 w-10 shrink-0 rounded-md object-contain sm:h-12 sm:w-12"
            />
            <div class="min-w-0 flex-1 overflow-hidden">
              <p class="truncate text-sm font-medium sm:text-base">
                {{ item.name }}
              </p>
              <a
                :href="item.url"
                target="_blank"
                rel="noopener noreferrer"
                class="block truncate text-xs text-blue-600 hover:underline"
                :title="item.url"
              >
                {{ item.url }}
              </a>
            </div>
          </div>
        </div>
      </ScrollArea>

      <SheetFooter class="px-6 pt-4 pb-6">
        <SheetClose as-child>
          <Button variant="destructive" class="w-full sm:w-auto">
            Close
          </Button>
        </SheetClose>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
