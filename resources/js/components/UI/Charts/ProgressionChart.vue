<template>
  <ACard subtitle="chart" class="w-full">
    <template #title>
      <div class="flex justify-between gap-x-2">
        <span class="flex grow"> {{ title }} </span>
        <ASelect class="text-xs w-12 flex-none" icon="i-bx-calendar" :options="card.ranges" v-model="card.range"
          @change="onChange">
        </ASelect>
      </div>
    </template>

    <div class="a-card-body">

      <ALoader v-if="loading" />
      <BarChart v-else ref="chartRef" :chartData="chartData" :options="options" />

    </div>
  </ACard>
</template>


<script setup>

import { BarChart } from 'vue-chart-3';
import { Chart, BarController, CategoryScale, LinearScale, BarElement, Tooltip } from "chart.js";

Chart.register(BarController, CategoryScale, LinearScale, BarElement, Tooltip)

const props = defineProps({
  card: Object
})

const chartRef = ref();

const loading = ref(true)

const data = ref([]);
const labels = ref([]);
const title = props.card.name

const backgroundColor = ['#77CEFF', '#0079AF', '#123E6B', '#97B0C4', '#A5C8ED']

const options = ref({
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: true,
      text: title,
    },
  },
});

const chartData = computed(() => ({
  labels: labels.value,
  datasets: [
    {
      data: data.value,
      backgroundColor,
    },
  ],
}));

const updateChart = () => chartRef.value.update()

const onChange = async (val) => {
  //loading.value = true

  try {
    const { data: response } = await Application.request().get(`/api/cards/${props.card.uriKey}`, {
      params: {
        range: val,
        // user_id: Application.config.user.id
      }
    })

    data.value = response.result.map((x) => x.value)
    labels.value = response.result.map((x) => x.label)

    updateChart()

  } finally {
    //loading.value = false
  }
}

onMounted(() => {
  loading.value = false
  data.value = props.card.result.map((x) => x.value)
  labels.value = props.card.result.map((x) => x.label)
})

</script>