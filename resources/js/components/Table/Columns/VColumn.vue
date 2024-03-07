<template>
  <span class="a-table-td-text" v-if="row[col.title]"> {{ col.formatter ? col.formatter(row) : row[col.name] }} </span>
</template>

<script setup>
const props = defineProps({
  index: Number,
  col: Object,
  row: Object,
})


</script>