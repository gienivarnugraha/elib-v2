<template>
  <a class="text-primary "> {{ user.name }}
    <ATooltip class="[&_.a-tooltip]-bg-primary">
      <div class="flex items-center gap-x-2 pa-1">
        <AAvatar :src="user.avatar_url"></AAvatar>
        <div class="flex flex-col text-left ">
          <span class="a-title">{{ user.name }}</span>
          <span class="a-subtitle">{{ user.org }}</span>
        </div>
      </div>
    </ATooltip>

  </a>
</template>

<script setup>
const props = defineProps({
  col: Object,
  row: Object,
})

const user = props.row[props.col.title]

</script>