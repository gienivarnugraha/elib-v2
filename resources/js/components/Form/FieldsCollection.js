import each from 'lodash/each'
import cloneDeep from "lodash/cloneDeep";
import { ref, reactive, computed } from 'vue'
import Field from './Field'

class FieldsCollection {
  /**
    * Initialize new FormableFields instance
    *
    * @param  {Array} fields
    *
    * @return {Void}
    */
  constructor(fields = []) {
    this.collection = reactive([])

    if (fields.length > 0) {
      this.set(fields)
    }
  }

  /**
   * Check whether the fields are loaded
   *
   * @return {Boolean}
   */
  loaded() {
    let load = computed(() => this.collection.every(f => f.loaded === true))
    return load.value
  }

  /**
   * Check whether the fields are dirty
   *
   * @return this
   */
  set(fields) {
    fields.forEach(field => this.collection.push(new Field(field)))

    return this
  }

  /**
   * Check whether the fields are dirty
   *
   * @return {Boolean}
   */
  reset() {
    this.collection.forEach(field => field.reset())
    return this
  }

  /**
   * Check whether the fields are dirty
   *
   * @return {Boolean}
   */
  dirty() {
    return this.collection.some(f => f.isDirty())
  }

  /**
   * Fill field values by form
   *
   * @param  {} form
   *
   * @return {FieldsCollection}
   */
  fill(record) {
    this.collection.forEach(field => field.fill(record))

    return this
  }


  /**
   * Find single field
   *
   * @param  {String} attribute
   *
   * @return {Object|null}
   */
  find(attribute) {
    return this.collection.find(field => attribute === field.attribute)
  }

  /**
   * Find single field
   *
   * @param  {String} attribute
   *
   * @return {Object|null}
   */
  each(callback) {
    this.collection.forEach(callback)
    return this
  }

  /**
   * Determine if an field exists in the collection by attribute.
   *
   * @param  {[type]}  attribute [description]
   *
   * @return {Boolean}           [description]
   */
  has(attribute) {
    return Boolean(this.find(attribute))
  }

  /**
   * Update fields by attribute
   *
   * @param  {Object} record
   *
   * @return {this}
   */
  update(records) {
    Object.keys(records).forEach(record => {
      let value = records[record]

      if (typeof value === 'object') {
        this.update(value)
      } else {
        let field = this.find(record)
        field.set(value)
      }
    })

    return this
  }


  /**
   * Get fields keys/attributes
   *
   * @return {Array}
   */
  keys() {
    let result = []

    this.collection.forEach(field => result.push(field.attribute))

    return result
  }

  /**
   * Remove field from the collection
   *
   * @param  {String} attribute
   *
   * @return {boolean}
   */
  remove(attribute) {
    const index = this.collection.findIndex((field) => field.attribute === attribute)

    if (index != -1) {
      this.collection.splice(index, 1)

      return true
    }

    return false
  }

  /**
   * Get all fields
   *
   * @return {Array}
   */
  all() {
    return this.collection || []
  }
}

export default FieldsCollection
