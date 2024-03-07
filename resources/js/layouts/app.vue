<template>
  <ALoader :loading="loading" v-if="loading" full-page />
  <div v-else class="flex w-full shrink-0">
    <Sidebar />

    <div class="flex flex-col w-full overflow-hidden">
      <Appbar />

      <main class="relative flex-1 focus:outline-none overflow-y-auto h-screen max-w-screen" id="main">

        <div class="p-8">
          <router-view v-slot="{ Component, route }">
            <transition name="fade">
              <component :is="Component" :key="route" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>
  </div>


  <!-- modal target -->
  <div id="modal"></div>
</template>

<script setup>

const loading = computed(() => !Application.booted.value)

</script>