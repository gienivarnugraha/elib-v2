<template>
  <div>
    <div class="grid-row grid-cols-2 md:grid-cols-4 mb-8" v-if="counter.length > 0">
      <!-- 👉 Sales -->

      <component v-for=" (card, index ) in counter" :key="index" :card="card" :is="cardsComponentMap[card.component]">
      </component>

    </div>

    <div class="grid-row grid-cols-1 md:grid-cols-2" v-if="cards.length > 0">

      <component v-for=" (card, index ) in cards" :key="index" :card="card" :is="cardsComponentMap[card.component]">
      </component>


    </div>
  </div>
</template>

<script setup>
import partition from 'lodash/partition'

import { cardsComponentMap } from '@/components.js'

const counter = ref([])

const cards = ref([])

onMounted(() => {
  Application.request('/api/cards').then(({ data }) => {
    const items = partition(data, ['component', 'count-card']);

    counter.value = items[0]
    cards.value = items[1]
  })
})

</script>


<route lang="json">
{
  "name": "index",
  "meta": {
    "layout": "app",
    "title": "asd",
    "requiresAuth": false
  }
}
</route>