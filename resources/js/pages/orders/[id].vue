<template>
  <VDialog :title="title" :subtitle="subtitle" v-model="show">
    <FieldsGenerator :resource-id="resourceId" :resource-name="resourceName" @submitted="saved" />
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

const route = useRoute()
const resourceId = Number(route.params.id)

const title = computed(() => props.title ?? `Update ${props.resourceName} Form`)
const subtitle = computed(() => props.subtitle ?? `Update ${props.resourceName} Form`)

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
  "name": "order-update",
  "meta": {
    "layout": "app",
    "requiresAuth": true,
    "intent": "update"
  }
}
</route>