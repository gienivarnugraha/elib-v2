export const defaultProps = {
  id: String,
  name: String,
  autofocus: Boolean,
  rounded: {
    default: true,
    type: Boolean,
  },
  bordered: {
    default: true,
    type: Boolean,
  },
  size: {
    type: [String, Boolean],
    default: "",
    validator: (value) => ["sm", "lg", "md", "", false].includes(value),
  },
  icon: {
    type: String,
  },
  iconPlacement: {
    type: [String, Boolean],
    validator(value) {
      return ["append", "prepend", "append-inner", "prepend-inner", false].includes(value);
    },
    default: false,
  },
  attribute: String,
  form: Object,
  field: Object,
  loading: Boolean,
  disabled: Boolean
}

export const defaultEmits = [
  'update:modelValue', 'blur', 'change', 'focus',
]

export const useVModel = (props, emit) => {
  const valueWhenFocus = ref(null)

  const loaded = ref(props.field.loaded.value)

  let localValue = computed({
    get() {
      return props.field.value
    },
    set(val) {
      return props.field.set(val)
    }
  })

  const reset = () => {
    localValue.value = ''
  }

  const watchLocalValue = watch(localValue, (val) => {
    props.form.fill(props.field, val)
    emit('update:modelValue', val)
  })

  onMounted(() => {
    localValue.value = props.field.value

    nextTick(() => {
      loaded.value = true
    })
  })

  onUnmounted(() => {
    watchLocalValue()
  })

  const blurHandler = (e) => {
    emit('blur', e)

    if (localValue.value !== valueWhenFocus.value) {
      emit('change', localValue.value)
    }
  }

  const changeHandler = (e) => emit('change', e.target.value)

  const focusHandler = (e) => {
    emit('focus', e)

    valueWhenFocus.value = localValue.value
  }

  return { localValue, loaded, blurHandler, focusHandler, changeHandler, reset }

}