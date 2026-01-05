<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import { useAppearance } from '@/composables/useAppearance';
import type { Setting } from '@/types';
import { ChevronDown, ChevronRight, Menu, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { ClientDocumentationLogo } from '.';

const props = defineProps<{
  setting?: Setting | null;
  menuItems: any[];
  expandedSections: Record<string, boolean>;
  activeSection: string;
  mobileMenuOpen: boolean;
}>();

const emit = defineEmits([
  'update:mobileMenuOpen',
  'toggleSection',
  'selectMenuItem',
]);
const { appearance, updateAppearance } = useAppearance();

const isOpen = computed({
  get: () => props.mobileMenuOpen,
  set: (val) => emit('update:mobileMenuOpen', val),
});

const switchState = computed({
  get: () => appearance.value === 'dark',
  set: (val) => {
    updateAppearance(val ? 'dark' : 'light');
  },
});

const toggleSection = (id: string) => {
  emit('toggleSection', id);
};

const selectMenuItem = (id: string) => {
  emit('selectMenuItem', id);
};
</script>

<template>
  <header
    class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-backdrop-filter:bg-background/60"
  >
    <div class="container flex h-16 items-center justify-between px-4">
      <div class="flex items-center gap-4">
        <Sheet v-model:open="isOpen">
          <SheetTrigger as-child>
            <Button variant="ghost" size="icon" class="lg:hidden">
              <Menu class="h-5 w-5" />
            </Button>
          </SheetTrigger>
          <SheetContent side="left" class="w-72 p-0">
            <SheetHeader>
              <SheetTitle> </SheetTitle>
              <SheetDescription> </SheetDescription>
              <div class="flex items-center gap-2">
                <img
                  :src="setting?.site_logo || '/images/logo.png'"
                  :alt="setting?.site_name || 'Forge Player'"
                  class="h-8 w-8 rounded-md"
                />
                <div class="grid">
                  <h2 class="text-lg font-bold">
                    {{ setting?.site_name || 'Forge Player' }}
                  </h2>
                  <p class="text-sm text-muted-foreground">Documentation</p>
                </div>
              </div>
            </SheetHeader>

            <ScrollArea class="h-full">
              <nav class="space-y-1 p-4">
                <div v-for="item in menuItems" :key="item.id">
                  <template v-if="item.hasSubmenu">
                    <Collapsible
                      :open="expandedSections[item.id]"
                      @update:open="toggleSection(item.id)"
                    >
                      <CollapsibleTrigger as-child>
                        <Button
                          variant="ghost"
                          class="w-full justify-between hover:bg-accent"
                        >
                          <div class="flex items-center gap-3">
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="text-sm font-medium">{{
                              item.title
                            }}</span>
                          </div>
                          <component
                            :is="
                              expandedSections[item.id]
                                ? ChevronDown
                                : ChevronRight
                            "
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
                          :variant="
                            activeSection === subItem.id ? 'default' : 'ghost'
                          "
                          class="w-full justify-start text-sm"
                          @click="selectMenuItem(subItem.id)"
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
                      @click="selectMenuItem(item.id)"
                    >
                      <component :is="item.icon" class="mr-1 h-4 w-4" />
                      <span class="text-sm font-medium">{{ item.title }}</span>
                    </Button>
                  </template>
                </div>
              </nav>
            </ScrollArea>
          </SheetContent>
        </Sheet>

        <ClientDocumentationLogo :setting="setting" />
      </div>

      <div class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          class="bg-transparent outline-none hover:bg-transparent active:bg-transparent dark:bg-transparent dark:outline-none dark:hover:bg-transparent dark:active:bg-transparent"
          @click="switchState = !switchState"
        >
          <Sun v-if="switchState" class="flex h-3 w-3 text-yellow-400" />
          <Moon v-else class="flex h-3 w-3 text-sky-500" />
        </Button>
        <Badge class="text-xs">v1.0.0</Badge>
      </div>
    </div>
  </header>
</template>
