import cloneDeep from "lodash/cloneDeep";
import isEqual from "lodash/isEqual";

import { ref, reactive } from 'vue'

export default class Field {

  /**
   * Initializes a new instance of the constructor.
   *
   * @param {type} field - the field to assign to the instance
   * @return {undefined} 
   */
  constructor(field) {
    Object.assign(this, field);
    this.originalValue = undefined
    this.loaded = ref(false)
  }

  /**
   * Fills the record with a value.
   *
   * @param {Object} record - The record to be filled.
   * @return {undefined} This function does not return a value.
   */
  fill(record) {
    let value = this.extractValue(this, record)
    this.value = value
    this.originalValue = cloneDeep(value)
    this.loaded = true
  }

  /**
   * Sets the value of the object.
   *
   * @param {type} value - The value to set.
   * @return {undefined} - Returns nothing.
   */
  set(value) {
    this.value = value;
  }

  /**
   * Resets the value of the object to its original value.
   *
   * @return {undefined}
   */
  reset() {
    this.value = cloneDeep(this.originalValue);
  }

  /**
   * Check if the current value of the object is different from its original value.
   *
   * @return {boolean} Returns true if the value is dirty, false otherwise.
   */
  isDirty() {
    return !isEqual(this.value, this.originalValue)
  }

  /**
   * @private
   *
   * Get the field value from the record
   *
   * @param  {Object} field
   * @return {mixed}
   */
  extractValue(field, record) {
    /*   if (field.morphManyRelationship) {
       return record[field.morphManyRelationship]
     } else */
    if (field.belongsToRelation) {
      return record[field.belongsToRelation]
    } else if (field.hasOneRelation && record[field.hasOneRelation]) {
      return record[field.hasOneRelation][field.attribute]
    } else {
      return record[field.attribute]
    }
  }
}