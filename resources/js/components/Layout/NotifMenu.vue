
<template>
  <div>
    <ABadge v-if="unreadNotifications > 0" :content="unreadNotifications" color="primary" variant="text" anchor="top left"
      :max="9" />
    <ABtn variant="outline" class="text-xs rounded-full" icon="i-bx-bell" icon-only>
      <AMenu class="w-48">
        <AList>
          <AListItem>
            <div class="text-primary"> Notifications </div>
          </AListItem>
          <div class="h-separator"> </div>
        </AList>
        <AList class="[--a-list-item-gap:1rem]">
          <router-link :to="notification.data.path" :key="notification.id" v-for="notification in latestNotifications"
            v-slot="{ href, route, navigate, isActive, isExactActive }" @click="show = !show">

            <AListItem :title="notification.data.title" :is-active="isActive" @click="navigate">
              <template #prepend>
                <div class="relative ml-2">
                  <Icon :icon="notification.data.icon" size="xl" color="primary" class="rounded-full" />
                </div>
              </template>
            </AListItem>

          </router-link>

        </AList>
      </AMenu>
    </ABtn>
  </div>
</template>

<script  setup>
import { useConfigStore } from '@/store/config'

const { latestNotifications, unreadNotifications } = useConfigStore()

</script>