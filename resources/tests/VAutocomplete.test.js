import { describe, it, expect, vi, beforeEach, test } from 'vitest'

import VAutocomplete from '@/components/Form/Base/VAutocomplete.vue'
import TooltipDirective from '@/directives/tooltip'
import { useUsersStore } from '@/store/users'

import { mount, flushPromises, shallowMount, config } from '@vue/test-utils'
import { AInput, AMenu, AList } from 'anu-vue'


describe("testing autocomplete", () => {
  beforeAll(() => {
    config.global.renderStubDefaultSlot = true
  })

  afterAll(() => {
    config.global.renderStubDefaultSlot = false
  })

  test("get default options", async () => {

    const selectedFruits = ref([])
    const search = ref('')
    // const fruits = ref(['banana', 'apple', 'watermelon', 'orange'])
    const fruits = ref([
      { text: 'banana', value: 'banana' },
      { text: 'apple', value: 'apple' },
      { text: 'watermelon', value: 'watermelon' },
      { text: 'melon', value: 'melon' },
      { text: 'orange', value: 'orange' },
    ])

    const wrapper = shallowMount(VAutocomplete, {
      props: {
        items: fruits.value,
        search: search.value,
        modelValue: selectedFruits.value,
      },
      global: {
        directives: { 'tooltip': TooltipDirective },
        stubs: { Icon: true }
      },
    })

    let input = wrapper.getComponent(AInput)
    let menu = wrapper.getComponent(AMenu)
    let list = wrapper.getComponent(AList)

    expect(input.exists()).toBe(true)
    expect(menu.exists()).toBe(true)
    expect(menu.props("modelValue")).toBe(false)
    expect(list.props('items').length).toBe(fruits.value.length)
    
    await wrapper.setProps({ search: 'bana' })
    expect(input.props('modelValue')).toBe('bana')
    expect(list.exists()).toBe(true)
    expect(menu.props("modelValue")).toBe(true)
    expect(list.props('items').length).toBe(1)

    await wrapper.setProps({ search: '' })
    expect(input.props('modelValue')).toBe('')
    expect(menu.props("modelValue")).toBe(false)
    expect(list.props('items').length).toBe(fruits.value.length)

    await wrapper.setProps({ search: 'mel' })
    expect(input.props('modelValue')).toBe('mel')
    expect(list.exists()).toBe(true)
    expect(menu.props("modelValue")).toBe(true)
    expect(list.props('items').length).toBe(2)

    console.log(list.classes());

    // expect(wrapper.find('[data-test="option-input"]')).toContain('i-bx-cog')

  })
})