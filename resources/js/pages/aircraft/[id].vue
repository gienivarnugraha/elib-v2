<template>
  <div class="flex mb-4">
    <ACard class="w-full">
      <div class="a-card-body a-card-spacer">
        <p class="a-title"> {{ store.record.type }} / S/N : {{ store.record.serial_num }} / Reg : {{ store.record.reg_code
        }} </p>
        <p class="a-subtitle"> Owner : {{ store.record.owner }} </p>
        <p class="a-subtitle"> Manufactured: {{ store.record.manuf_date }} </p>

      </div>

    </ACard>

  </div>

  <div class="grid-row md:grid-cols-3">
    <ACard title="Document Details" subtitle="Chocolate cake tiramisu donut" class="w-full col-auto md:col-span-1">
      <template #title>
        <div class="flex justify-between">
          <span>Card title</span>
          <div class="flex items-center gap-x-3 text-base text-light-emphasis">
            <i class="cursor-pointer" :class="{ 'i-bx-pencil': !edit, 'i-bx-x': edit }" @click="edit = !edit" />
          </div>
        </div>
      </template>

      <ResourceFieldsGenerator :disabled="!edit" :resource-name="resourceName" @submited="saved" />

    </ACard>

    <ACard title="Related Documents" subtitle="Lists of documents or manuals related to this aircraft"
      class="w-full col-auto md:col-span-2">

      <ATabs class="a-tabs-bordered" :tabs="tabs">
        <template #documents>
          <div class="my-2">
            <AListItem v-for=" document in store.record.documents" :title="document.subject" :subtitle="document.no"
              @click="router.push('/documents/' + document.id)">
              <template #prepend>
                <AAvatar class="rounded-lg text-[1.25rem]" :content="document.type"></AAvatar>
              </template>
            </AListItem>
          </div>
        </template>
        <template #manuals>
          <div class="my-2">
            <AListItem v-for=" manual in store.record.manuals" :title="manual.title" :subtitle="manual.part_number"
              @click="$router.push('/manuals/' + manual.id)">
              <template #prepend>
                <AAvatar class="rounded-lg text-[1.25rem]" :content="manual.type"></AAvatar>
              </template>
            </AListItem>
          </div>
        </template>
      </ATabs>
    </ACard>
  </div>
</template>


<script setup>
import Fields from '../../components/Form/FieldsCollection';
import Form from '../../components/Form/Form';
import { findStore } from '@/store'

const props = defineProps({
  resourceName: String,
})

const tabs = [
  {
    title: 'Documents',
    value: 'documents',
    icon: 'i-bx-file-blank',
  },
  {
    title: 'Manuals',
    value: 'manuals',
    icon: 'i-bx-bxs-plane',
  }]

const edit = ref(false)

const store = findStore(props.resourceName)

const route = useRoute()

const resourceId = Number(route.params.id)

const form = new Form()

const fields = new Fields()

const saved = () => {
  edit.value = false
}

const timeline = ref(null)
const submit = () => {
  form.put(`/api/${props.resourceName}/${resourceId}`).then((response) => {
    timeline.value.fetchTimeline()
  })
}


</script>


<route lang="json">
{
  "name": "aircraft-update",
  "meta": {
    "layout": "app",
    "requiresAuth": true,
    "intent": "update"
  }
}
</route>