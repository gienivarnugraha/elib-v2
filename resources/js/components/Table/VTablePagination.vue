<template>
  <div class="a-data-table-pagination flex items-center w-full">
    <span class="a-subtitle a-data-table-pagination-meta">{{ table.paginationMeta() }}</span>
    <div class="flex-grow" />
    <div class="a-data-table-per-page flex items-center">
      <span class="sm:inline hidden">per page</span>
      <ASelect v-model="table.query.per_page"
        :options="Array.from(new Set([table.query.per_page, 10, 15, 20, 25])).sort((a, b) => a - b)"
        options-wrapper-classes="a-data-table-per-page-select--options-wrapper-classes" />
    </div>
    <span class="a-subtitle a-data-table-pagination-meta"> {{ table.pageMeta() }} </span>
    <div class="a-data-table-pagination-navigation">
      <ABtn class="a-data-table-paginate-previous" icon="i-bx-left-arrow-alt" icon-only variant="text"
        :disabled="table.pagination.isFirstPage" @click="table.goToPreviousPage()" />
      <ABtn class="a-data-table-paginate-next" icon="i-bx-right-arrow-alt" icon-only variant="text"
        :disabled="table.pagination.isLastPage" @click="table.goToNextPage()" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  table: Object
})
</script>