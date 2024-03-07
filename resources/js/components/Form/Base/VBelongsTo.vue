<template>
  <VAutocomplete id="search" :search="search" icon-placement="prepend-inner" class="text-sm w-full" icon="bx-search"
    placeholder="Search.." return-object :label-key="field.labelKey" :label="field.label" :resource-name="field.asyncUrl"
    :disabled="disabled" clearable v-model="selected">

    <template #item="{ item }">
      <AListItem :title="item.display_name" icon="i-bx-right-arrow" icon-append></AListItem>
    </template>

  </VAutocomplete>
</template>


<script setup>
import { watch } from 'vue';
import { defaultEmits, defaultProps } from './Input'

const props = defineProps({
  ...defaultProps,
  resourceName: String,
})

const emits = defineEmits(['update:search', ...defaultEmits])

const search = ref('')

const selected = ref('')

watch(selected, (val) => {
  props.form.fill(props.field, val)
})

onMounted(() => {
  selected.value = props.field.value
})


</script>
