<template>
  <VTable :resource-name="resourceName" />
</template>


<script setup>
const props = defineProps({
  resourceName: String,
})

</script>


<route lang="json">
{
  "name": "documents",
  "meta": {
    "layout": "app",
    "requiresAuth": true
  }
}
</route>