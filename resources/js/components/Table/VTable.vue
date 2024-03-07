<template>
  <ACard>
    <template #title>
      <div class="grid-row sm:grid-cols-3 place-items-stretch">
        <span>{{ title }}</span>
        <span> <!--  --> </span>
        <AInput :placeholder="`Search on ${title} table..`" v-model="search" prepend-inner-icon="i-bx-search"
          @input="searchDebounce" class="text-sm">
          <template #append-inner>
            <i class="cursor-pointer i-bx-x" @click="reset"></i>
          </template>
        </AInput>
      </div>
    </template>

    <div class="pa-2" v-if="table.filters.length > 0">
      <Filters :filters="table.filters" :query="table.query" />
    </div>

    <ATable :cols="table.getColumns()" :rows="table.getRows()" :loading="table.loading()" id="table">
      <!-- 👉 Header slot -->
      <template v-for="(col, index) in table.getColumns()" :key="col.name" #[`header-${col.name}`]>
        <span v-if="col.isSortable" class="cursor-pointer" @click="table.sortColumn(col)">{{ col.name }}</span>
        <span v-else>{{ col.name }}</span>

        <i v-show="col.sortBy === 'asc'" class="i-bx-up-arrow-alt" />
        <i v-show="col.sortBy === 'desc'" class="i-bx-down-arrow-alt" />

      </template>

      <!-- 👉 Row slot -->
      <template v-for="(col, index) in  table.getColumns() " :key="col.name" #[`col-${col.name}`]='{ row }'>
        <slot :name="`col-${col.name}`" :row="row">
          <component :is="columnComponentMap[col.component]" :key="row.id" :row="row" :col="col"> </component>
        </slot>
      </template>

      <!-- 👉 Pagination -->
      <template #after-table>
      </template>
    </ATable>

    <VTablePagination :table="table" />
  </ACard>
</template>

<script setup>
import debounce from 'lodash/debounce'
import { columnComponentMap } from '@/components.js'
import { titleCase } from '@/utils/formatStr.js'
import Table from './Table';

const props = defineProps({
  resourceName: String,
})

let title = titleCase(props.resourceName)

const emits = defineEmits(['loaded'])

const table = new Table(props.resourceName)

const search = ref('')
const searchDebounce = debounce(() => table.query.q = search.value, 500)

const reset = () => {
  search.value = ''
  table.query.q = ''
};

if (table.store.table.needFetch) {
  table.init()

  table.store.table.needFetch = false
  emits('loaded', true)
}

const refresh = () => table.refresh()

onUnmounted(() => {
  table.unMounted()
})

defineExpose({
  table,
  refresh
})
</script>