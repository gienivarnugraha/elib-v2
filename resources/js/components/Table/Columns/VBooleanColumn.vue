<template>
  <span class="a-table-td-text text-center" :class="row[col.title] ? 'text-green-300' : 'text-red-300'">
    <Icon :icon="row[col.title] ? col.meta.trueIcon : col.meta.falseIcon"></Icon>
  </span>
</template>

<script setup>
const props = defineProps({
  index: Number,
  col: Object,
  row: Object,
})


</script>