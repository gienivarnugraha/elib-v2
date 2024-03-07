<template>
  <Transition name="slide-x">
    <div
      class="flex md:h-screen bg-neutral-800 dark:bg-neutral-900 border-neutral-200 dark:border-neutral-600 border-r md:shrink-0 ">
      <div class="flex flex-col !max-w-64 transition-width " :class="{
        'w-64': sideBar,
        'w-16': !sideBar
      }">
        <ACard class="shadow-none rounded-none h-screen">

          <div class="flex h-16 w-full pa-4 items-center" :class="sideBar ? 'justify-between' : 'justify-center'">
            <router-link class="whitespace-normal flex items-center" to="/" v-show="sideBar">
              <AAvatar :src="logo" class="h-8 w-8 mr-4" />
              <span class="font-bold text-neutral-700 dark:text-neutral-100">{{ settings.company_name }}</span>
            </router-link>

            <ABtn class="text-xs h-8 rounded-full tranistion duration-1000 ease-in" icon-only
              :icon="sideBar ? 'i-bx-chevron-left' : 'i-bx-menu'" variant="outline" @click="setSideBar(false)" />
          </div>


          <span class="h-separator my-1"></span>

          <UserMenu :class="sideBar ? 'mx-4' : 'px-1'" class="my-4" />

          <NavItem :menu="menu" v-for="menu in menus" :key="menu.id">
            <NavItem v-if="menu.children" :menu="child" v-for="child in menu.children" :key="child.id">
            </NavItem>
          </NavItem>
        </ACard>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useConfigStore } from '@/store/config'

import { useAnu } from 'anu-vue';

const { activeThemeName } = useAnu()

const { sideBar, menus, settings } = storeToRefs(useConfigStore())

const { setSideBar } = useConfigStore()

const props = defineProps({
  collapsible: Boolean,
})

const logo = computed(() => activeThemeName === 'dark' ? settings.value.logo_dark : settings.value.logo_light)
</script>