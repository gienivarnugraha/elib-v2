
<template>
  <ABtn variant="fill" class="text-xs rounded-full" icon="i-bx-plus" icon-only>
    <AMenu class="w-48">
      <AList>
        <AListItem>
          <div class="text-primary"> Quick Create </div>
        </AListItem>
        <div class="h-separator"> </div>
      </AList>
      <AList class="[--a-list-item-gap:1rem]">
        <router-link :to="menu.quickCreateRoute" :key="menu.id" v-for="menu in quickCreateMenus"
          v-slot="{ href, route, navigate, isActive, isExactActive }" @click="show = !show">

          <AListItem :title="menu.quickCreateName" :is-active="isActive" @click="navigate">
            <template #prepend>
              <div class="relative ml-2">
                <Icon :icon="menu.icon" size="xl" color="primary" class="rounded-full" />
              </div>
            </template>
          </AListItem>

        </router-link>

      </AList>
    </AMenu>
  </ABtn>
</template>

<script  setup>
import { useConfigStore } from '@/store/config'

const { menus } = storeToRefs(useConfigStore())

const quickCreateMenus = menus.value.filter(menu => menu.inQuickCreate)

</script>