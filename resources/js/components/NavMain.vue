<script setup lang="ts">
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  useSidebar,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
  items: NavItem[];
}>();

const page = usePage();
const { open, isMobile } = useSidebar();
const sidebarIndex = ref<number | null>(null);
const toggleSidebarItem = (id: number) => {
  sidebarIndex.value = sidebarIndex.value === id ? null : id;
};
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <template v-for="(menu, i) in items" :key="i">
      <SidebarMenu v-if="menu.child && menu.child.length > 0">
        <Collapsible
          :open="sidebarIndex === i"
          as-child
          class="group/collapsible"
        >
          <SidebarMenuItem>
            <CollapsibleTrigger as-child @click="toggleSidebarItem(i)">
              <SidebarMenuButton as-child :tooltip="menu.title">
                <div class="flex w-full items-center gap-2">
                  <component :is="menu.icon" />
                  <span
                    class="block"
                    :class="{
                      hidden: !open && !isMobile,
                      'ml-0': open || isMobile,
                    }"
                  >
                    {{ menu.title }}
                  </span>
                  <ChevronRight
                    v-if="open || isMobile"
                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                  />
                </div>
              </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
              <SidebarMenuSub>
                <SidebarMenuSubItem v-for="sub in menu.child" :key="sub.title">
                  <SidebarMenuSubButton
                    as-child
                    :is-active="urlIsActive(sub.href, page.url)"
                  >
                    <Link :href="sub.href">
                      <component :is="sub.icon" v-if="sub.icon" />
                      <span>{{ sub.title }}</span>
                    </Link>
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>
      </SidebarMenu>
      <SidebarMenu v-else>
        <SidebarMenuItem>
          <SidebarMenuButton
            as-child
            :is-active="urlIsActive(menu.href, page.url)"
            :tooltip="menu.title"
          >
            <Link :href="menu.href">
              <component :is="menu.icon" />
              <span>{{ menu.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </template>
  </SidebarGroup>
</template>
