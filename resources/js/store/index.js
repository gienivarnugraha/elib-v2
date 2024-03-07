import { createPinia } from 'pinia'
import router from '@/router'
import set from 'lodash/set'
import { useUsersStore } from './resources/users';
import { useAircraftStore } from './resources/aircraft';
import { useDocumentsStore } from './resources/documents';
import { useManualsStore } from './resources/manuals';
import { useRevisionsStore } from './resources/revisions';
import { useOrdersStore } from './resources/orders';

const pinia = createPinia()

pinia.use(({ store }) => { store.router = markRaw(router) });

export default pinia

const storeMap = {
  'users': useUsersStore,
  'aircraft': useAircraftStore,
  'documents': useDocumentsStore,
  'manuals': useManualsStore,
  'revisions': useRevisionsStore,
  'orders': useOrdersStore,
}

export const findStore = (name) => {
  try {
    return storeMap[name]()
  } catch (error) {
    console.error(`store ${name} not found, please put in the store map in store/index.js `);
  }
}