<template>
  <VDialog :title="title" :subtitle="subtitle" v-model="show">
    <ResourceFieldsGenerator :resource-name="resourceName" @submitted="saved" />
  </VDialog>
</template>


<script setup>

import { findStore } from '@/store'

const props = defineProps({
  resourceName: String,
  title: String,
  subtitle: String
})

const store = findStore(props.resourceName)

const show = ref(false)

const title = computed(() => props.title ?? `Create ${props.resourceName} Form`)
const subtitle = computed(() => props.subtitle ?? `Create ${props.resourceName} Form`)

const saved = () => {
  store.table.needFetch = true
  show.value = false
}


onMounted(() => {
  show.value = true
})


</script>


<route lang="json">
{
  "name": "manual-create",
  "meta": {
    "layout": "app",
    "requiresAuth": true,
    "intent": "create"
  }
}
</route>