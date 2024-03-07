import { describe, it, expect, vi, beforeEach, test } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

import Form from '@/components/Form/Form'
import Field from '@/components/Form/FieldsCollection'

import FieldsGenerator from '@/components/Form/FieldsGenerator.vue'
import BelongsTo from '@/components/Form/BelongsTo.vue'
import { useUsersStore } from '@/store/users'

import { mount, flushPromises, shallowMount } from '@vue/test-utils'
import Init from '@/init.js'
import axios from 'axios'

const data = {
  name: "test",
  email: "testEmail",
  setting: {
    currency: 'IDR'
  }
}

const fieldsData = [
  {
    "component": "v-input",
    "attribute": "name",
    "label": "Name",
    "helpText": null,
    "helpTextDisplay": "icon",
    "readonly": false,
    "collapsed": false,
    "icon": "bx-user",
    "iconPlacement": "prepend-inner",
    "toggleable": false,
    "displayNone": false,
    "emitChangeEvent": null,
    "colClass": null,
    "value": null,
    "isRequired": false,
    "inputType": "text"
  },
  {
    "component": "v-input",
    "attribute": "email",
    "label": "Email",
    "helpText": null,
    "helpTextDisplay": "icon",
    "readonly": false,
    "collapsed": false,
    "icon": "bx-envelope",
    "iconPlacement": "prepend-inner",
    "toggleable": false,
    "displayNone": false,
    "emitChangeEvent": null,
    "colClass": null,
    "value": null,
    "isRequired": false,
    "inputType": "email"
  },
  {
    "component": "v-select",
    "attribute": "currency",
    "label": "Currency",
    "helpText": null,
    "helpTextDisplay": "icon",
    "readonly": false,
    "collapsed": false,
    "icon": "bx-calendar",
    "iconPlacement": "prepend-inner",
    "toggleable": false,
    "displayNone": false,
    "emitChangeEvent": null,
    "colClass": null,
    "value": null,
    "isRequired": false,
    "valueKey": "value",
    "labelKey": "text",
    "optionsViaResource": null,
    "options": [
      {
        "text": "USD",
        "value": "USD"
      },
      {
        "text": "IDR",
        "value": "IDR"
      }
    ],
    "isMultiOptionable": false,
    "acceptLabelAsValue": true,
    "hasOneRelation": "setting"
  }
]

// const windowSpy = vi.spyOn(app.prototype, 'request');
// const mock = vi.mock(app, 'request');


describe("testing form ", () => {
  beforeEach((context) => {
    context.form = new Form(data)
  })

  it("init form", ({form}) => {
    expect(form.data).toHaveProperty('name')
  })

  it("update data", ({form}) => {
    const testData = {
      name: "test update",
      email: "testEmail@update",
    }

    form.update(testData)

    expect(form.originalData['name']).toEqual('test')

    expect(form.data['name']).toEqual('test update')
    expect(form.data['email']).toEqual('testEmail@update')

    expect(form.dirty()).toEqual(testData)
    expect(Object.keys(form.dirty()).length).toEqual(2)

    form.startProcessing()

    expect(form.busy.value).toEqual(true)

    form.finishProcessing()

    expect(form.busy.value).toEqual(false)

    form.reset()

    expect(Object.keys(form.dirty()).length).toEqual(0)
  })


})

describe("testing fields", () => {
  beforeEach((context) => {
    context.form = new Form(data)
    context.fields = new Field(fieldsData)
    context.fields.fill(context.form.data)
  })

  it("set fields", ({fields, form}) => {
    const name = fields.find('name')
    const email = fields.find('email')

    expect(name.value).toEqual(form.data.name)
    expect(email.value).toEqual(form.data.email)
  })

  it("update fields", ({fields, form}) => {
    const name = fields.find('name')
    const email = fields.find('email')
    const currency = fields.find('currency')

    expect(name.value).toEqual(form.data.name)
    expect(email.value).toEqual(form.data.email)

    const updated = {
      name: "test update",
      email: "testEmail@adsdd",
      setting: {
        currency: 'USD'
      }
    }

    form.update(updated)
    expect(form.data.name).toEqual(updated.name)
    expect(form.data.email).toEqual(updated.email)
    expect(form.data.setting.currency).toEqual(updated.setting.currency)

    fields.update(updated)
    expect(name.value).toEqual(updated.name)
    expect(email.value).toEqual(updated.email)
    expect(currency.value).toEqual(updated.setting.currency)
    expect(fields.dirty()).toEqual(true)
  })

})

