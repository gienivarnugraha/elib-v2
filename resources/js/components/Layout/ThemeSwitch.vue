<template>
  <ASwitch v-model="activeThemeName" v-tooltip="'Dark Theme'" on-value="dark" off-value="light" on-icon="i-bx-moon"
    off-icon="i-bx-sun" />
</template>

<script setup>

import { useAnu } from 'anu-vue';

const { activeThemeName } = useAnu()

onMounted(() => {
  if (!('theme' in localStorage)) {
    localStorage.theme = 'light';
  }

  activeThemeName.value = localStorage.theme
})

const unwatchTheme = watch(activeThemeName, (val) => localStorage.theme = val, { immediate: true })
</script>