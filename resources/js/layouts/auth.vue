<template>
  <layout>
    <div class="
        min-h-screen
        bg-neutral-50
        dark:bg-neutral-900
        flex flex-col
        justify-center
        py-12
        sm:px-6
        lg:px-8
        ">
      <router-view v-slot="{ Component, route }">
        <transition name="slide">
          <component :is="Component" :key="route" />
        </transition>
      </router-view>
    </div>
  </layout>
</template>

<script setup>

</script>