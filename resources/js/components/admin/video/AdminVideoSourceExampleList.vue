<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Icon } from '@iconify/vue';
import { useVModel } from '@vueuse/core';
import { AlertCircleIcon } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const emits = defineEmits<{
  (e: 'update:open', open: boolean): void;
  (e: 'close'): void;
}>();
const props = defineProps<{
  open?: boolean;
}>();

const isOpen = useVModel(props, 'open', emits, {
  defaultValue: false,
  passive: true,
});

const copyToClipboard = (value: string) => {
  try {
    navigator.clipboard.writeText(value);
    toast.success('Copied to clipboard!');
  } catch (error) {
    toast.error(
      error instanceof Error ? error.message : 'Failed to copy to clipboard',
    );
  }
};
</script>

<template>
  <Button variant="destructive" @click="isOpen = true">
    Example Video Link
  </Button>
  <Dialog v-model:open="isOpen" @update:open="(open) => (isOpen = open)">
    <DialogContent
      class="mx-auto mt-4 flex min-h-screen w-full flex-col gap-10 py-12 lg:max-w-xl"
    >
      <DialogHeader>
        <DialogTitle> Example Support Links </DialogTitle>
        <DialogDescription>
          Please check the example video links below. >
        </DialogDescription>
      </DialogHeader>

      <div class="grid w-full gap-4">
        <Alert
          class="bg-yellow-600/20 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400"
        >
          <AlertCircleIcon />
          <AlertTitle>Warning!</AlertTitle>
          <AlertDescription
            class="text-sm text-yellow-800 dark:text-yellow-400"
          >
            Streaming video URLs are currently not supported. Please only use
            Video on Demand (VOD) URLs.
          </AlertDescription>
        </Alert>
        <ScrollArea
          class="h-[calc(100vh-350px)] w-full rounded-md border border-border px-2 py-4"
        >
          <div class="grid w-full gap-6">
            <div class="grid gap-2">
              <Label for="m3u8-url">
                M3u8/ HLS/ TS Files
                <span class="text-blue-500 dark:text-blue-400">
                  (Multi Quality Selection)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="m3u8-url"
                  type="url"
                  default-value="https://bitmovin-a.akamaihd.net/content/MI20192708/master.m3u8"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://bitmovin-a.akamaihd.net/content/MI20192708/master.m3u8',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="mkv-url">
                MKV Files
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="mkv-url"
                  type="url"
                  default-value="https://cdn.fluidplayer.com/videos/valerian-720p.mkv"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://cdn.fluidplayer.com/videos/valerian-720p.mkv',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="mp4-url">
                MP4 Files
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="mp4-url"
                  type="url"
                  default-value="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="dash-url">
                MPEG Dash Files
                <span class="text-blue-500 dark:text-blue-400">
                  (Multi Quality Selection)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="dash-url"
                  type="url"
                  default-value="https://bitmovin-a.akamaihd.net/content/MI20192708/stream.mpd"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://bitmovin-a.akamaihd.net/content/MI20192708/stream.mpd',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="rumble-url">
                Rumble
                <span class="text-blue-500 dark:text-blue-400">
                  (360p, 480p, 720p, 1080p, 2K, 4K)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="rumble-url"
                  type="url"
                  default-value="https://rumble.com/v72ofes-squirrel.html"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard('https://rumble.com/v72ofes-squirrel.html')
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="facebook-url">
                Facebook
                <span class="text-blue-500 dark:text-blue-400">
                  (HD & SD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="facebook-url"
                  type="url"
                  default-value="https://www.facebook.com/mediacorp.re.dian/videos/1690901491715181"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://www.facebook.com/mediacorp.re.dian/videos/1690901491715181',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="yandex-url">
                Yandex Disk
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="yandex-url"
                  type="url"
                  default-value="https://yadi.sk/i/1D0h3rm5u7uYvg"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="copyToClipboard('https://yadi.sk/i/1D0h3rm5u7uYvg')"
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="google-photo-url">
                Google Photos
                <span class="text-blue-500 dark:text-blue-400">
                  (360p, 720p, 1080p)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="google-photo-url"
                  type="url"
                  default-value="https://photos.google.com/share/AF1QipOsLX0NINR6D1A0PicyoFe-ZgcjRBlkBHaIrObSV_1Lv_xfq249jA88V8CwEDVF_Q/photo/AF1QipPRmNCNws2-1YDZbMIXkWNkC0rAiHgtSrxejCph?key=UEd5WTg1Ykh6TEVfS0twQkdyRFVqYW4zanNfRkhn"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://photos.google.com/share/AF1QipOsLX0NINR6D1A0PicyoFe-ZgcjRBlkBHaIrObSV_1Lv_xfq249jA88V8CwEDVF_Q/photo/AF1QipPRmNCNws2-1YDZbMIXkWNkC0rAiHgtSrxejCph?key=UEd5WTg1Ykh6TEVfS0twQkdyRFVqYW4zanNfRkhn',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="google-drive-url">
                Google Drive
                <span class="text-blue-500 dark:text-blue-400">
                  (360p, 720p, 1080p)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="google-drive-url"
                  type="url"
                  default-value="https://drive.google.com/file/d/1rEXY-xMQuVzwtymUJ5SqJxRAaJX6zPSt/view"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://drive.google.com/file/d/1rEXY-xMQuVzwtymUJ5SqJxRAaJX6zPSt/view',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="youtube-url">
                Youtube
                <span class="text-blue-500 dark:text-blue-400">
                  (360p, 480p, 720p, 1080p, 2K, 4K)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="youtube-url"
                  type="url"
                  default-value="https://www.youtube.com/watch?v=BjkIOU5PhyQ"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://www.youtube.com/watch?v=BjkIOU5PhyQ',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="dropBox-url">
                DropBox
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="dropBox-url"
                  type="url"
                  default-value="https://www.dropbox.com/s/seap0x345nexifw/007.mp4"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://www.dropbox.com/s/seap0x345nexifw/007.mp4',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="archive-url">
                Internet Archive
                <span class="text-blue-500 dark:text-blue-400">
                  (360p, 480p, 720p, 1080p, 2K, 4K)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="archive-url"
                  type="url"
                  default-value="https://archive.org/details/thor-arrives-in-wakanda"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://archive.org/details/thor-arrives-in-wakanda',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="amazon-url">
                Amazon Drive
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="amazon-url"
                  type="url"
                  default-value="https://www.amazon.com/clouddrive/share/ljr2Tl6Fv3UdezkJbUMBO4KweJYh5ESF700b9kyZbH7"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://www.amazon.com/clouddrive/share/ljr2Tl6Fv3UdezkJbUMBO4KweJYh5ESF700b9kyZbH7',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
            <div class="grid gap-2">
              <Label for="mega-url">
                Mega Disk
                <span class="text-blue-500 dark:text-blue-400">
                  (Quality Automatic HD)
                </span>
              </Label>
              <div class="flex items-center">
                <Input
                  id="mega-url"
                  type="url"
                  default-value="https://mega.nz/file/P1RnTQCA#jWw51lajO6Yqrutwq9YkeuWGfHSG_y9eLwvpyxd9nGE"
                  readonly
                />
                <Button
                  type="button"
                  variant="default"
                  size="sm"
                  @click="
                    copyToClipboard(
                      'https://mega.nz/file/P1RnTQCA#jWw51lajO6Yqrutwq9YkeuWGfHSG_y9eLwvpyxd9nGE',
                    )
                  "
                >
                  <Icon icon="material-symbols:content-copy" />
                  Copy
                </Button>
              </div>
            </div>
          </div>
        </ScrollArea>
      </div>
      <DialogFooter>
        <DialogClose as-child>
          <Button type="button" variant="destructive" @click="isOpen = false">
            Close
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
