<template>
  <ABaseInput @click:inputWrapper="wrapperClick" :label="field.label" :disabled="disabled" :help="field.helpText">
    <template #[iconPlacement]>
      <Icon :icon="icon" :help-text="field.helpText" />
    </template>

    <template #append-inner v-if="clearable">
      <i class="cursor-pointer i-bx-x" @click="reset" v-tooltip="'Clear Input'"></i>
    </template>

    <template #default="slotProps">
      <div v-if="loaded" class="flex flex-nowrap items-center w-full">
        <input v-bind="{ ...$attrs, ...slotProps }" ref="input" v-model="localValue" class="a-input-input"
          :name="field.attribute" :type="field.inputType" :placeholder="field.placeholder" :minlength="minlength"
          :maxlength="maxlength" :min="min" :max="max" :step="field.inputType === 'number' && decimal ? 0.01 : 0"
          @keydown="form.onKeydown(field.attribute)" @blur="blurHandler" @focus="focusHandler" />
      </div>
    </template>

  </ABaseInput>
</template>

<script setup>
import { useVModel, defaultEmits, defaultProps } from './Input'

const emits = defineEmits([...defaultEmits])
const props = defineProps({
  ...defaultProps,
  modelValue: String | Number,
  decimal: {
    type: Boolean,
    default: false
  },
  minlength: Number,
  maxlength: Number,
  min: Number,
  max: Number,
  clearable: Boolean
})


const input = ref()

const wrapperClick = () => input.value?.focus()

const { loaded, localValue, blurHandler, focusHandler, reset } = useVModel(props, emits)

</script>
