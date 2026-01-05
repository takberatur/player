<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { analytics, dashboard } from '@/routes/admin';
import {
  create as customAdsCreate,
  index as customAdsIndex,
} from '@/routes/admin/custom-ads';
import { index as customAdsAnalyticsIndex } from '@/routes/admin/custom-ads/analytics';
import { edit } from '@/routes/admin/settings/profile';
import {
  create as videoCreate,
  index as videoIndex,
} from '@/routes/admin/video';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
  ChartArea,
  ChartColumnBig,
  EditIcon,
  Globe,
  HandCoins,
  LayoutGrid,
  Settings,
  VideoIcon,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid,
  },
  {
    title: 'Videos',
    href: '#',
    icon: VideoIcon,
    child: [
      {
        title: 'Create',
        href: videoCreate(),
        icon: EditIcon,
      },
      {
        title: 'Table',
        href: videoIndex(),
        icon: VideoIcon,
      },
      {
        title: 'Analytics',
        href: analytics(),
        icon: ChartArea,
      },
    ],
  },
  {
    title: 'Custom Ad Vast',
    href: '#',
    icon: HandCoins,
    child: [
      {
        title: 'Create',
        href: customAdsCreate(),
        icon: EditIcon,
      },
      {
        title: 'Table',
        href: customAdsIndex(),
        icon: HandCoins,
      },
      {
        title: 'Analytics',
        href: customAdsAnalyticsIndex(),
        icon: ChartColumnBig,
      },
    ],
  },
];

const footerNavItems: NavItem[] = [
  {
    title: 'Settings',
    href: edit(),
    icon: Settings,
  },
  {
    title: 'Home Page',
    href: '/',
    icon: Globe,
  },
];
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="dashboard()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
