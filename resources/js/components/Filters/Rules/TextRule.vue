<template>
  <div @click="emits('update-rules', filter.rules)">
    {{ filter.label }}
  </div>
</template>

<script setup>
const props = defineProps({
  filter: Object
})

const emits = defineEmits(['update-rules'])

</script>