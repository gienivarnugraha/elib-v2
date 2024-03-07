<template>
  <v-table ref="table" :resource-name="resourceName">
    <template #col-action="{ row }">

      <span class="inline-flex">
        <span v-if="row.is_confirmed">
          <ABtn variant="text" @click="generate(row.id)" icon="i-bx-refresh" color="info" v-tooltip="'Reresh Passcode'" />

        </span>
        <span v-else>

          <ABtn variant="text" @click="confirm(row.id, true)" icon="i-bx-check" color="success"
            v-tooltip="'Accept Request'" />
          <ABtn variant="text" @click="confirm(row.id, false)" icon="i-bx-x" color="red" v-tooltip="'Reject Request'" />
        </span>
      </span>
    </template>
  </v-table>
</template>


<script setup>
const props = defineProps({
  resourceName: String,
})

const table = ref(null)

const generate = async (id) => {
  const { data } = await Application.request().post(`/api/orders/generate`, { id })

  try {    
    table.value.refresh()
  } catch (error) {
    console.log(error);
  };
}

const confirm = async (id, confirmed) => {
  const { data } = await Application.request().post(`/api/orders/confirm`, { id, confirmed })

  try {
    table.value.refresh()
  } catch (error) {
    console.log(error);
  };
}

</script>


<route lang="json">
{
  "name": "order",
  "meta": {
    "layout": "app",
    "requiresAuth": true
  }
}
</route>