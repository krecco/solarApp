<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        lg="12"
        md="12"
      >
        <b-card>
          <b-row>
            <b-col
              cols="12"
              lg="6"
              md="6"
            >
              <h3>Abrechnungsdaten</h3>
            </b-col>
            <b-col
              cols="12"
              lg="6"
              md="6"
            >
              <b-button
                variant="primary"
                size="sm"
                style="float:right;"
                @click="openRepaymentSidebar({})"
              >
                <span class="text-nowrap">Neue Abrechnung</span>
              </b-button>
            </b-col>
            <b-col
              cols="12"
              lg="12"
              md="12"
            >
              <b-table
                responsive="sm"
                :items="repaymentData"
                :fields="tableColumns"
                sticky-header="true"
                style="height:250px;"
              >
                <template #cell(repaymentPeriod)="data">
                  <b-link
                    class="font-weight-bold d-block text-nowrap"
                    @click="openRepaymentSidebar(data.item)"
                  >
                    {{ data.item.repaymentPeriod }}
                  </b-link>
                </template>
                <template #cell(powerProduction)="data">
                  {{ Number(data.item.powerProduction) | numFormat('0,0.00') }}
                </template>
                <template #cell(powerConsumption)="data">
                  {{ Number(data.item.powerConsumption) | numFormat('0,0.00') }}
                </template>
                <template #cell(consumptionTariff)="data">
                  {{ Number(data.item.consumptionTariff) | numFormat('0,0.0000') }}
                </template>
                <template #cell(productionTariff)="data">
                  {{ Number(data.item.productionTariff) | numFormat('0,0.0000') }}
                </template>
                <template #cell(productionExtraTariff)="data">
                  {{ Number(data.item.productionExtraTariff) | numFormat('0,0.0000') }}
                </template>
                <template #cell(t0)="data">
                  {{ data.item.t0 | moment("DD.MM. YYYY") }}
                </template>
              </b-table>
            </b-col>
          </b-row>
        </b-card>
      </b-col>
    </b-row>
    <repayment-sidebar
      :repayment-data-object.sync="repaymentDataObject"
      :is-add-new-repayment-active.sync="isAddNewRepaymentActive"
    />
  </div>
</template>

<script>
import {
  BCard,
  BRow,
  BCol,
  BTable,
  BButton,
  BLink,
} from 'bootstrap-vue'
import {
  ref,
  onUnmounted,
  watch,
  computed,
  //  nextTick,
} from '@vue/composition-api'
import router from '@/router'
import store from '@/store'
import storeModule from '../storeModule'
import RepaymentSidebar from './RepaymentSidebar.vue'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    BTable,
    BButton,
    BLink,
    RepaymentSidebar,
  },
  props: {
    plantTariff: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  setup(props) {
    const STORE_MODULE_NAME = 'app-power-plant-repayment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const repaymentData = ref([])
    const repaymentDataObject = ref({})
    const isAddNewRepaymentActive = ref(false)

    const openRepaymentSidebar = repayment => {
      if (typeof repayment.plantId === 'undefined') {
        repayment.plantId = router.currentRoute.params.id // eslint-disable-line no-param-reassign
        repayment.consumptionTariff = props.plantTariff.rateConsumption // eslint-disable-line no-param-reassign
        repayment.productionTariff = props.plantTariff.rateExcessProduction // eslint-disable-line no-param-reassign
        repayment.productionExtraTariff = props.plantTariff.rateExcessProductionExternal // eslint-disable-line no-param-reassign
      }
      repaymentDataObject.value = repayment
      console.log('aaa', repaymentDataObject.value)
      isAddNewRepaymentActive.value = true
    }

    const fetchRepaymentData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchRepaymentData`, { id: router.currentRoute.params.id })
        .then(response => {
          repaymentData.value = response.data
        })
    }

    //  load data
    fetchRepaymentData()

    const repaymentDataUpdatedAt = computed(() => store.getters[`${STORE_MODULE_NAME}/repaymentDataUpdatedAt`])
    watch(repaymentDataUpdatedAt, (val, oldVal) => {
      if (val > oldVal) {
        fetchRepaymentData()
      }
    })

    const tableColumns = [
      { key: 'repaymentPeriod', label: 'Berechnungsperiode' },
      { key: 'powerProduction', label: 'Stromertrag kWh/a' },
      { key: 'powerConsumption', label: 'Eigenverbrauch kWh/a' },
      { key: 'consumptionTariff', label: 'Tarif(e) Eigenverbrauch' },
      { key: 'productionTariff', label: 'Tarif(e) Überschussstrom' },
      { key: 'productionExtraTariff', label: 'Tarif(e) PV Überschuß' },
      { key: 't0', label: 'Datum', thStyle: { width: '150px !important' } },
    ]

    return {
      repaymentData,
      repaymentDataObject,
      tableColumns,
      isAddNewRepaymentActive,
      openRepaymentSidebar,
    }
  },
}
</script>
