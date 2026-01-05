<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  ChartTooltipContent,
  componentToString,
} from '@/components/ui/chart';
import type { ChartData } from '@/types';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

const props = defineProps<{
  charts?: ChartData[];
  summary?: {
    total_views: number;
    total_videos: number;
    unique_videos_viewed: number;
  };
  rangeDescription?: string;
}>();
const chartData = computed(() => {
  if (!props.charts) return [];

  return props.charts.map((item) => ({
    date: new Date(item.date).getTime(),
    total_views: item.total_views || 0,
    unique_videos_viewed: item.unique_videos_viewed || 0,
    new_videos: item.new_videos || 0,
  }));
});

const description = 'An interactive area chart';
type Data = (typeof chartData.value)[number];
const chartConfig = {
  total_views: {
    label: 'Total Views',
    color: 'var(--chart-1)',
  },
  unique_videos_viewed: {
    label: 'Unique Videos',
    color: 'var(--chart-2)',
  },
  new_videos: {
    label: 'New Videos',
    color: 'var(--chart-3)',
  },
} satisfies ChartConfig;

const filteredData = computed(() => {
  return chartData.value.filter(
    (d) =>
      !isNaN(d.date) &&
      !isNaN(d.total_views) &&
      !isNaN(d.unique_videos_viewed) &&
      !isNaN(d.new_videos),
  );
});

const maxY = computed(() => {
  if (!filteredData.value.length) return 100;
  return (
    Math.max(
      ...filteredData.value.map((d) => d.total_views),
      ...filteredData.value.map((d) => d.unique_videos_viewed),
      ...filteredData.value.map((d) => d.new_videos),
    ) * 1.2
  );
});
</script>
<template>
  <div class="space-y-6">
    <div v-if="summary" class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <div class="rounded-lg border bg-green-600 p-6">
        <div class="text-sm text-white">Total Views</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_views.toLocaleString() }}
        </div>
      </div>
      <div class="rounded-lg border bg-blue-600 p-6">
        <div class="text-sm text-white">Unique Videos Viewed</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.unique_videos_viewed.toLocaleString() }}
        </div>
      </div>
      <div class="rounded-lg border bg-yellow-600 p-6">
        <div class="text-sm text-white">New Videos Added</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_videos.toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="rounded-lg border bg-card p-6">
      <h3 class="mb-4 text-lg font-semibold">Views Analytics</h3>

      <Card class="@container/card">
        <CardHeader>
          <CardTitle>Total Visitors</CardTitle>
          <CardDescription>
            <span class="hidden @[540px]/card:block">
              {{ props.rangeDescription || 'Total for the last 3 months' }}
            </span>
          </CardDescription>
        </CardHeader>
        <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6">
          <ChartContainer
            :config="chartConfig"
            class="aspect-auto h-62.5 w-full"
            :cursor="false"
          >
            <VisXYContainer
              :data="filteredData"
              :padding="{ top: 10, right: 10, bottom: 10, left: 10 }"
              :yDomain="[0, maxY]"
            >
              <VisArea
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_views"
                color="url(#fillVideos)"
                :opacity="0.6"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_views"
                :color="chartConfig.total_views.color"
                :line-width="2"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.unique_videos_viewed"
                :color="chartConfig.unique_videos_viewed.color"
                :line-width="2"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.new_videos"
                :color="chartConfig.new_videos.color"
                :line-width="2"
              />
              <VisAxis
                type="x"
                :x="(d: Data) => d.date"
                :tick-line="false"
                :domain-line="false"
                :grid-line="false"
                :num-ticks="6"
                :tick-format="
                  (d: number) => {
                    const date = new Date(d);
                    return date.toLocaleDateString('en-US', {
                      month: 'short',
                      day: 'numeric',
                    });
                  }
                "
              />
              <VisAxis
                type="y"
                :num-ticks="3"
                :tick-line="false"
                :domain-line="false"
              />
              <ChartTooltip />
              <ChartCrosshair
                :template="
                  componentToString(chartConfig, ChartTooltipContent, {
                    labelFormatter: (d) => {
                      return new Date(d).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                      });
                    },
                  })
                "
              />
            </VisXYContainer>
          </ChartContainer>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
