import { defineStore } from 'pinia'

export const useRevisionsStore = defineStore('revisions', {
  state: () => ({
    record: {},
    records: [],
    fields: [],
  }),
  getters: {
  },
  actions: {

  }
})