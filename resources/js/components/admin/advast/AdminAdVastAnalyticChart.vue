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
import type { AdVastChartData } from '@/types';
import { VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

const props = defineProps<{
  charts?: AdVastChartData[];
  summary?: {
    total_impressions: number;
    total_clicks: number;
    total_starts: number;
    total_completes: number;
  };
  rangeDescription?: string;
}>();
const chartData = computed(() => {
  if (!props.charts) return [];

  return props.charts.map((item) => ({
    date: new Date(item.date).getTime(),
    total_impressions: item.total_impressions || 0,
    total_clicks: item.total_clicks || 0,
    total_starts: item.total_starts || 0,
    total_completes: item.total_completes || 0,
  }));
});

const description = 'An interactive area chart for Ad Vast Tag Analytics';
type Data = (typeof chartData.value)[number];
const chartConfig = {
  total_impressions: {
    label: 'Impressions',
    color: 'var(--chart-1)',
  },
  total_clicks: {
    label: 'Clicks',
    color: 'var(--chart-2)',
  },
  total_starts: {
    label: 'Starts',
    color: 'var(--chart-3)',
  },
  total_completes: {
    label: 'Completes',
    color: 'var(--chart-4)',
  },
} satisfies ChartConfig;

const filteredData = computed(() => {
  return chartData.value.filter(
    (d) =>
      !isNaN(d.date) &&
      !isNaN(d.total_impressions) &&
      !isNaN(d.total_clicks) &&
      !isNaN(d.total_starts) &&
      !isNaN(d.total_completes),
  );
});

const maxY = computed(() => {
  if (!filteredData.value.length) return 100;
  return (
    Math.max(
      ...filteredData.value.map((d) => d.total_impressions),
      ...filteredData.value.map((d) => d.total_clicks),
      ...filteredData.value.map((d) => d.total_starts),
      ...filteredData.value.map((d) => d.total_completes),
    ) * 1.2
  );
});
</script>

<template>
  <div class="space-y-6">
    <div v-if="summary" class="grid grid-cols-1 gap-4 md:grid-cols-4">
      <div class="rounded-lg border bg-blue-600 p-6">
        <div class="text-sm text-white">Total Impressions</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_impressions.toLocaleString() }}
        </div>
      </div>
      <div class="rounded-lg border bg-green-600 p-6">
        <div class="text-sm text-white">Total Clicks</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_clicks.toLocaleString() }}
        </div>
      </div>
      <div class="rounded-lg border bg-yellow-600 p-6">
        <div class="text-sm text-white">Total Starts</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_starts.toLocaleString() }}
        </div>
      </div>
      <div class="rounded-lg border bg-purple-600 p-6">
        <div class="text-sm text-white">Total Completes</div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ summary.total_completes.toLocaleString() }}
        </div>
      </div>
    </div>
    <div class="grid grid-cols-1 gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Ad Performance Overview</CardTitle>
          <CardDescription>
            {{ rangeDescription }}
          </CardDescription>
        </CardHeader>
        <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6">
          <ChartContainer
            :config="chartConfig"
            class="aspect-auto h-62.5 w-full"
          >
            <VisXYContainer
              :data="filteredData"
              :padding="{ top: 10, right: 10, bottom: 10, left: 10 }"
              :yDomain="[0, maxY]"
            >
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_impressions"
                :color="chartConfig.total_impressions.color"
                :strokeWidth="2"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_clicks"
                :color="chartConfig.total_clicks.color"
                :strokeWidth="2"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_starts"
                :color="chartConfig.total_starts.color"
                :strokeWidth="2"
              />
              <VisLine
                :x="(d: Data) => d.date"
                :y="(d: Data) => d.total_completes"
                :color="chartConfig.total_completes.color"
                :strokeWidth="2"
              />
              <VisAxis
                type="x"
                :tick-format="
                  (d: number) =>
                    new Date(d).toLocaleDateString('en-US', {
                      month: 'short',
                      day: 'numeric',
                    })
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
