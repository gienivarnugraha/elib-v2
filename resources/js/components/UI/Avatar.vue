<template>
  <div class="a-card-body">
    <p class="a-title">Avatar</p>

    <div class="grid grid-cols-2 justify-items-center text-center mb-4">
      <div class="flex-row">
        <span class="a-subtitle"> Current </span>
        <img :src="src" class="w-40 h-40" />


      </div>
      <div class="flex-row" v-show="preview">
        <span class="a-subtitle"> New </span>
        <img :src="preview" class="w-40 h-40" />
      </div>
    </div>

    <div class="flex justify-between gap-x-2 items-center">
      <AInput :loading="loading" type="file" accept="image/jpeg" :label="label" :placeholder="placeholder" :error="errors"
        @change="onFileChange">
        <template #[iconPlacement]>
          <Icon :icon="icon" />
        </template>
      </AInput>

      <ABtn v-if="file" :loading="loading" icon="i-bx-upload" @click="upload"></ABtn>
    </div>


  </div>
</template>

<script setup>
const emits = defineEmits(['uploaded'])

const props = defineProps({
  icon: {
    type: String,
  },
  src: String,
  iconPlacement: {
    type: [String, Boolean],
    validator(value) {
      return ["append", "prepend", "append-inner", "prepend-inner", false].includes(value);
    },
    default: false,
  },
  label: String,
  placeholder: String,
})
const loading = ref(false)

const preview = ref('')

const file = ref('')

const errors = ref('')

const onFileChange = (e) => {
  file.value = e.target.files[0]

  const reader = new FileReader();
  reader.readAsDataURL(file.value);
  reader.onload = e => {
    preview.value = e.target.result;
  };

}

const upload = () => {
  loading.value = true

  let formData = new FormData()
  formData.append('avatar', file.value)

  Application.request({
    url: `/api/users/avatar`,
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


</script>
