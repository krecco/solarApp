<template>
  <div>
    <!-- Add new item component -->
    <add-new
      :is-add-new-active.sync="isAddNewActive"
      @refetch-data="refetchData"
    />

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
    >
      <div class="m-2">
        <!-- Table Top -->
        <b-row>
          <!-- Per Page -->
          <b-col
            cols="12"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>Show</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>entries</label>
          </b-col>
          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Search..."
              />
              <b-button
                variant="primary"
                @click="isAddNewActive = true"
              >
                <span class="text-nowrap">Add New</span>
              </b-button>
            </div>
          </b-col>
        </b-row>
      </div>
      <b-table
        ref="tableRef"
        :items="tableData"
        :fields="tableColumns"
        responsive
        primary-key="id"
        class="position-relative"
        show-empty
        empty-text="No matching records found"
      >
        <template #cell(t0)="data">
            {{ data.item.t0 | moment("DD.MM. YYYY") }}
        </template>
        <!-- Column: Actions -->
        <template #cell(actions)="data">
          <b-dropdown
            variant="link"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >

            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>
            <b-dropdown-item :to="{ name: 'generic-detail', params: { id: data.item.id } }">
              <feather-icon icon="FileTextIcon" />
              <span class="align-middle ml-50">Details</span>
            </b-dropdown-item>

            <b-dropdown-item :to="{ name: 'generic-edit', params: { id: data.item.id } }">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Edit</span>
            </b-dropdown-item>
          </b-dropdown>
        </template>
      </b-table>

       <div class="mx-2 mb-2">
        <b-row>
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">Showing {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} entries</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >
            <b-pagination
              v-model="currentPage"
              :total-rows="totalRecords"
              :per-page="perPage"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
              prev-class="prev-item"
              next-class="next-item"
            >
              <template #prev-text>
                <feather-icon
                  icon="ChevronLeftIcon"
                  size="18"
                />
              </template>
              <template #next-text>
                <feather-icon
                  icon="ChevronRightIcon"
                  size="18"
                />
              </template>
            </b-pagination>
          </b-col>
        </b-row>
       </div>

    </b-card>
  </div>
</template>

<script>
import {
  BCard,
  BRow,
  BCol,
  BButton,
  BFormInput,
  BTable,
  BDropdown,
  BDropdownItem,
  BPagination,
} from 'bootstrap-vue'

import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'

import vSelect from 'vue-select'
import store from '@/store'
import storeModule from '../storeModule'
import AddNew from './AddNew.vue'

export default {
  components: {
    AddNew,

    BCard,
    BRow,
    BCol,
    BButton,
    BFormInput,
    BTable,
    BDropdown,
    BDropdownItem,
    BPagination,
    vSelect,
  },
  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-generic'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const tableData = ref([])
    const perPage = ref(10)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const isAddNewActive = ref(false)
    const searchQuery = ref('')
    const totalRecords = ref(0)
    const sortBy = ref('t0')
    const isSortDirDesc = ref(true)
    const tableRef = ref(null)

    const dataMeta = computed(() => {
      const localItemsCount = tableRef.value ? tableRef.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalRecords.value,
      }
    })

    const tableColumns = [
      { key: 'title', label: 'Title' },
      { key: 'powerProductionForecast', label: 'Power Production' },
      { key: 'unitPrice', label: 'Value' },
      { key: 't0', label: 'Date' },
      { key: 'actions' },
    ]

    const refetchData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchDemoList`, {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
        })
        .then(response => {
          console.log(response)
          tableData.value = response.data

          //  todo prepare responmse to return data and total data nr from db
          totalRecords.value = 32
        })
        .catch(error => {
          if (error.status === 404) {
            console.log('loadfiles error response')
          }
        })
    }

    watch([currentPage, perPage, searchQuery], () => {
      refetchData()
    })

    //  load data
    refetchData()

    return {
      perPage,
      currentPage,
      perPageOptions,
      isAddNewActive,
      searchQuery,
      tableColumns,
      tableData,
      totalRecords,
      tableRef,
      dataMeta,
      refetchData,
    }
  },
}
</script>

<style lang="scss" scoped>
  .per-page-selector {
    width: 90px;
  }
</style>

<style lang="scss">
  @import '@core/scss/vue/libs/vue-select.scss';
</style>
