<template>
  <div>
    <ABaseInput @click:inputWrapper="wrapperClick" :label="label" :disabled="disabled">
      <template #[iconPlacement]>
        <Icon :icon="icon" />
      </template>

      <template #append-inner v-if="clearable">
        <i class="cursor-pointer i-bx-x" @click="clear"></i>
      </template>

      <template #default="slotProps">
        <div class="flex flex-nowrap items-center w-full">

          <AChip v-if="multiple" class=" mr-2" v-for="(item, index) in localSelect" :key="index" closable
            @click:close="onRemove(item)">
            <slot name="chip" :item="item">
              <span>{{ item[valueKey] }}</span>
            </slot>
          </AChip>

          <input v-bind="{ ...$attrs, ...slotProps }" ref="input" :value="localInput" class="" @input="onInput"
            :placeholder="placeholder" @blur="onBlur" @focus="onFocus" />
        </div>
      </template>

    </ABaseInput>

    <AMenu v-model="showMenu">
      <AListItem v-if="loading">
        <ALoader />
      </AListItem>

      <AList v-else :items="items" class="max-h-32 overflow-y-auto">
        <AList v-if="hasItems" v-for="(item, index) in items" :key="index" :disabled="item.disabled"
          @click="onSelect(item)">
          <slot name="prepend" :item="item">
          </slot>
          <slot name="item" :item="item">
            <AListItem :title="item.display_name" :is-active="isActive(item)" :subtitle="item.path"
              icon="i-bx-right-arrow" icon-append></AListItem>
          </slot>


          <slot name="append" :item="item">
          </slot>
        </AList>

        <AListItem v-else class="text-center" :title="noData" />
      </AList>

    </AMenu>

  </div>
</template>

<script setup>
import debounce from 'lodash/debounce';
import { defaultEmits, defaultProps } from './Input'
import isObject from 'lodash/isObject';

const props = defineProps({
  ...defaultProps,
  placeholder: String,
  label: String,
  search: String,
  modelValue: String | Array,
  multiple: Boolean,
  disabled: Boolean,
  minLength: {
    type: Number,
    default: 2
  },
  labelKey: {
    type: String,
    default: 'name'
  },
  valueKey: {
    type: String,
    default: 'id'
  },
  clearable: {
    type: Boolean,
    default: false
  },
  returnObject: {
    type: Boolean,
    default: false
  },
  resourceName: String,
})

const emits = defineEmits(['update:search', ...defaultEmits])

const input = ref()

const wrapperClick = () => {
  input.value?.focus()
}

const noData = computed(() => {
  if (localInput.value && props.minLength > 0) {
    return localInput.value.length > props.minLength ? `Type to start searching..` : 'Nothing found..'
  } else if (localInput.value && items.value.length === 0) {
    return `Nothing Found..`
  }
  else {
    return `Type ${props.minLength} character to start searching..`
  }
})

const loading = ref(false)

const items = ref([])
const hasItems = computed(() => items.value.length > 0)

const showMenu = ref(false)

const localSelect = ref(props.multiple ? [] : '')

const localInput = computed({
  get: () => {
    if (isObject(props.modelValue)) {
      return props.modelValue[props.labelKey]
    } else {
      return props.modelValue
    }
  },
  set: (value) => value
})

const findIndex = (item) => localSelect.value.findIndex(sel => sel.id === item.id)

const onSelect = (item) => {
  if (props.multiple && Array.isArray(localSelect.value)) {

    if (findIndex(item) === -1) {
      localSelect.value.push(item)
    }
  } else {
    localSelect.value = item
  }

  localInput.value = item[props.labelKey]

  if (props.returnObject) {
    emits('update:modelValue', item)
  } else {
    emits('update:modelValue', item[props.labelKey])
  }

}
const onRemove = (item) => {
  localSelect.value.splice(findIndex(item), 1)
}

const onBlur = () => {
  showMenu.value = false
}

const onFocus = () => {
  showMenu.value = true
}

const onInput = (event) => {
  let value = event.target.value
  localInput.value = value

  if (value.length > props.minLength) {
    performSearch(value)
  }

  emits('update:search', value)
}


const isActive = (item) => {
  if (props.multiple) {
    return findIndex(item) !== -1
  } else {
    return localSelect.value === item
  }
}

const clear = () => {
  items.value = []
  showMenu.value = false
  localInput.value = ''
}

const performSearch = debounce((val) => request(val), 1000)

const uri = computed(() => {
  let api = '/api'
  if (props.resourceName) api += '/' + props.resourceName
  api += '/search'
  return api
})

const request = async (val) => {
  loading.value = true

  try {
    const { data } = await Application.request().get(uri.value,
      {
        params: {
          q: val,
          take: 5,
        }
      })

    let result = data.map((item) => {
      return {
        text: item[props.labelKey],
        value: item[props.valueKey],
        ...item
      }
    })

    items.value = result

  } catch (error) {
  }
  loading.value = false
}


</script>
