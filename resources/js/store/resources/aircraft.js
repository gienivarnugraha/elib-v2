import { defineStore } from 'pinia'
import deepClone from 'lodash/cloneDeep'

import { tableState, tableGetters, tableActions } from '../table'

const { resetRecord: baseReset, findColumn: baseFind } = deepClone(tableActions)

export const useAircraftStore = defineStore('aircraft', {
    state: () => ({
        record: {},
        records: [],
        fields: [],
        ...deepClone(tableState)
    }),
    getters: {
        ...deepClone(tableGetters)
    },
    actions: {
        findCol(col){
           return baseFind(this, col)
        },
        resetRecord() {    
            return baseReset(this)
        }
    }
})