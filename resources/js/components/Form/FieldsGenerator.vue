<template>
  <div class="max-h-screen">
    <div class="a-card-body a-card-spacer max-h-[calc(60vh+7rem)] overflow-y-auto">
      <div class=" rounded-lg ">

        <ALoader v-if="!loaded"></ALoader>
        <component v-else class="mb-4" v-for="(field, index) in iterableFields" :key="index" :form="form" :field="field"
          :is="componentMap[field.component]" :disabled="disabled || field.readonly"
          :error="form.errors.get(field.attribute)">
        </component>

      </div>
    </div>
    <slot name="after" v-if="!disabled">

      <div class="flex my-4 gap-4 px-4">

        <ABtn class="w-full " @click="reset" :loading="loading" :disabled="loading" variant="outline" color="red"> Reset
        </ABtn>
        <ABtn class="w-full " @click="$emit('submited')" :loading="loading" :disabled="loading"> Submit </ABtn>
      </div>
    </slot>

  </div>
</template>

<script setup>
import castArray from "lodash/castArray";
import reject from "lodash/reject";
import { componentMap } from '@/components.js'

const props = defineProps({
  form: Object,
  fields: [Object, Array],
  disabled: Boolean,
  except: [Array, String],
  only: [Array, String],

})


const emits = defineEmits(['submited', 'reset'])

const loaded = computed(() => props.fields.loaded() && props.form.loaded.value)

const loading = computed(() => props.form.busy.value)

const isCollection = computed(() => !Array.isArray(props.fields))

const iterableFields = computed(() => {
  let fields = isCollection ? props.fields.all() : props.fields

  if (props.only) {
    return reject(
      fields,
      (field) => castArray(props.only).indexOf(field.attribute) === -1
    );
  } else if (props.except) {
    return reject(
      fields,
      (field) => castArray(props.except).indexOf(field.attribute) > -1
    );
  }

  return fields;
})

const reset = () => {
  emits('reset')
  props.form.reset()
  props.fields.reset()
  props.form.errors.clear()
}


</script>