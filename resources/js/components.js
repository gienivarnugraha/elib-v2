import Notification from 'notiwind'

import VInput from '@/components/Form/Base/VInput.vue'
import VSwitch from '@/components/Form/Base/VSwitch.vue'
import VSelect from '@/components/Form/Base/VSelect.vue'
import VPassword from './components/Form/Base/VPassword.vue'
import VAutocomplete from './components/Form/Base/VAutocomplete.vue'
import VBelongsTo from './components/Form/Base/VBelongsTo.vue'

import VPresentableColumn from './components/Table/Columns/VPresentableColumn.vue'
import VUserColumn from './components/Table/Columns/VUserColumn.vue'
import VColumn from './components/Table/Columns/VColumn.vue'
import VAvatarColumn from './components/Table/Columns/VAvatarColumn.vue'
import VBooleanColumn from './components/Table/Columns/VBooleanColumn.vue'
import VPasswordColumn from './components/Table/Columns/VPasswordColumn.vue'

import CountCard from './components/UI/Cards/CountCard.vue'
import ProgressionChart from './components/UI/Charts/ProgressionChart.vue'


import SelectRule from '@/components/Filters/Rules/SelectRule.vue'
import StaticRule from '@/components/Filters/Rules/StaticRule.vue'
import TextRule from '@/components/Filters/Rules/TextRule.vue'

/* Register global components */
export default function (app) {
  app.use(Notification)
}


/* Ensure dynamic components are imported properly without registering on global components */
export const componentMap = {
  'v-select': VSelect,
  'v-input': VInput,
  'v-password': VPassword,
  'v-autocomplete': VAutocomplete,
  'v-belongs-to': VBelongsTo,
  'v-switch': VSwitch,
}

export const filterComponentMap = {
  'v-select-rule': SelectRule,
  'v-static-rule': StaticRule,
  'v-text-rule': TextRule,
}

export const columnComponentMap = {
  'v-column': VColumn,
  'v-password-column': VPasswordColumn,
  'v-presentable-column': VPresentableColumn,
  'v-user-column': VUserColumn,
  'v-avatar-column': VAvatarColumn,
  'v-boolean-column': VBooleanColumn,
}

export const cardsComponentMap = {
  'count-card': CountCard,
  'progression-chart': ProgressionChart,
}

