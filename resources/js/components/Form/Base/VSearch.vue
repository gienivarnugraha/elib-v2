<template>
  <VAutocomplete id="search" :search="search" icon-placement="prepend-inner" class="text-sm w-full" icon="bx-search"
    placeholder="Search.." clearable>

    <template #prepend="{ item }">
      <AListItem class="a-title"> {{ item.title }} </AListItem>
      <span class="h-separator"></span>
    </template>

    <template #item="{ item }">
      <router-link v-for="(data, index) in item.data" :key="index" :to="data.path">
        <AListItem :title="`Title: ${data.display_name}`" :subtitle="`Created At: ${data.created_at}`"
          icon="i-bx-right-arrow" icon-append></AListItem>
      </router-link>
    </template>

    <template #append="{ item }">
      <AListItem class="a-subtitle"> Found {{ item.data.length }} results on {{ item.title }} </AListItem>
    </template>

  </VAutocomplete>
</template>

<script setup>
const search = ref('')

</script>