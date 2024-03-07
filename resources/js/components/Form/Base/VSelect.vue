<template>
  <ASelect v-if="loaded" :loading="loading" :label="field.label" :placeholder="field.placeholder" ref="inputRef"
    v-model="localValue" :options="options" :error="form.errors.get(field.attribute)" :disabled="disabled" emit-object>
    <template #[field.iconPlacement]>
      <Icon :icon="field.icon" />
    </template>
  </ASelect>
</template>

<script setup>
import { defaultEmits, defaultProps } from './Input'

const emits = defineEmits([...defaultEmits])
const props = defineProps({
  ...defaultProps,
  modelValue: String | Number,

})

const options = computed(() => props.field.options)

const loaded = ref(false)

const localValue = computed({
  get() {
    return props.field.value
  },
  set(val) {
    return props.field.set(val)
  }
})

const watchLocalValue = watch(localValue, (val) => {
  props.form.fill(props.field, val && val.value)
  emits('update:modelValue', val)
})

onMounted(() => {
  //let val = options.value.find((option) => option.value === props.field.value)
  //localValue.value = val && val.value

  nextTick(() => {
    loaded.value = true
  })
})

onUnmounted(() => {
  watchLocalValue()
})


</script>
