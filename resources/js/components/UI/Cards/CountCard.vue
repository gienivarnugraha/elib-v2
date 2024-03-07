<template>
  <ACard class="w-full" :title="card.name" :subtitle="card.description">
    <div class="a-card-body">
      <div class="flex gap-x-3 items-center">
        <AAvatar icon="i-bx-trending-up" color="success" class="rounded-lg text-[1.25rem]" />

        <ATypography :title="[card.data, 'text-xl font-semibold !text-high-emphasis']" subtitle="Total">
        </ATypography>
      </div>
    </div>
  </ACard>
</template>

<script setup>
const props = defineProps({
  card: Object
})



</script>