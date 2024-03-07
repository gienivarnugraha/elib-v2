<template>
  <div class="flex gap-x-2">

    <ABtn icon="i-bx-filter">

      <AMenu persist="content" class="max-h-64 overflow-y-auto">
        <AListItem> Available Rules </AListItem>
        <div class="h-separator"></div>
        <AListItem v-for="filter in filters" :key="filter.id" class="max-h-32 overflow-y-auto" :value="null">
          <component :is="filterComponentMap[filter.component]" :filter="filter" @update-rules="updateRules" />
        </AListItem>

        <!-- <span class="h-separator"></span> -->

        <!-- <AListItem class="max-h-32 overflow-y-auto" @click="modal = true">Add Rule</AListItem> -->

      </AMenu>

      Filter
    </ABtn>


    <div class="flex gap-x-2">
      <AChip v-for="(rule, index) in query.rules" class="rounded-md gap-x-2" :key="index">
        <!--  {{ rule.attribute }} is {{ rule.operator }} to {{ rule.value }}  -->
        {{ ruleDisplay(rule) }}

        <ABtn icon-only variant="text" class="rounded-full text-xs" @click="removeRule(rule)">x</ABtn>
      </AChip>

    </div>


    <!-- <ABtn v-if="query.rules.length > 0" icon="i-bx-x" variant="outline" icon-only @click="clearQuery" v-tooltip="'Clear Filter'" /> -->

    <ADialog v-model="modal">

    </ADialog>
  </div>
</template>

<script setup>
import { filterComponentMap } from '@/components.js'
import find from 'lodash/find'

const props = defineProps({
  filters: Array,
  query: Object,
})

const modal = ref(false)

const updateRules = (rules) => {
  // ! Multiple rules
  // if (findIndex(props.query.rules, rules) === -1) {
  //   props.query.rules.push(rules)
  // }
  props.query.rules[0] = rules
}

const removeRule = rules => {
  // ! Multiple rules
  // const index = findIndex(props.query.rules, rules)
  // props.query.rules.splice(index, 1)
  props.query.rules = []
}

const ruleDisplay = (select) => {
  const rule = find(props.filters, (filter) => filter.id === select.attribute && filter.type === select.type)

  const options = rule.type === 'static' ? select : find(rule.options, (option) => option[rule.valueKey] === select.value)

  const value = rule.type === 'static' ? options.value : options[rule.labelKey]

  return `${rule.label} is ${select.operator} to ${value}`

}

const clearQuery = () => props.query.rules.length = 0

</script>