<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
  menuItems: any[];
  expandedSections: Record<string, boolean>;
  activeSection: string;
}>();

const emit = defineEmits(['toggle-section', 'select-menu-item']);

const onToggleSection = (id: string) => {
  emit('toggle-section', id);
};

const onSelectMenuItem = (id: string) => {
  emit('select-menu-item', id);
};
</script>
<template>
  <aside
    class="sticky top-16 hidden h-[calc(100vh-4rem)] w-72 border-r lg:block"
  >
    <ScrollArea class="h-full">
      <nav class="space-y-1 p-4">
        <div v-for="item in menuItems" :key="item.id">
          <template v-if="item.hasSubmenu">
            <Collapsible
              :open="expandedSections[item.id]"
              @update:open="onToggleSection(item.id)"
            >
              <CollapsibleTrigger as-child>
                <Button
                  variant="ghost"
                  class="w-full justify-between hover:bg-accent"
                >
                  <div class="flex items-center gap-3">
                    <component :is="item.icon" class="h-4 w-4" />
                    <span class="text-sm font-medium">{{ item.title }}</span>
                  </div>
                  <component
                    :is="expandedSections[item.id] ? ChevronDown : ChevronRight"
                    class="h-4 w-4"
                  />
                </Button>
              </CollapsibleTrigger>
              <CollapsibleContent
                class="mt-1 ml-4 space-y-1 border-l border-border pl-4"
              >
                <Button
                  v-for="subItem in item.submenu"
                  :key="subItem.id"
                  :variant="activeSection === subItem.id ? 'default' : 'ghost'"
                  class="w-full justify-start text-sm"
                  @click="onSelectMenuItem(subItem.id)"
                >
                  <component :is="subItem.icon" class="h-4 w-4" />
                  {{ subItem.title }}
                </Button>
              </CollapsibleContent>
            </Collapsible>
          </template>
          <template v-else>
            <Button
              :variant="activeSection === item.id ? 'default' : 'ghost'"
              class="w-full justify-start"
              @click="onSelectMenuItem(item.id)"
            >
              <component :is="item.icon" class="mr-1 h-4 w-4" />
              <span class="text-sm font-medium">{{ item.title }}</span>
            </Button>
          </template>
        </div>
      </nav>
    </ScrollArea>
  </aside>
</template>
