<template>
  <div>
    <ASelect :options="options" v-model="model" :label="filter.label" class="text-xs" @change="onChange">
    </ASelect>

  </div>
</template>

<script setup>

const model = ref()

const props = defineProps({
  filter: Object
})

const options = props.filter.options.map((option) => {
  return {
    text: option[props.filter.labelKey],
    value: option[props.filter.valueKey]
  }
})

const emits = defineEmits(['update-rules'])

const onChange = (value) => {
  const data = {
    ...props.filter.rules,
    value
  }

  emits('update-rules', data)
}
</script>