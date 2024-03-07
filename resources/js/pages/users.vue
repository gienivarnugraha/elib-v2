<template>
  <div>
    <teleport v-if="isMounted" to="#navbar-actions">
      <ABtn icon="i-bx-plus" class="text-xs" @click="$router.push('/users/create')">New User</ABtn>
    </teleport>

    <!-- create and update routes component -->
    <router-view v-slot="{ Component, route }">
      <component :is="Component" :key="route" :resource-name="resource" />
    </router-view>
  </div>
</template>


<script setup>
import { useMounted } from '@vueuse/core'
const resource = 'users'
const isMounted = useMounted()
</script>


<route lang="json">
{
  "name": "users",
  "meta": {
    "layout": "app",
    "requiresAuth": true
  }
}
</route>