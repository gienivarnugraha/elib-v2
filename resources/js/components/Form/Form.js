import Errors from './Errors'
import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
// import { isFile } from './utils'
import { objectToFormData } from './composables/FormData'

import { ref, reactive } from 'vue'
import isEmpty from 'lodash/isEmpty'
import isObject from 'lodash/isObject'
import set from 'lodash/set'

const defaultClearDelay = 1000


class Form {
  /**
   * Create a new form instance.
   *
   * @param {Object} data
   */
  constructor(data = {}) {
    this.busy = ref(false)
    this.loaded = ref(false)
    this.successful = ref(false)
    this.recentlySuccessful = ref(false)
    this.queryString = {}
    this.errors = new Errors()
    this.originalData = cloneDeep(data)
    this.data = ref({})

    if (Object.keys(data).length > 0) {
      this.set(data)
    }
  }

  /**
   * Set initial form data/attribute.
   * E.q. value can be used when resetting the form
   *
   * @param {String} attribute
   * @param {Mixed} value
   */
  set(record) {
    this.data = record
    this.originalData = cloneDeep(record)
    this.loaded.value = true

    return this
  }

  /**
   * Fill form data/attribute.
   *
   * @param {String} attribute
   * @param {Mixed} value
   */
  fill(field, val) {
    /*  if (field.morphManyRelationship ) {
      return this.data[field.morphManyRelationship] = val
    } else */
    if (field.belongsToRelation) {
      // this.data[field.belongsToRelation] = val
      return this.data[field.attribute] = val[field.valueKey]
    } else if (field.hasOneRelation && this.data[field.hasOneRelation]) {
      return this.data[field.hasOneRelation][field.attribute] = val
    } else {
      return this.data[field.attribute] = val
    }
  }

  /**
   * Add form query string
   *
   * @param {Object} values
   */
  withQueryString(values) {
    this.queryString = { ...this.queryString, ...values }
  }

  /**
   * Start processing the form.
   */
  startProcessing() {
    this.errors.clear()
    this.busy.value = true
    this.successful.value = false
  }

  /**
   * Finish processing the form.
   */
  finishProcessing() {
    this.busy.value = false
    this.successful.value = true
    this.recentlySuccessful.value = true

    setTimeout(() => (this.recentlySuccessful.value = false), defaultClearDelay)
  }

  /**
   * Clear the form errors.
   */
  clear() {
    this.errors.clear()
    this.successful.value = false
  }

  /**
   * Update fields by attribute
   *
   * @param  {Object} record
   *
   * @return {this}
   */
  update(record) {
    Object.keys(record).forEach(key => this.data[key] = record[key])

    return this
  }


  /**
   * Submit the form via a DELETE request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  keys(data) {
    return Object.keys(data).filter(key => !Form.ignore.includes(key))
  }

  /**
   * Submit the form via a DELETE request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  dirty(data, ori) {
    let dirty = {}

    data = data || this.data
    ori = ori || this.originalData

    this.keys(data).forEach((key) => {
      if (isObject(data[key])) {
        let obj = this.dirty(data[key], ori[key])

        if (this.keys(obj).length > 0) {
          Object.assign(dirty, { [key]: obj })
        }

        if (this.isFile(data[key])) {
          Object.assign(dirty, { [key]: data[key] })
        }

      } else if (data[key] !== ori[key]) {
        Object.assign(dirty, { [key]: data[key] })
      }

    })

    return dirty
  }

  /**
   * Reset the form fields.
   */
  reset() {
    this.keys(this.data).forEach(k => {
      this.data[k] = cloneDeep(this.originalData[k])
    })
  }

  /**
   * Submit the form via a GET request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  get(url, config = {}) {
    return this.submit('get', url, config)
  }

  /**
   * Submit the form via a POST request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  post(url, config = {}) {
    return this.submit('post', url, config)
  }

  /**
   * Submit the form via a PUT request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  put(url, config = {}) {
    return this.submit('put', url, config)
  }

  /**
   * Submit the form via a DELETE request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  delete(url, config = {}) {
    return this.submit('delete', url, config)
  }

  /**
   * Submit the form data via an HTTP request.
   *
   * @param  {String} method (get, post, patch, put)
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  submit(method, url, config = {}) {
    this.startProcessing()

    let urlData = this.createUriData(url)

    let data =
      method === 'get'
        ? {
          params: merge(urlData.queryString, this.data),
        }
        : method === 'put' ? this.dirty() : this.data

    /* if (this.hasFiles()) {
      merge(config, { headers: { "Content-Type": "multipart/form-data" } })
      data = objectToFormData(this.dirty())
      method === 'put' ? data.append('_method', 'put') : data.append('_method', 'post')
    }
 */
    return new Promise((resolve, reject) => {
      Application.request()
      [method](
        urlData.uri,
        data,
        merge(
          {
            params: urlData.queryString,
          },
          config
        )
      )
        .then(response => {
          this.finishProcessing()

          resolve(response.data)
        })
        .catch(error => {
          this.busy.value = false
          if (error.response) {
            this.errors.set(this.errors.extractErrors(error.response))

            // setTimeout(() => this.errors.clear(), defaultClearDelay)
          }

          reject(error)
        })
    })
  }

  /**
   * Get a named route.
   *
   * @param  {String} url
   *
   * @return {Object}
   */
  createUriData(url) {
    let urlArray = url.split('?')
    let params = urlArray[1]
      ? Object.fromEntries(new URLSearchParams(urlArray[1]))
      : {}

    return {
      uri: urlArray[0],
      queryString: merge(params, this.queryString),
    }
  }

  /**
   * Clear errors on keydown.
   *
   * @param {KeyboardEvent} event
   */
  onKeydown(event) {
    console.log(this.errors.has(event));
    if (this.errors.has(event)) {
      this.errors.clear(event)
      return
    }

    /*   if (event.target.name) {
        this.errors.clear(event.target.name)
      } else if (event.target.id) {
        this.errors.clear(event.target.id)
      } */
  }

  hasFiles() {
    for (const property in this.originalData) {
      if (this.hasFilesDeep(this.data[property])) {
        return true
      }
    }

    return false
  }

  hasFilesDeep(object) {
    if (object === null) {
      return false
    }

    if (typeof object === 'object') {
      for (const key in object) {
        if (object.hasOwnProperty(key)) {
          if (this.hasFilesDeep(object[key])) {
            return true
          }
        }
      }
    }

    if (Array.isArray(object)) {
      for (const key in object) {
        if (object.hasOwnProperty(key)) {
          return this.hasFilesDeep(object[key])
        }
      }
    }

    return this.isFile(object)
  }

  isFile(object) {
    return object instanceof File || object instanceof FileList
  }
}

Form.ignore = [
  'authorizations',
  'display_name',
  'path',
]


export default Form
