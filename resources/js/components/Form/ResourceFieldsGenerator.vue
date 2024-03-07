<template>
  <FieldsGenerator :fields="fields" :disabled="disabled" :except="except" :only="only" :form="form" @submited="submit" />
</template>

<script setup>
import isEmpty from "lodash/isEmpty";
import Form from './Form';
import Fields from './FieldsCollection';
import { findStore } from '@/store'

const props = defineProps({
  resourceName: String,
  resourceId: Number,
  disabled: Boolean,
  except: [Array, String],
  only: [Array, String],
})

const emits = defineEmits(['submited', 'reset'])

const route = useRoute()

let form = new Form()

let fields = new Fields()

let resourceId = props.resourceId ?? Number(route.params.id)

let view = route.meta.intent

const getUri = computed(() => {
  let uri = `/api/${props.resourceName}`

  if (resourceId) uri += `/${resourceId}`

  uri += `/${view}-fields`

  return uri
})

const submit = async () => {
  let data;
  try {
    if (resourceId) {
      data = await form.put(`/api/${props.resourceName}/${resourceId}`)
      form.update(data)
      Application.info(`Success Updated ${props.resourceName}`)
    } else {
      data = await form.post(`/api/${props.resourceName}`)
      Application.info(`Success Created New ${props.resourceName}`)
    }
    emits('submited', data)
  } catch (error) {
    console.error(error);
  }
}

const store = findStore(props.resourceName)

let storeFields = store.$state.fields
let storeRecord = store.$state.record

const getFields = async () => {
  if (!isEmpty(storeFields)) {
    fields.set(storeFields)
    console.log('get fields from store');
  } else {
    const fieldResponse = await form.get(getUri.value)
    fields.set(fieldResponse)
    store.$state.fields = fieldResponse
  }

  if (view == 'create') {
    let newRecord = store.resetRecord()
    form.set(newRecord)
    fields.fill(newRecord)
  } else if (view === 'update' && resourceId) {
    getRecord()
  }
}

const getRecord = async () => {
  if (!isEmpty(storeRecord) && resourceId === storeRecord.id) {
    form.set(storeRecord)
    fields.fill(storeRecord)
    console.log('get record from store');
  }
  else {
    const record = await form.get(`/api/${props.resourceName}/${resourceId}`)

    form.set(record)
    fields.fill(record)
    store.$state.record = record
  }

}

onMounted(() => {
  getFields()
})
</script>