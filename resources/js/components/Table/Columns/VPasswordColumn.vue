<template>
  <span class="a-table-td-text text-center" v-if="row[col.title]">
    {{ password }}
    <i class="cursor-pointer" :class="show ? 'i-bx-show' : 'i-bx-hide'" @click="show = !show"></i>
  </span>
</template>

<script setup>
const props = defineProps({
  index: Number,
  col: Object,
  row: Object,
})

const value = props.row[props.col.title]

let show = ref(false)

const password = computed(() => show.value ? value : '*'.repeat(value.length))


</script>