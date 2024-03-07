<template>
  <div v-if="hasFile || isNotClosedOrCanceled"
    class="grid grid-cols-2 gap-2 justify-between pa-2 w-full border rounded-lg bg-gray-200 border-gray-200 dark:(bg-gray-800 border-gray-800)  ">
    <div class="justify-self-start">
      <div class=" grid grid-cols-3 gap-x-2">
        <ABtn class="text-xs" :loading="loading" v-if="hasFile" icon="i-bx-file" variant="outline" @click="show"
          v-tooltip="'View Document'" />

        <ABtn class="text-xs transition" :loading="loading" v-show="isNotClosedOrCanceled && file" :key="item.id"
          color="warning" icon="i-bx-upload" @click="upload(item.id)" v-tooltip="'Upload Document'" />

        <ABtn class="text-xs" :loading="loading" v-if="isNotClosedOrCanceled" :variant="hasFile ? 'outline' : 'fill'"
          color="info" :icon="hasFile ? 'i-bx-refresh' : 'i-bx-file'" @click="selectFile"
          v-tooltip="hasFile ? 'Change Document' : 'Select Document'" />

      </div>

    </div>
    <div class="justify-self-end" v-if="isNotClosedOrCanceled">
      <div class=" grid grid-cols-3 gap-x-2">
        <ABtn :loading="loading" class="text-xs" color="danger" icon="i-bx-x" @click="cancel"
          v-tooltip="'Cancel Document'"></ABtn>
        <ABtn :loading="loading" class="text-xs" color="success" icon="i-bx-check" @click="close"
          v-tooltip="'Close Document'"></ABtn>
        <ABtn :loading="loading" class="text-xs" color="info" icon="i-bx-mail-send" @click="requestDoc(item.media.uuid)"
          v-tooltip="'Request Document'"></ABtn>

      </div>

    </div>

    <input style="display:none" ref="fileRef" @change="fileChanged" type="file" accept="application/pdf" />
  </div>
</template>

<script setup>


const props = defineProps({
  item: Object,
})

const emits = defineEmits(['closed', 'canceled', 'uploaded'])

const id = props.item.id

const router = useRouter()

const loading = ref(false)
const fileRef = ref(null)
const file = ref()

const hasFile = computed(() => props.item.media)

const isNotClosedOrCanceled = computed(() => !props.item.is_closed && !props.item.is_canceled)

const selectFile = () => fileRef.value.click()

const fileChanged = (event) => file.value = event.target.files[0]

const upload = (id) => {
  loading.value = true

  let formData = new FormData()
  formData.append('files', file.value)

  Application.request({
    url: `/api/revisions/${id}/upload`,
    headers: {
      "Content-Type": "multipart/form-data"
    },
    method: 'POST',
    data: formData
  })
    .then((response) => {
      emits('uploaded', response.data)
    })
    .finally(() => loading.value = false)
}

const requestDoc = async (uuid) => {
  const { data } = await Application.request().post(`/api/orders`, { uuid })

  try {
    console.log(data)
  } catch (error) {
    console.log(error);
  };
}
const close = () => {
  loading.value = true
  Application.request().put(`/api/revisions/${id}/close`, { is_closed: true })
    .then((response) => {
      emits('closed', response.data)
    })
    .finally(() => loading.value = false)
}

const show = () => {
  router.push(`/pdf/${props.item.media.uuid}`)
}

const cancel = () => {
  loading.value = true
  Application.request().put(`/api/revisions/${id}/cancel`, { is_canceled: true })
    .then((response) => {
      emits('canceled', response.data)
    })
    .finally(() => loading.value = false)
}
</script>