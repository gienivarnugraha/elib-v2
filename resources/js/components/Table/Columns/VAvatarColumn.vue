<template>
  <AAvatar :src="row.avatar ?? row.avatar_url" />
</template>

<script setup>
const props = defineProps({
  col: Object,
  row: Object,
})


</script>