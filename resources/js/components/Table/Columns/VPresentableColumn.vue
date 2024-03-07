<template>
  <router-link class="text-primary " :to="to"> {{ display }} </router-link>
</template>

<script setup>
const props = defineProps({
  col: Object,
  row: Object,
})

const to = props.col.relationField ? props.row[props.col.title]['path'] : props.row['path']
const display = props.col.relationField ? props.row[props.col.title]['display_name'] : props.row['display_name']
</script>