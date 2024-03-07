<template>
  <AInput placeholder="Enter Password" :type="showPassword ? 'input' : 'password'" :loading="loading" :disabled="disabled"
    label="Password" v-model="localValue" :error="form.errors.get('password')">
    <template #append-inner>
      <i class="cursor-pointer mr-2 hover:bg-primary " :class="showPassword ? 'i-bx-hide' : 'i-bx-show'"
        @click="showPassword = !showPassword"></i>
    </template>
  </AInput>
</template>

<script setup>
import { useVModel, defaultEmits, defaultProps } from './Input'

const emits = defineEmits([...defaultEmits])

const props = defineProps({
  ...defaultProps,
  modelValue: String,
})
const { localValue } = useVModel(props, emits)

const showPassword = ref(false)

</script>