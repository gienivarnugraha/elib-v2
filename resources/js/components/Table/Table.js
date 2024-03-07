import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import qs from 'qs'
import { findStore } from '@/store'

export default class Table {
  constructor(resource) {
    this.resource = resource

    this.store = findStore(this.resource)

    this.pagination = this.store.table.pagination

    this.query = this.store.table.query

    this.filters = []

    this.fetchingRows = ref(false)
    this.fetchingColumns = ref(false)

    this.watchQuery = watch(this.query, async (val) => {
      await nextTick()
      this.setRows(await this.fetchRows())
    })

  }

  /**
   * Initializes the function by fetching columns and rows asynchronously using Promise.all.
   * Sets the fetched rows using this.setRows() and the fetched columns using nextTick() and this.setColumns().
   * 
   * @return {undefined} No return value.
   */
  init() {
    Promise.all([this.fetchColumns(), this.fetchRows()]).then(([colResponse, rowResponse]) => {
      this.setRows(rowResponse)

      nextTick(() => {
        this.setColumns(colResponse)
      })
    })
  }

  refresh() {
    this.setRows([])

    this.fetchRows().then((data) => {
      this.setRows(data)
    })
  }
  unMounted() {
    onUnmounted(() => {
      this.watchQuery()
    })
  }

  loading = () => computed(() => this.fetchingColumns && this.fetchingRows)

  paginationMeta = () => computed(() => `Showing ${this.pagination.from} - ${this.pagination.to} of ${this.pagination.total} from ${this.pagination.allTimeTotal}`)

  pageMeta = () => computed(() => `Page ${this.pagination.currentPage} of ${this.pagination.lastPage}`)

  goToNextPage = () => this.query.page++

  goToPreviousPage = () => this.query.page--

  columnFormatter(row, col) {
    if (row.displayAs[col.attribute]) {
      return row.displayAs[col.attribute]
    } else if (col.relationField) {
      return get(row, `${col.attribute}.${col.relationField}`)
    } else {
      return row[col.attribute]
    }
  }

  /**
   * Check if the given column is the one used for sorting.
   *
   * @param {type} col - the column to check for sorting
   * @return {type} boolean - true if the column is used for sorting, false otherwise
   */
  isSortedBy = (col) => this.store.table.query.order.attribute === col.title


  headerClasses = (col) => col.primary ? 'table-sticky-column table-sticky-header' : ''

  colClasses = (col) => col.primary ? 'table-sticky-column' : ''

  async fetchColumns() {
    if (this.store.table.columns.length !== 0) {
      return this.store.table.columns
    }

    this.fetchingColumns.value = true

    const { data } = await Application.request(`/api/${this.resource}/table/settings`)

    const columns = data.columns.filter((_col) => !_col.hidden)
      .map((_col, idx) => {
        return reactive({
          title: _col.attribute,
          name: _col.label,
          component: _col.component,
          isSortable: _col.sortable,
          relationField: _col.relationField,
          headerClasses: this.headerClasses(_col),
          classes: this.colClasses(_col),
          sortBy: undefined,
          meta: _col.meta,
          formatter: row => this.columnFormatter(row, _col)
        })
      })

    this.filters = data.filters

    this.fetchingColumns.value = false

    return columns


  }

  async fetchRows() {
    this.fetchingRows.value = true

    const { order: orderQuery, ...restPageData } = this.query

    let order = orderQuery.map(col => col.join('|')).join(',')

    const queryString = qs.stringify({ ...restPageData, order })

    const { data } = await Application.request(`/api/${this.resource}/table?${queryString}`)

    const { data: rows, meta } = data

    this.store.$patch((state) => {
      state.table.pagination.isLastPage = meta.current_page === meta.last_page
      state.table.pagination.isFirstPage = meta.current_page === 1
      state.table.pagination.currentPage = meta.current_page
      state.table.pagination.lastPage = meta.last_page
      state.table.pagination.total = meta.total
      state.table.pagination.from = meta.from
      state.table.pagination.to = meta.to
      state.table.pagination.allTimeTotal = meta.all_time_total
    })

    this.fetchingRows.value = false
    return rows
  }

  setRows(rows) {
    this.store.$state.table.rows = rows
    return this
  }

  setColumns(columns) {
    this.store.$state.table.columns = columns
    return this
  }

  getRows() {
    return this.store.getRows
  }

  getColumns() {
    return this.store.getColumns
  }

  sortColumn(col) {

    if (col.isSortable === false) return

    // ! MULTIPLE ORDER
    const tableCol = this.store.findCol(col)

    let index = this.query.order.findIndex((order) => order[0] === tableCol.title)

    if (tableCol.sortBy === undefined) tableCol.sortBy = 'asc'

    else if (tableCol.sortBy === 'asc') tableCol.sortBy = 'desc'

    else tableCol.sortBy = undefined

    if (tableCol.sortBy === undefined) this.query.order.splice(index, 1)

    else this.query.order.splice(index, index === -1 ? 0 : 1, [tableCol.title, tableCol.sortBy])

  }

}