<template>
  <ACard title="Document Details" subtitle="Chocolate cake tiramisu donut">
    <template #title>
      <div class="flex justify-between">
        <span>Card title</span>
        <div class="flex items-center gap-x-3 text-base text-light-emphasis">
          <!-- <i class="cursor-pointer" :class="{ 'i-bx-pencil': !edit, 'i-bx-x': edit }" @click="edit = !edit" /> -->
        </div>
      </div>
    </template>

    <ATabs :tabs="tabs" class="a-tabs-bordered">
      <template #settings>
        <avatar :src="`http://localhost:8000${user.avatar}`"></avatar>
        <ResourceFieldsGenerator resource-name="users" :resource-id="user.id" @submitted="saved" />
      </template>
      <template #documents>
        <AListItem v-for="document in documents" :title="document.subject" :subtitle="document.no"
          @click="$router.push('/documents/' + document.id)">
          <template #prepend>
            <AAvatar class="rounded-lg text-[1.25rem]" :content="document.index"></AAvatar>
          </template>
          <template #append>
            <Icon icon="bx-show" help-text="View" />
          </template>
        </AListItem>
      </template>
      <template #revisions>
        <AListItem v-for="revision in revisions" :title="revision.title" :subtitle="revision.index_date">
          <template #prepend>
            <AAvatar class="rounded-lg text-[1.25rem]" :content="revision.index"></AAvatar>
          </template>
        </AListItem>
      </template>
    </ATabs>


  </ACard>
</template>


<script setup>
import { onMounted } from 'vue';


const user = Application.config.user

const documents = ref([])
const revisions = ref([])

onMounted(() => {
  Application.request('/api/owned').then(({ data }) => {
    documents.value = data.documents
    revisions.value = data.revisions
  })
})

const edit = ref(false)
const tabs = [
  {
    title: 'Settings',
    value: 'settings',
    icon: 'i-bx-cog',
  },
  {
    title: 'Documents',
    value: 'documents',
    icon: 'i-bx-file',
  },
  {
    title: 'Revisions',
    value: 'revisions',
    icon: 'i-bx-abacus',
  }
]

const saved = () => console.log('saved');

</script>


<route lang="json">
{
  "name": "settings",
  "meta": {
    "layout": "app",
    "intent": "update",
    "requiresAuth": true
  }
}
</route>