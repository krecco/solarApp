<template>
  <div>
    <!--  Filters -->
    <b-card no-body>
      <b-card-header class="pb-50">
        <h5>
          Filter
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            md="4"
            class="mb-md-0 mb-2"
          >
            <label>Status</label>
            <v-select
              v-model="statusOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="statusOptions"
              :clearable="false"
              class="w-100"
            />
          </b-col>
          <!--
          <b-col
            cols="12"
            md="4"
            class="mb-md-0 mb-2"
          >
            <label>Kampagne</label>
            <v-select
              v-model="campaignOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="campaignDropdown"
              :clearable="false"
              class="w-100"
            />
          </b-col>
          <b-col
            cols="12"
            md="4"
            class="mb-md-0 mb-2"
          >
            <label>Tarif</label>
            <v-select
              v-model="tariffOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="tariffDropdown"
              :clearable="false"
              class="w-100"
            />
          </b-col>
          -->
        </b-row>
      </b-card-body>
    </b-card>

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
            md="5"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>Einträge anzeigen</label>
          </b-col>
          <b-col
            cols="12"
            md="3"
            :class="filterTypeClass()"
          >
            <!--
            <label>Status</label>
            <v-select
              v-model="statusOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="statusOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
              style="min-width:300px !important;"
            />
            -->
          </b-col>
          <!-- Search -->
          <b-col
            cols="12"
            md="4"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block"
                placeholder="Suchen..."
              />
              <!--
              <b-button
                variant="primary"
                @click="isAddNewActive = true"
              >
                <span class="text-nowrap">Add New</span>
              </b-button>
              -->
            </div>
          </b-col>
        </b-row>
        <!--
        test
        <b-row
          style="padding-top:10px; padding-bottom:10px;"
        >
          <b-col
            cols="12"
            md="4"
          >
            <label>Status</label>
            <v-select
              v-model="statusOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="statusOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
              style="min-width:300px !important;"
            />
          </b-col>
          <b-col
            cols="12"
            md="4"
          >
            <label>Status</label>
            <v-select
              v-model="statusOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="statusOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
              style="min-width:300px !important;"
            />
          </b-col>
          <b-col
            cols="12"
            md="4"
          >
            <label>Status</label>
            <v-select
              v-model="statusOption"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="statusOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
              style="min-width:300px !important;"
            />
          </b-col>
        </b-row>
        -->
      </div>
      <b-row>
        <b-col
          style="text-align:right; padding-right:30px;"
        >
          <!--
          <b-button
            variant="flat-primary"
          >
            <a
              v-auth-href
              :href="`${apiUrl}/solar-plant/list-csv?q=${searchQuery}&perPage=1&page=0&sortBy=t0&sortDesc=true&status=${statusOption.value}`"
              target="_blank"
            >
              CSV exportieren
            </a>
          </b-button>
          -->
          <!--
          <b-button
            variant="flat-primary"
            @click="downloadCsv"
          >
            CSV exportieren
          </b-button>
          -->
        </b-col>
      </b-row>
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
        <template #cell(status)="data">
          <b-badge
            pill
            :variant="getCurrentStatus(data.item).variant"
          >
            {{ getCurrentStatus(data.item).label }}
          </b-badge>
        </template>

         <template #cell(avatar)="data">
          <b-avatar
            :text="avatarText(fullName(data.item.lastname,data.item.firstname))"
            variant="success"
          />
          <b-link
            :to="{ name: 'user-detail', params: { id: data.item.id } }"
            class="font-weight-bold d-block text-nowrap"
          />
        </template>
        <template #cell(client)="data">
          <b-link
            :to="{ name: 'user-detail', params: { id: data.item.userid } }"
            class="font-weight-bold d-block text-nowrap"
          >
            {{ data.item.lastname }} {{ data.item.firstname }}
            <br>
            {{ data.item.email }}
          </b-link>
        </template>
        <template #cell(amount)="data">
          <b-link
            :to="{ name: 'investment-detail', params: { id: data.item.id } }"
            class="font-weight-bold d-block text-nowrap"
          >
            {{ Number(data.item.amount) | numFormat('0,0.00') }}
          </b-link>
        </template>
        <template #cell(duration)="data">
          {{ Number(data.item.duration) | numFormat('0') }} Jahren
        </template>
        <template #cell(interestrate)="data">
          {{ Number(data.item.interestrate) | numFormat('0,0.00') }}% / Jahr
        </template>

        <!--
        <template #cell(title)="data">
          <b-link
            :to="{ name: 'power-plant-detail', params: { id: data.item.id } }"
            class="font-weight-bold d-block text-nowrap"
          >
            {{ getPlantTitle(data.item.title, 'title') }}
          </b-link>
          {{ getPlantTitle(data.item.title, 'person') }}
        </template>
        <template #cell(nominalpower)="data">
          {{ Number(data.item.nominalpower) | numFormat('0,0.00') }}
        </template>
        <template #cell(t0)="data">
          {{ data.item.t0 | moment("DD.MM. YYYY") }}
        </template>
        <template #cell(symbols)="data">
          <b-avatar
            v-if="data.item.inspectionenergycomunity === 'true'"
            v-b-tooltip.hover.top="'Teilnehmer Energiegemeinschaft'"
            variant="light-info"
            style="margin-left:5px;"
          >
            <feather-icon icon="UsersIcon" />
          </b-avatar>
          <b-avatar
            v-if="data.item.inspectionwaterheating === 'true'"
            v-b-tooltip.hover.top="'Warmwasserbereitung'"
            variant="light-info"
            style="margin-left:5px;"
          >
            <feather-icon icon="DropletIcon" />
          </b-avatar>
          <b-avatar
            v-if="data.item.inspectionstorage === 'true'"
            v-b-tooltip.hover.top="'Speicher'"
            variant="light-info"
            style="margin-left:5px;"
          >
            <feather-icon icon="DatabaseIcon" />
          </b-avatar>
          <b-avatar
            v-if="data.item.inspectionemergencypower === 'true'"
            v-b-tooltip.hover.top="'Notstromfunktionalität'"
            variant="light-info"
            style="margin-left:5px;"
          >
            <feather-icon icon="BatteryIcon" />
          </b-avatar>
          <b-avatar
            v-if="data.item.inspectioncharginginfrastructure === 'true'"
            v-b-tooltip.hover.top="'Ladeinfrastruktur'"
            variant="light-info"
            style="margin-left:5px;"
          >
            <feather-icon icon="ZapIcon" />
          </b-avatar>
        </template>
        -->
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
            <b-dropdown-item :to="{ name: 'investment-detail', params: { id: data.item.id } }">
              <feather-icon icon="FileTextIcon" />
              <span class="align-middle ml-50">Mehr</span>
            </b-dropdown-item>

            <b-dropdown-item @click="deleteInvestment(data.item.id)">
              <feather-icon icon="XCircleIcon" />
              <span class="align-middle ml-50">Investition Löschen</span>
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
            <span class="text-muted">{{ dataMeta.from }} bis {{ dataMeta.to }} von {{ dataMeta.of }} Einträge</span>
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
  //  BButton,
  BFormInput,
  BTable,
  BDropdown,
  BDropdownItem,
  BPagination,
  BLink,
  BBadge,
  BAvatar,
  VBTooltip,
  BCardHeader,
  BCardBody,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'

import { createBlob } from 'blob-util'
import moment from 'moment'

import { $apiUrl } from '@serverConfig'
import vSelect from 'vue-select'
import store from '@/store'
import storeModule from '../storeModule'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    //  BButton,
    BFormInput,
    BTable,
    BDropdown,
    BDropdownItem,
    BPagination,
    vSelect,
    BLink,
    BBadge,
    BAvatar,
    BCardHeader,
    BCardBody,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      apiUrl: $apiUrl,
    }
  },
  methods: {
    fullName(lastName, firstName) {
      return `${lastName} ${firstName}`
    },
    filterTypeClass() {
      let style = 'd-flex align-items-center justify-content-end mb-1 mb-md-0'
      if ((this.$store.getters['app/currentBreakPoint'] === 'xs') || (this.$store.getters['app/currentBreakPoint'] === 'sm')) {
        style = 'd-flex align-items-center justify-content-start mb-1 mb-md-0'
      }

      return style
    },
    deleteInvestment(investmentId) {
      this.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie die PV-Anlage löschen möchten.', {
          title: 'PV-Anlage löschen?',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'Ja',
          cancelTitle: 'Nein',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            store.dispatch('app-investment/deleteInvestment', { investmentId })
              .then(response => {
                if (response.data.status === 200) {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Daten wurden gelöscht',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                  this.refetchData()
                } else {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Daten wurden nicht gelöscht!',
                      text: response.data.payload.message,
                      icon: 'AlertTriangleIcon',
                      variant: 'warning',
                    },
                  })
                }
              })
              .catch(() => {
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Fehler bei der Verbindung zum Server',
                    icon: 'AlertTriangleIcon',
                    variant: 'warning',
                  },
                })
              })
          }
        })
    },

    customerCancelPlant(plant) {
      this.$bvModal
        .msgBoxConfirm('Kundenstorno bestätigen!', {
          title: 'Kundenstorno?',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'Ja',
          cancelTitle: 'Nein',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            store.dispatch('app-power-plant/simpleStatusUpdate', { id: plant.id, status: 'cancelByCustomer', value: true })
              .then(response => {
                if (response.data.status === 200) {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'OK',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                  this.refetchData()
                } else {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Fehler',
                      text: response.data.payload.message,
                      icon: 'AlertTriangleIcon',
                      variant: 'warning',
                    },
                  })
                }
              })
              .catch(() => {
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Fehler bei der Verbindung zum Server',
                    icon: 'AlertTriangleIcon',
                    variant: 'warning',
                  },
                })
              })
          }
        })
    },
  },
  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-investment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const tableData = ref(null)
    const perPage = ref(10)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const isAddNewActive = ref(false)
    const isCloneSolarActive = ref(false)
    const searchQuery = ref('')
    const totalRecords = ref(0)
    const sortBy = ref('t0')
    const isSortDirDesc = ref(true)
    const tableRef = ref(null)
    const solarPlant = ref({})
    const tariffDropdown = ref([])
    const campaignDropdown = ref([])

    //  needs to be mapped
    const statusOptions = ref(
      [
        { value: -1, label: 'Alle' },
        { value: 0, label: 'Neu' },
        { value: 1, label: 'Vertrag abgeschlossen' },
      ],
    )
    const statusOption = ref({ label: 'Alle', value: -1 })
    const tariffOption = ref({ label: 'Alle', value: '77399e89-79f4-470b-8eec-34e13ab9d74b' })
    const campaignOption = ref({ label: 'Alle', value: '77399e89-79f4-470b-8eec-34e13ab9d74b' })

    const dataMeta = computed(() => {
      const localItemsCount = tableRef.value ? tableRef.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalRecords.value,
      }
    })

    const tableColumns = [
      { key: 'status', label: '', thStyle: { width: '60px' } },
      { key: 'avatar', label: '', thStyle: { width: '60px' } },
      { key: 'client', label: 'Kunde' },
      { key: 'amount', label: 'Investition' },
      { key: 'duration', label: 'Dauer' },
      { key: 'interestrate', label: 'Zinsen' },

      /*
      { key: 'status', label: 'Status', thStyle: { width: '50px !important' } },
      { key: 'title', label: 'Titel' },
      { key: 'campaigntitle', label: 'Kampagne' },
      { key: 'nominalpower', label: 'EL' },
      { key: 'tarifftitle', label: 'Tarif' },
      { key: 't0', label: 'Datum', thStyle: { width: '150px !important' } },
      { key: 'symbols', label: '' },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
      */
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
    ]

    const refetchData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchList`, {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
          status: statusOption.value.value,
        })
        .then(response => {
          tableData.value = response.data.payload
          totalRecords.value = response.data.records
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('fetchList error response')
            /* eslint-enable no-console */
          }
        })
    }

    const downloadCsv = () => {
      console.log('download csv')
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchListCsv`, {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
          status: statusOption.value.value,
          tariff: tariffOption.value.value,
          campaign: campaignOption.value.value,
        })
        .then(response => {
          const d = new Date()
          const blob = createBlob([response.data], 'text/csv')
          const fileURL = URL.createObjectURL(blob)
          const anchor = document.createElement('a')
          anchor.href = fileURL
          anchor.target = '_blank'
          anchor.download = `${moment(d).format('YYYY_MM_DD_hhmmss')}__PV_anlage_list.csv`
          anchor.click()
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('fetchList error response')
            /* eslint-enable no-console */
          }
        })
    }

    watch([currentPage], () => {
      refetchData()
    })

    watch([perPage, searchQuery, statusOption, tariffOption, campaignOption], () => {
      if (currentPage.value > 0) {
        currentPage.value = 0
      } else {
        refetchData()
      }
    })

    //  load data
    refetchData()

    /*
    store
      .dispatch(`${STORE_MODULE_NAME}/getCampaignDropdown`, {})
      .then(response => {
        campaignDropdown.value = response.data
      })
      .catch(error => {
        if (error.status === 404) {
          //  console.log('getCampaignDropdown error response')
        }
      })

    store
      .dispatch(`${STORE_MODULE_NAME}/getTariffDropdown`, {})
      .then(response => {
        tariffDropdown.value = response.data
      })
      .catch(error => {
        if (error.status === 404) {
          //  console.log('getTariffDropdown error response')
        }
      })
    */

    const getCurrentStatus = investment => {
      if (investment.contractfinalized === 'true') {
        return { label: 'Vertrag abgeschlossen', variant: 'primary' }
      }

      return { label: 'Neu', variant: 'light-danger' }
    }

    const getPlantTitle = (titleString, what) => {
      const titleArray = titleString.split(',')

      if (what === 'title') {
        return `${titleArray[0] ? titleArray[0] : ''}, ${titleArray[1] ? titleArray[1] : ''}`
      }

      if (what === 'person') {
        return titleArray[2] ? titleArray[2] : ''
      }

      return ''
    }

    return {
      perPage,
      currentPage,
      perPageOptions,
      isAddNewActive,
      isCloneSolarActive,
      searchQuery,
      tableColumns,
      tableData,
      totalRecords,
      tableRef,
      dataMeta,
      refetchData,
      solarPlant,
      statusOptions,
      statusOption,
      getCurrentStatus,
      getPlantTitle,
      downloadCsv,
      campaignDropdown,
      tariffDropdown,
      tariffOption,
      campaignOption,
      avatarText,
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
