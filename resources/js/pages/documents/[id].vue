<template>
  <div class="flex mb-4">
    <ACard class="w-full">
      <div class="a-card-body a-card-spacer">
        <p class="a-title"> {{ store.record.subject }} </p>
        <p class="a-subtitle"> {{ store.record.no }} </p>

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

      <ResourceFieldsGenerator :disabled="!edit" :except="['aircraft_id', 'assignee_id']" :resource-name="resourceName"
        @submitted="saved" />

    </ACard>

    <ACard title="Revision History" subtitle="Chocolate cake tiramisu donut" class="w-full col-auto md:col-span-2">
      <Timeline ref="timeline" :resource-name="resourceName" :resource-id="resourceId">
        <template #after>
          <div class="flex justify-end px-2">
            <ABtn class="" icon="i-bx-plus">

              <AMenu persist="content">
                <FieldsGenerator :fields="fields" :form="form" @submited="submit" resource-name="revisions" />
              </AMenu>

              Add Revision
            </ABtn>
          </div>
        </template>
      </Timeline>
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

const edit = ref(false)

const store = findStore(props.resourceName)

const route = useRoute()

const resourceId = Number(route.params.id)

const form = new Form({
  body: '',
  title: '',
})

const fields = new Fields([
  {
    "component": "v-input",
    "attribute": "title",
    "placeholder": "Enter Title",
    "label": "Title",
    "value": null,
    "isRequired": true,
    "inputType": "text"
  },
  {
    "component": "v-input",
    "attribute": "body",
    "placeholder": "Enter Body",
    "label": "Body",
    "value": null,
    "isRequired": true,
    "inputType": "text"
  }
]).fill(form.data)



const saved = () => {
  store.table.needFetch = true
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
  "name": "documents-update",
  "meta": {
    "layout": "app",
    "requiresAuth": true,
    "intent": "update"
  }
}
</route>