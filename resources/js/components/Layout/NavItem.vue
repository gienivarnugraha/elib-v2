<template>
  <AList class="[--a-list-item-gap:1rem]">
    <router-link :to="menu.route" :key="menu.id" v-slot="{ href, route, navigate, isActive, isExactActive }"
      @click="show = !show">

      <Transition name="slide-y">
        <AListItem :title="sideBar ? menu.name : ''" :is-active="isActive" @click="navigate"
          v-tooltip="sideBar ? '' : menu.name">
          <template #append v-if="menu.children">
            <AAvatar :icon="show ? 'i-bx-chevron-up' : 'i-bx-chevron-down'" class="text-base" />
          </template>
          <template #prepend v-else>
            <div class="relative ml-2">
              <ABadge v-if="menu.badge" :content="menu.badge" :color="menu.badgeVariant" anchor="top left" :max="9" />
              <Icon :icon="menu.icon" size="xl" color="primary" class="rounded-full" />
            </div>
          </template>
        </AListItem>
      </Transition>

      <Transition name="slide-y">
        <div class="pl-4" v-show="show">
          <slot></slot>
        </div>
      </Transition>

    </router-link>

  </AList>
</template>

<script setup>
import { useConfigStore } from '@/store/config'

const { sideBar } = storeToRefs(useConfigStore())

const props = defineProps({
  menu: Object
})

const show = ref(false)


</script>

<style></style>