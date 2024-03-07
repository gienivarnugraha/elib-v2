<template>
  <ASwitch v-if="loaded" :loading="loading" :label="field.label" :placeholder="field.placeholder" v-model="localValue"
    :error="form.errors.get(field.attribute)" :disabled="disabled">
    <template #[field.iconPlacement]>
      <Icon :icon="field.icon" />
    </template>
  </ASwitch>
</template>

<script setup>
import { useVModel, defaultEmits, defaultProps } from './Input'

const emits = defineEmits([...defaultEmits])
const props = defineProps({
  ...defaultProps,
  modelValue: Boolean,

})

const { loaded, localValue } = useVModel(props, emits)

</script>
