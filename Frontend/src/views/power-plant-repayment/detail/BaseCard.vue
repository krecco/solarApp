<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        lg="4"
        md="12"
      >
        <b-card>
          <h3>
            <feather-icon
              icon="MapPinIcon"
              size="20"
            />&nbsp;
            <a
              :href="plantTitle.googleLink"
              target="_blank"
            >{{ plantTitle.title }}</a>
          </h3>
          <h3>
            <feather-icon
              icon="UserIcon"
              size="20"
            />&nbsp;
            {{ plantTitle.person }}
          </h3>
          <h3>
            <feather-icon
              icon="PhoneIcon"
              size="20"
            />&nbsp;
            {{ (plantUser.phone || 'N/A') }}
          </h3>
          <br>
          <b-row
            class="match-height"
          >
            <b-col
              cols="12"
              md="12"
            >
              <b-row>
                <b-col>
                  Kampagne
                  <h2>{{ plantCampaign.title }}</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Photovoltaik-Anlagen-Engpassleistung
                  <h2>{{ baseData.nominalPower | numFormat('0.00') }}  kWp</h2>
                </b-col>
              </b-row>
            </b-col>
            <b-col
              v-if="plantTariff.title != ''"
              cols="12"
              md="12"
            >
              <br>
              <b-row>
                <b-col>
                  Preis Photovoltaik-Anlage inkl. Ust.
                  <h2>{{ baseData.unitPrice | numFormat('0,0.00') }} EUR</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Tarif
                  <h2>{{ plantTariff.title }}</h2>
                </b-col>
              </b-row>
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="12"
              md="12"
            >
              <br>
              <br>
              <!--hide on test server-->
              <b-button
                :to="{ name: 'power-plant-edit', params: { id: baseData.id } }"
                variant="primary"
                style="width:270px; display:none;"
              >
                <span
                  v-if="baseData.solarPlantFilesVerifiedByBackendUser == true"
                >
                  Mehr
                </span>
              </b-button>
            </b-col>
          </b-row>
        </b-card>
      </b-col>
      <b-col
        cols="12"
        lg="8"
        md="12"
      >
        <b-card>
          <b-row>
            <b-col
              cols="12"
              md="6"
            >
              <h3>Rückzahlung Status</h3>
              <br>
              <b-row>
                <b-col>
                  Preis Photovoltaik-Anlage inkl. Ust.
                  <h2>{{ baseData.unitPrice | numFormat('0,0.00') }} EUR</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Rückzahlung Summe
                  <h2>{{ plantContractRepaidSum | numFormat('0,0.00') }} EUR</h2>
                </b-col>
              </b-row>
              <br>
              <br>
              <b-row>
                <b-col>
                  offener Betrag
                  <h2>{{ baseData.unitPrice-plantContractRepaidSum | numFormat('0,0.00') }} EUR</h2>
                </b-col>
              </b-row>
              <br>
            </b-col>
            <b-col
              cols="12"
              md="6"
            >
              <chartjs-component-doughnut-chart
                :height="275"
                :data="doughnutChart.data"
                :options="doughnutChart.options"
                class="mb-3"
              />
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="12"
              md="12"
              style="padding-top:40px;"
            >
              <h4>Testphase Aktionen - generieren</h4>
              <b-button
                variant="primary"
                size="xs"
                :disabled="tempLoading"
                @click="generateLog"
              >
                <span>
                  Log generieren
                </span>
              </b-button>
              &nbsp;
              <b-button
                variant="primary"
                size="xs"
                :disabled="tempLoading"
                @click="generateRepayment"
              >
                <span>
                  Mahnung generieren
                </span>
              </b-button>
              &nbsp;
              <b-button
                variant="primary"
                size="xs"
                :disabled="tempLoading"
              >
                <a
                  v-auth-href
                  :href="`${apiUrl}/plant-repayment/service-operation/${baseData.id}`"
                  style="color:white"
                >
                  <span>
                    Servicepauschal download
                  </span>
                </a>
              </b-button>
            </b-col>
            <b-col
              cols="12"
              md="12"
              style="padding-top:20px;"
            >
              <h4>Testphase Aktionen - löschen</h4>
              <b-button
                variant="danger"
                size="xs"
                :disabled="tempLoading"
                @click="deleteLog"
              >
                <span>
                  Log löschen
                </span>
              </b-button>
              &nbsp;
              <b-button
                variant="danger"
                size="xs"
                :disabled="tempLoading"
                @click="deleteRepayment"
              >
                <span>
                  Mahnung löschen
                </span>
              </b-button>
            </b-col>
          </b-row>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BCard,
  BButton,
  VBModal,
} from 'bootstrap-vue'

import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'

import numeral from 'numeral'

import { $apiUrl } from '@serverConfig'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import { $themeColors } from '@themeConfig'
import ChartjsComponentDoughnutChart from './chart/ChartjsComponentDoughnutChart.vue'

import store from '@/store'
import storeModule from '../storeModule'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BButton,
    ChartjsComponentDoughnutChart,
  },
  directives: {
    'b-modal': VBModal,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    plantUser: {
      type: Object,
      required: true,
      default: () => {},
    },
    plantTariff: {
      type: Object,
      required: true,
      default: () => {},
    },
    plantCampaign: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  data() {
    return {
      apiUrl: $apiUrl,
    }
  },
  setup(props) {
    const STORE_MODULE_NAME = 'app-power-plant-repayment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    const toast = useToast()

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const plantTitle = ref({})
    const plantContractRepaidSum = ref(store.getters['app-power-plant-repayment/contractRepaidSum'])

    const tempLoading = ref(false)

    const getPlantTitle = titleString => {
      const titleArray = titleString.split(',')

      plantTitle.value.title = `${titleArray[0] ? titleArray[0] : ''}, ${titleArray[1] ? titleArray[1] : ''}`
      plantTitle.value.googleLink = `https://www.google.com/maps/place/${titleArray[0] ? titleArray[0] : ''}, ${titleArray[1] ? titleArray[1] : ''}`
      plantTitle.value.person = titleArray[2] ? titleArray[2] : ''
    }
    getPlantTitle(props.baseData.title)

    const contractRepaidSum = computed(() => store.getters['app-power-plant-repayment/contractRepaidSum'])
    watch(contractRepaidSum, (val, oldVal) => {
      if (val > oldVal) {
        plantContractRepaidSum.value = val
      }
    })

    const doughnutChart = ref({
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
            labels: ['Rückzahlung Summe', 'offener Betrag'],
            data: [plantContractRepaidSum.value, (props.baseData.unitPrice - plantContractRepaidSum.value)],
            backgroundColor: [$themeColors.primary, $themeColors.danger],
            borderWidth: 5,
            pointStyle: 'rectRounded',
          },
        ],
      },
    })

    const generateLog = () => {
      console.log('generateLog', props.baseData.id)

      tempLoading.value = true
      store.dispatch(`${STORE_MODULE_NAME}/generateLog`, { plantId: props.baseData.id })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'OK',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          tempLoading.value = false
          store.commit('app-power-plant-repayment/updateTempRefreshListAt')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
          tempLoading.value = false
        })
    }

    const generateRepayment = () => {
      console.log('generateRepayment', props.baseData.id)
      tempLoading.value = true
      store.dispatch(`${STORE_MODULE_NAME}/generateRepayment`, { plantId: props.baseData.id })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'OK',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          tempLoading.value = false
          store.commit('app-power-plant-repayment/updateTempRefreshListAt')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
          tempLoading.value = false
        })
    }

    const deleteLog = () => {
      console.log('deleteLog', props.baseData.id)

      tempLoading.value = true
      store.dispatch(`${STORE_MODULE_NAME}/deleteLog`, { plantId: props.baseData.id })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'OK',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          tempLoading.value = false
          store.commit('app-power-plant-repayment/updateTempRefreshListAt')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
          tempLoading.value = false
        })
    }

    const deleteRepayment = () => {
      console.log('deleteRepayment', props.baseData.id)

      tempLoading.value = true
      store.dispatch(`${STORE_MODULE_NAME}/deleteRepayment`, { plantId: props.baseData.id })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'OK',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          tempLoading.value = false
          store.commit('app-power-plant-repayment/updateTempRefreshListAt')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
          tempLoading.value = false
        })
    }

    return {
      plantTitle,
      plantContractRepaidSum,
      doughnutChart,

      tempLoading,
      generateLog,
      generateRepayment,
      deleteLog,
      deleteRepayment,
    }
  },
}
</script>
