
<template>
  <Iconify :icon="icon" :class="[`text-${size}`, `text-${color}-400 dark:text-${color}-500 icon`]" v-tooltip="helpText" />
</template>

<script setup>
import { Icon as Iconify } from '@iconify/vue';

const props = defineProps({
  icon: String,
  color: String,
  size: {
    type: String,
    default: 'base',
    validator: (val) => ['sm', 'base', 'lg', 'xl'].includes(val)
  },
  helpText: {
    type: [String, Boolean],
    default: false
  }
})

</script>