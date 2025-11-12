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
            <label>Berechnungsperiode (Jahr / Intervall)</label>
            <b-row>
              <b-col
                cols="12"
                md="4"
              >
                <v-select
                  v-model="calculationYearOption"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="calculationYearOptions"
                  :clearable="false"
                />
              </b-col>
              <b-col
                cols="12"
                md="4"
                style="padding-left:0px;"
              >
                <v-select
                  v-model="calculationYearPeriodOption"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="calculationYearPeriodOptions"
                  :clearable="false"
                  style="max-width:120px;"
                />
              </b-col>
            </b-row>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>

    <!-- Table Container Card -->
    <b-card
      class="mb-0"
    >
      <div class="mx-2 mb-2">
        <h3>Rückzahlung Status</h3>
        <br>
        <br>
        <b-row>
          <b-col
            cols="12"
            md="3"
          >
            <b-row>
              <b-col>
                anstehende Rückzahlungen
                <h2>{{ repaymentStats.open | numFormat('0,0.00') }} EUR</h2>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col>
                Rückzahlungen gezahlt
                <h2>{{ repaymentStats.paid | numFormat('0,0.00') }} EUR</h2>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col>
                Rückzahlungssumme
                <h2>{{ repaymentStats.sum | numFormat('0,0.00') }} EUR</h2>
              </b-col>
            </b-row>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <chartjs-component-doughnut-chart
              ref="repaymentChart"
              :key="chartVersion"
              :height="200"
              :data="doughnutChart.data"
              :options="doughnutChart.options"
              class="mb-3"
            />
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
  VBTooltip,
  BCardHeader,
  BCardBody,
} from 'bootstrap-vue'
import {
  ref,
  onUnmounted,
  watch,
} from '@vue/composition-api'

import numeral from 'numeral'

import { $apiUrl } from '@serverConfig'
import vSelect from 'vue-select'
import { $themeColors } from '@themeConfig'
import store from '@/store'
import storeModule from '../storeModule'

import ChartjsComponentDoughnutChart from './chart/ChartjsComponentDoughnutChart.vue'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    //  BButton,
    vSelect,
    BCardHeader,
    BCardBody,
    ChartjsComponentDoughnutChart,
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
    filterTypeClass() {
      let style = 'd-flex align-items-center justify-content-end mb-1 mb-md-0'
      if ((this.$store.getters['app/currentBreakPoint'] === 'xs') || (this.$store.getters['app/currentBreakPoint'] === 'sm')) {
        style = 'd-flex align-items-center justify-content-start mb-1 mb-md-0'
      }

      return style
    },
  },
  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const d = new Date()
    const repaymentStats = ref({})

    const calculationYearOptions = ref(
      [
        { value: -1, label: 'Alle' },
      ],
    )

    const year = d.getFullYear()

    //  fake to 2021 for testing
    const start = 2021

    for (let i = start; i <= year; i += 1) {
      const temp = {}
      temp.value = i
      temp.label = i
      calculationYearOptions.value.push(temp)
    }

    const calculationYearPeriodOptions = ref(
      [
        { value: -1, label: 'Alle' },
        { value: 1, label: '1' },
        { value: 2, label: '2' },
        { value: 3, label: '3' },
        { value: 4, label: '4' },
      ],
    )

    const calculationYearOption = ref({ label: 'Alle', value: -1 })
    const calculationYearPeriodOption = ref({ label: 'Alle', value: -1 })

    const doughnutChart = ref({})
    const chartVersion = ref(0)

    const refetchData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchRepaymentStats`, {
          calculationYear: calculationYearOption.value.value,
          calculationYearPeriod: calculationYearPeriodOption.value.value,
          //  campaign: campaignOption.value.value,
        })
        .then(response => {
          const res = {}
          res.open = parseInt(response.data.sumopen, 10)
          res.paid = parseInt(response.data.sumrepaid, 10)
          res.sum = res.open + res.paid

          repaymentStats.value = res

          doughnutChart.value = {
            options: {
              responsive: true,
              maintainAspectRatio: false,
              responsiveAnimationDuration: 1000,
              cutoutPercentage: 50,
              legend: { display: false },
              tooltips: {
                callbacks: {
                  label(tooltipItem, data) {
                    const label = data.datasets[0].labels[tooltipItem.index] || ''
                    const value = data.datasets[0].data[tooltipItem.index]
                    const output = ` ${label} : ${numeral(value).format('0,0.00')} EUR`
                    return output
                  },
                },
                // Updated default tooltip UI
                shadowOffsetX: 1,
                shadowOffsetY: 1,
                shadowBlur: 8,
                shadowColor: $themeColors.dark,
                backgroundColor: $themeColors.light,
                titleFontColor: $themeColors.dark,
                bodyFontColor: $themeColors.dark,
              },
            },
            data: {
              datasets: [
                {
                  labels: ['Rückzahlungssumme', 'anstehende Rückzahlungen'],
                  data: [repaymentStats.value.paid, repaymentStats.value.open],
                  backgroundColor: [$themeColors.primary, $themeColors.danger],
                  borderWidth: 5,
                  pointStyle: 'rectRounded',
                },
              ],
            },
          }

          chartVersion.value += 1
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('fetchList error response')
            /* eslint-enable no-console */
          }
        })
    }

    watch([calculationYearPeriodOption, calculationYearOption], () => {
      refetchData()
    })

    //  load data
    refetchData()

    const getCurrentStatus = plant => {
      if (plant.open !== '0.0') {
        return { label: 'Rückzahlung offen', variant: 'danger' }
      }
      return { label: 'OK', variant: 'primary' }
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
      refetchData,
      getCurrentStatus,
      getPlantTitle,

      calculationYearOptions,
      calculationYearPeriodOptions,
      calculationYearOption,
      calculationYearPeriodOption,
      repaymentStats,
      doughnutChart,
      chartVersion,
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
