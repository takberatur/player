<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty';
import {
  Item,
  ItemActions,
  ItemContent,
  ItemDescription,
  ItemTitle,
} from '@/components/ui/item';
import { ScrollArea } from '@/components/ui/scroll-area';
import { create } from '@/routes/admin/custom-ads';
import type { CustomAdVastTag } from '@/types';
import { Link } from '@inertiajs/vue3';
import { FolderX } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  adVast?: CustomAdVastTag[];
}>();

const host = ref(window.location.origin);

const handleClickView = (adVast?: CustomAdVastTag) => {
  if (!adVast) return;

  window.location.href = `${host.value}/ads/vast/${adVast.id}.xml`;
};
</script>

<template>
  <Card class="w-full">
    <CardHeader>
      <CardTitle>Top Custom Ad Vast</CardTitle>
      <CardDescription> Top Custom Ad Vast Link </CardDescription>
    </CardHeader>
    <CardContent>
      <ScrollArea
        v-if="props.adVast && props.adVast?.length > 0"
        class="h-100 w-full rounded-lg border border-border"
      >
        <div class="grid grid-cols-1 gap-4">
          <Item v-for="item in props.adVast" :key="item.id" variant="outline">
            <ItemContent>
              <ItemTitle>{{ item.name }}</ItemTitle>
              <ItemDescription class="line-clamp-1">
                {{ `${host}/ads/vast/${item.id}.xml` }}
              </ItemDescription>
            </ItemContent>
            <ItemActions>
              <Button
                variant="outline"
                size="sm"
                @click="handleClickView(item)"
              >
                View
              </Button>
            </ItemActions>
          </Item>
        </div>
      </ScrollArea>
      <Empty v-else>
        <EmptyHeader>
          <EmptyMedia variant="icon">
            <FolderX />
          </EmptyMedia>
          <EmptyTitle>No Ad Vast Yet</EmptyTitle>
          <EmptyDescription>
            You haven't created any ad vast yet. Get started by creating your
            first ad vast.
          </EmptyDescription>
        </EmptyHeader>
        <EmptyContent>
          <div class="flex items-center justify-center">
            <Link :href="create().url">
              <Button>Create Ad Vast</Button>
            </Link>
          </div>
        </EmptyContent>
      </Empty>
    </CardContent>
  </Card>
</template>
