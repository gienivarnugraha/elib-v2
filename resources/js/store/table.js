import set from 'lodash/set'

export const tableGetters = {
  getRows: (state) => state.table.rows,
  getColumns: (state) => state.table.columns,
}

export const tableState = {
  table: {
    identifier: '',
    columns: [],
    rows: [],
    needFetch: true,
    pagination: {
      isLastPage: false,
      isFirstPage: false,
      total: 0,
      from: 0,
      allTimeTotal: 0,
      to: 0
    },
    query: {
      q: '',
      page: 1,
      per_page: 10,
      order: [],
      rules: [],
      // search_match: '' // and | or
      // order: {},
    }
  }
}

export const tableActions = {
  findColumn(state, col) {
    return state.table.columns.find(_col => {
      if (_col.relationField) {
        return _col.relationField === col.relationField && _col.relationname === col.relationname
      } else {
        return _col.title === col.title
      }
    })
  },

  /**
   * Reset the record object in the given state.
   *
   * @param {Object} state - The state object.
   * @return {Object} The updated record object.
   */
  resetRecord(state) {
    let record = {}

    state.fields.forEach(field => {
      if (field.belongsToRelation) {
        set(record, `${field.belongsToRelation}.${field.attribute}`, '')
      } else if (field.morphManyRelationship) {
        set(record, `${field.morphManyRelationship}.${field.attribute}`, '')
      } else if (field.hasOneRelation) {
        set(record, `${field.hasOneRelation}.${field.attribute}`, '')
      } else {
        set(record, `${field.attribute}`, '')
      }
    })

    state.record = record

    return state.record
  },
}