<template>
  <main class="relative flex-1 focus:outline-none overflow-y-auto h-screen max-w-screen" id="main">

    <router-view v-slot="{ Component, route }">
      <transition name="fade">
        <component :is="Component" :key="route" />
      </transition>
    </router-view>
  </main>

  <!-- modal target -->
  <!-- <div id="modal"></div> -->
</template>

<script setup>

</script>