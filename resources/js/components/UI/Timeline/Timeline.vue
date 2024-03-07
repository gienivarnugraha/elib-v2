<template>
  <ALoader v-if="loading" />
  <div v-else>

    <div class="flex items-center mb-4">
      <span class="w-16 text-center">Index</span>
      <span class="grow">Title</span>
    </div>

    <div class="flex pl-8  max-h-[calc(60vh+7rem)] overflow-y-auto">

      <ol class="pl-4 relative border-s border-gray-200 dark:border-gray-700 w-full  ">

        <AListItem v-for="item in items" :key="item.id" class="w-full" color="success">
          <span
            class="absolute flex items-center justify-center w-8 h-8 rounded-full -start-4 ring-4  ring-gray-200 dark:ring-gray-700 bg-sky-200 dark:bg-sky-600">
            <b> {{ item.index }}</b>
          </span>
          <slot :item="item">

            <div class="w-full">
              <div class="flex justify-between items-center">
                <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> {{ item.title }}
                </h3>

                <span class="text-lg" :class="getColor(item)" v-tooltip="getTooltip(item)"></span>

              </div>
              <time class="block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                By: <a class="underline text-blue-400" v-if="item.user"> {{
                  item.user.name
                }}
                  <ATooltip class="">
                    <div class="flex items-center gap-x-2 pa-1">
                      <AAvatar :src="item.user.avatar"></AAvatar>
                      <div class="flex flex-col text-left ">
                        <span class="a-title">{{ item.user.name }}</span>
                        <span class="a-subtitle">{{ item.user.name }}</span>
                      </div>
                    </div>
                  </ATooltip>
                </a>
                at {{ item.created_at }}</time>
              <p class=" mb-4 text-base font-normal text-gray-500 dark:text-gray-400">
                {{ item.body }}
              </p>

              <TimelineFooter :item="item" @closed="fetchTimeline" @canceled="fetchTimeline" @uploaded="fetchTimeline" />

            </div>
          </slot>


        </AListItem>
      </ol>

    </div>

    <span class="h-separator"></span>
    <div class="py-4">
      <slot name="after">

      </slot>

    </div>

  </div>
</template>

<script setup>
import { defineExpose } from 'vue';

const props = defineProps({
  resourceName: String,
  resourceId: Number
})


const loading = ref(true)

const items = ref([])

const fetchTimeline = () => {
  loading.value = true
  Application.request(`/api/revisions/${props.resourceName}/${props.resourceId}`)
    .then((response) => {
      items.value = response.data
      loading.value = false
    })
}

const getColor = (item) => {
  if (item.is_canceled) return 'bg-rose-200  dark:bg-rose-600 i-bx-x-circle'
  else if (item.is_closed) return 'bg-emerald-200  dark:bg-emerald-600 i-bx-check-circle'
  else return 'bg-cyan-200  dark:bg-cyan-600 i-bx-info-circle'
}

const getTooltip = (item) => {
  if (item.is_canceled) return 'Revision Canceled'
  else if (item.is_closed) return 'Revision Closed'
  else return 'Revision Open'
}

onMounted(() => {
  fetchTimeline()
})

defineExpose({
  fetchTimeline
})


</script>