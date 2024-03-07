<template>
  <div class="relative h-16 w-full flex bg-white shadow dark:bg-neutral-700">
    <div class="flex flex-1 justify-between pr-4 sm:pr-6 lg:pr-8">

      <div class="flex flex-1">
        <div class="mx-8 max-w-xs py-5 ">
          <h1 class="truncate font-semibold uppercase text-neutral-700 dark:text-neutral-100" v-text="title" />
        </div>
        <span class="v-separator  mx-2" />

        <div class="flex flex-1 mx-4">
          <div class=" flex items-center w-full">
            <VSearch class="" :min-length="1" />
          </div>
        </div>
      </div>

      <span class="v-separator mx-4" />

      <div class="flex items-center ">

        <!-- Notifications -->
        <div class="">
          <NotifMenu />
        </div>

        <span class="mx-1" />

        <!-- Quick create dropdown -->
        <div class="hidden md:block">
          <QuickCreate />
        </div>


        <span class="v-separator mx-4 hidden md:flex" />
        <div class=" hidden md:flex">
          <ThemeSwitch />

          <span class=" mx-2" />
          <!-- Teleport target -->
          <Transition name="fade">
            <div id="navbar-actions"></div>
          </Transition>
        </div>

        <!-- Profile dropdown -->
        <div class="ml-1 md:hidden lg:ml-3">
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useConfigStore } from '@/store/config'
import {
  isMacintosh,
  searchKeyboardShortcutMainKey,
  searchKeyboardShortcutKey,
  shouldUseSearchKeyboardShortcut
} from '@/utils/platform'


const route = useRoute()
const title = computed(() => route.name)
const search = ref('')



</script>