<template>
  <div>
    <b-row
      v-if="baseData.powerBillSaved === true && baseData.calculationSaved === true"
    >
      <b-col
        cols="12"
        lg="12"
        md="12"
        class="mr-0 text-right"
        style="padding-right:0px;"
      >
        <b-button
          variant="outline-primary"
          class="mr-0 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          type="button"
          style="display:none"
        >
          <feather-icon
            icon="EyeIcon"
            class="mr-50"
          />
          <a
            ref="calculationPreview"
            v-auth-href
            :href="`${apiUrl}/solar-plant/power-forecast-calculation/${$router.currentRoute.params.id}/${pUser.id}/false/prognoserechnung`"
          >
            Prognoserechnung Vorschau
          </a>
        </b-button>

        <b-button
          variant="outline-primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          type="button"
          :disabled="isDisabled()"
          @click="calculationPreviewTrigger"
        >
          <b-spinner
            v-if="showPreviewSpinner"
            small
          />
          Prognoserechnung Vorschau
        </b-button>

      </b-col>
    </b-row>
    <validation-observer ref="simpleRules">
      <b-form
        @submit.prevent="onSubmitBaseData"
      >
        <b-row>
          <b-col
            cols="12"
            md="6"
          >
            <b-form-group
              label="Title"
              label-for="title"
            >
              <validation-provider
                #default="{ errors }"
                name="Title"
                rules="required"
              >
                <b-form-input
                  id="title"
                  v-model="baseData.title"
                  trim
                  placeholder="Enter title"
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <!--
            <b-form-group
              label="Kampagnenname"
              label-for="campaign"
            >
              <b-form-input
                id="campaign"
                v-model="baseData.campaign"
                trim
                placeholder="Kampagnenname"
                :disabled="isDisabled()"
              />
            </b-form-group>
            -->

            <b-form-group
              label="Kampagnenname"
              label-for="campaign"
            >
              <v-select
                v-model="selectedCampaign"
                :options="campaignOptions"
                :clearable="false"
                label="text"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Bearbeiter"
              label-for="createdByName"
            >
              <validation-provider
                #default="{ errors }"
                name="Bearbeiter"
                rules="required"
              >
                <b-form-input
                  id="createdByName"
                  v-model="baseData.createdByName"
                  trim
                  placeholder=""
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Photovoltaik-Anlagen-Engpassleistung in kWp"
              label-for="nominalPower"
            >
              <validation-provider
                #default="{ errors }"
                name="First Name"
                rules="required"
              >
                <cleave
                  id="nominalPower1"
                  v-model="baseData.nominalPower"
                  class="form-control"
                  trim
                  placeholder="Enter Nominal Power"
                  :options="options.number"
                  :state="errors.length > 0 ? false:null"
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>

          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              v-if="tariff.directBuy === false"
              label="Anzahlung"
              label-for="prePayment"
            >
              <validation-provider
                #default="{ errors }"
                name="Anzahlung"
                rules="required"
              >
                <cleave
                  id="prePayment"
                  v-model="baseData.prePayment"
                  class="form-control"
                  trim
                  placeholder=""
                  :options="options.number"
                  :state="errors.length > 0 ? false:null"
                  :disabled="isDisabled()"
                  :change="recalculateFinalPrice()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <b-col
            v-if="tariff.directBuy === false"
            cols="12"
            md="6"
          >
            <b-form-group
              label="Anzahlung"
              label-for="prePayment"
            >
              <vue-slider
                v-model="baseData.prePayment"
                :adsorb="true"
                :marks="false"
                :tooltip="'always'"
                :min="prePaymentInterval.min"
                :max="prePaymentInterval.max"
                :interval="50"
                class="mb-3"
                direction="ltr"
                style="padding-top:30px;"
                :disabled="isDisabled()"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="12"
          >
            <hr>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Zusatzkosten exkl MWSt."
              label-for="additionalCost"
            >
              <cleave
                id="additionalCost"
                v-model="baseData.additionalCost"
                class="form-control"
                trim
                placeholder=""
                :options="options.number"
                :disabled="isDisabled()"
                :change="recalculateFinalPrice()"
              />
            </b-form-group>
            <b-form-group
              label=""
              label-for="buildingPermitCosts"
            >
              <b-form-checkbox
                id="buildingPermitCosts"
                v-model="baseData.buildingPermitCosts"
                switch
                inline
              >
                Baugenehmigung
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="joinPowerMeters"
            >
              <b-form-checkbox
                id="joinPowerMeters"
                v-model="baseData.joinPowerMeters"
                switch
                inline
              >
                Zählerzusammenlegung
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="communityPlant"
            >
              <b-form-checkbox
                id="communityPlant"
                v-model="baseData.communityPlant"
                switch
                inline
              >
                Gemeinschaftsanlage
              </b-form-checkbox>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="9"
          >
            <b-form-group
              label="Beschreibung Zusatzkosten"
              label-for="additionalCostDescription"
            >
              <b-form-textarea
                id="additionalCostDescription"
                v-model="baseData.additionalCostDescription"
                placeholder="Beschreibung Zusatzkosten"
                rows="6"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="12"
            style="padding-top:10px;"
          >
            <b-form-group
              label="Zusatzleistungen"
              label-for="extraOptions"
            >
              <v-select
                v-model="extraSelected"
                :options="extraOptions"
                :clearable="true"
                multiple
                label="text"
                @input="extrasChanged"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="12"
          >
            <hr>
            <br>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Preis Photovoltaik-Anlage inkl. Ust."
            >
              <h4>{{ baseData.unitPrice | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Planungspauschale"
            >
              <h4>{{ baseData.planFee | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            v-if="baseData.buildingPermitCosts == true"
            cols="12"
            md="2"
          >
            <b-form-group
              label="Baugenehmigungskosten in €"
            >
              <h4>{{ tariff.buildingPermitCosts | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Förderung"
            >
              <h4>{{ infoObj.subvention | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Servicepauschale"
            >
              <h4>{{ baseData.serviceFee | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Zusatzleistungen"
            >
              <h4>{{ extraOptionsSum | numFormat('0,0.00') }} EUR</h4>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <b-form-group
              label="Anzahl der Module"
            >
              <h4>{{ plantSize }}</h4>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row
          style="padding-top:30px;"
        >
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Tarifgruppe"
              label-for="tariff"
            >
              <b-form-select
                v-model="baseData.tariff"
                :options="tarrifOptions"
                :disabled="isDisabled()"
                @change.native="reloadTariff($event)"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Prognose Stromertrag per a in kWh/a"
              label-for="powerProductionForecast"
            >
              <validation-provider
                #default="{ errors }"
                name="Prognose Stromertrag "
                rules="required"
              >
                <cleave
                  id="powerProductionForecast"
                  v-model="baseData.powerProductionForecast"
                  class="form-control"
                  trim
                  placeholder="Enter Power Production Forecast"
                  :options="options.number"
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Prognose Eigenverbrauch per a in kWh/a"
              label-for="powerConsumptionForecast"
            >
              <validation-provider
                #default="{ errors }"
                name="Prognose Eigenverbrauch"
                rules="required"
              >
                <cleave
                  id="powerConsumptionForecast"
                  v-model="baseData.powerConsumptionForecast"
                  class="form-control"
                  trim
                  placeholder="Enter Power Consumption Forecast"
                  :options="options.number"
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>

          <b-col
            cols="12"
            md="3"
          >
            <b-form-group
              label="Prognose Eigenverbrauch Z2 per a in kWh/a"
              label-for="powerConsumptionForecastMeter2"
            >
              <cleave
                id="powerConsumptionForecastMeter2"
                v-model="baseData.powerConsumptionForecastMeter2"
                class="form-control"
                trim
                placeholder="Enter Power Consumption Forecast"
                :options="options.number"
                :disabled="isDisabled()"
              />
            </b-form-group>
          </b-col>

          <b-col
            cols="12"
            md="3"
          >
            <br>
            <b-form-group
              label="Fördergeber"
              label-for="subventionProvider"
            >
              {{ tariff.subventionInstitution }}
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <br>
            <b-form-group
              label="Förderung EUR per kWp ab 10 kWp"
              label-for="subventionFrom10Kw"
            >
              <cleave
                id="subventionFrom10Kw"
                v-model="baseData.subventionFrom10Kw"
                class="form-control"
                trim
                placeholder=""
                :options="options.number"
                :disabled="isDisabled()"
                :change="recalculateFinalPrice()"
              />
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <br>
            <b-form-group
              label="Maximaler Förderzuschuss"
              label-for="maxSubvention"
              style="padding-top:10px;"
            >
              {{ infoObj.maxSubvention | numFormat('0,0.00') }} EUR
            </b-form-group>
          </b-col>
          <b-col
            cols="12"
            md="3"
          >
            <br>
            <b-form-group
              label="Aufschlag bei Direktkauf"
              label-for="kWpPriceSurchargeDirectBuy"
              style="padding-top:10px;"
            >
              {{ tariff.kWpPriceSurchargeDirectBuy | numFormat('0,0.00') }} %
            </b-form-group>
          </b-col>
        </b-row>
        <br>
        <b-button
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          type="submit"
          :disabled="isDisabled()"
          @click.prevent="validationForm"
        >
          Speichern
        </b-button>
      </b-form>
    </validation-observer>
  </div>
</template>
<script>

import {
  BRow,
  BCol,
  BButton,
  BFormGroup,
  BFormInput,
  BForm,
  BFormTextarea,
  BFormSelect,
  BFormCheckbox,
  BSpinner,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'

import VueSlider from 'vue-slider-component'
import { required } from '@validations'

import '@core/scss/vue/libs/vue-slider.scss'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Cleave from 'vue-cleave-component'
import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'

import formulajs from '@formulajs/formulajs'

import { $apiUrl } from '@serverConfig'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'
import storeModule from '../../storeModule'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BButton,
    BFormGroup,
    BFormInput,
    BForm,
    BFormTextarea,
    BFormSelect,
    Cleave,
    VueSlider,
    ValidationProvider,
    ValidationObserver,
    BFormCheckbox,
    vSelect,
    BSpinner,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    paramId: {
      type: String,
      required: true,
    },
    tarrifOptions: {
      type: Array,
      required: true,
      default: () => [],
    },
    pUser: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
      required,
      apiUrl: $apiUrl,
      showPreviewSpinner: false,
    }
  },
  methods: {
    validationForm() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          this.onSubmitBaseData()
        }
      })
    },
    calculationPreviewTrigger() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          this.showPreviewSpinner = true
          this.onSubmitBaseData()

          setTimeout(() => {
            this.$refs.calculationPreview.click()
            this.showPreviewSpinner = false
          }, 750)
        }
      })
    },

    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
  },
  setup(props) {
    const toast = useToast()
    const tariff = ref({})
    const plantSize = ref(0)
    const infoObj = ref({
      maxSubvention: 0,
      calculatedSubvention: 0,
    })

    const campaignOptions = ref([])
    const extraOptions = ref([])
    const extraOptionsApi = ref([])
    const extraOptionsSum = ref(0)
    const selectedCampaign = ref({})
    const extraSelected = ref([])
    const prePaymentInterval = ref({
      min: 0,
      max: 0,
    })

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const powerBillSavedAt = computed(() => store.getters[`${STORE_MODULE_NAME}/powerBillSavedAt`])
    watch(powerBillSavedAt, (val, oldVal) => {
      if (val > oldVal) {
        /* eslint-disable no-param-reassign */
        props.baseData.powerBillSaved = true
        /* eslint-ebable no-param-reassign */
      }
    })

    const onSubmitBaseData = () => {
      /* eslint-disable no-param-reassign */
      props.baseData.campaignId = selectedCampaign.value.value
      /* eslint-ebable no-param-reassign */
      store.dispatch(`${STORE_MODULE_NAME}/editBaseData`, { baseData: props.baseData })
        .then(response => {
          if (response.data.status === 200) {
            /* eslint-disable no-param-reassign */
            props.baseData.calculationSaved = true
            /* eslint-ebable no-param-reassign */
            toast({
              component: ToastificationContent,
              props: {
                title: 'Daten wurden aktualisiert',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            //  cant come to that -- power bill needs to be saved first
            //  store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
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
        })
    }

    const recalculateFinalPriceDirectBuy = () => {
      let serviceFee = tariff.value.serviceFee * props.baseData.nominalPower
      if (serviceFee < tariff.value.serviceFeeMin) {
        serviceFee = tariff.value.serviceFeeMin
      } else if (serviceFee > tariff.value.serviceFeeMax) {
        serviceFee = tariff.value.serviceFeeMax
      }

      let planFee = tariff.value.planningFlatRate * props.baseData.nominalPower
      if (planFee < tariff.value.planningFlatRateMin) {
        planFee = tariff.value.planningFlatRateMin
      } else if (planFee > tariff.value.planningFlatRateMax) {
        planFee = tariff.value.planningFlatRateMax
      }

      /* eslint-disable no-param-reassign */
      props.baseData.serviceFee = serviceFee
      props.baseData.planFee = planFee

      //  Planung Pauschale
      //  const planCost = props.baseData.planFee

      //  min prePayment
      const minPrePayment = Math.ceil((tariff.value.kWpPrice * props.baseData.nominalPower * 0.10) / 50) * 50
      const minPrePaymentRaw = tariff.value.kWpPrice * props.baseData.nominalPower * 0.10

      //  max subvention  -- new formula
      //  const maxSubvention = props.baseData.subventionCoefficient * props.baseData.nominalPower
      let maxSubvention = 0
      if (props.baseData.nominalPower > 10) {
        //  maxSubvention = 10 * tariff.value.subventionTo10Kw + ((props.baseData.nominalPower - 10) * tariff.value.subventionFrom10Kw)
        //  powerplat specific subvention
        maxSubvention = 10 * tariff.value.subventionTo10Kw + ((props.baseData.nominalPower - 10) * props.baseData.subventionFrom10Kw)
      } else {
        maxSubvention = props.baseData.nominalPower * tariff.value.subventionTo10Kw
      }

      const maxprePayment = Math.ceil((maxSubvention / 0.35) / 50) * 50
      const maxprePaymentRaw = maxSubvention / 0.35

      prePaymentInterval.value.min = minPrePayment
      prePaymentInterval.value.max = maxprePayment

      let subvention = props.baseData.prePayment * 0.35
      if (props.baseData.prePayment >= maxprePayment) {
        subvention = maxSubvention
      }

      infoObj.value.maxSubvention = maxSubvention
      infoObj.value.subvention = subvention

      //  overrider max subvention only!!!
      infoObj.value.subvention = maxSubvention

      //  kwp-Preis x kWp Wert
      //  let price = tariff.value.kWpPrice * props.baseData.nominalPower

      /*
      if (props.baseData.buildingPermitCosts === true) {
        price += tariff.value.buildingPermitCosts
      }
      */

      //  Zuschlag
      //  const priceSurcharge = price * (tariff.value.kWpPriceSurcharge / 100)

      //  const extras = props.baseData.prePayment - props.baseData.additionalCost
      //  const interest = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extras), 1, 150, 0)) * -1

      //  const extrasMin = minPrePaymentRaw - props.baseData.additionalCost
      //  const interestMin = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extrasMin), 1, 150, 0)) * -1

      //  const extrasMax = maxprePaymentRaw - props.baseData.additionalCost
      //  const interestMax = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extrasMax), 1, 150, 0)) * -1

      //  Preis Photovoltaik Anlage
      //  new after Andy
      /*
      const finalPrice = Math.ceil((price + priceSurcharge + planCost + interest + Number(props.baseData.additionalCost)) * 1.2)
      const finalPriceMin = Math.ceil((price + priceSurcharge + planCost + interestMin + Number(props.baseData.additionalCost)) * 1.2)
      const finalPriceMax = Math.ceil((price + priceSurcharge + planCost + interestMax + Number(props.baseData.additionalCost)) * 1.2)
      */

      //  const finalPrice = Math.ceil((price + priceSurcharge + planCost + Number(props.baseData.additionalCost) + extraOptionsSum.value) * 1.2)
      //  const finalPriceMin = Math.ceil((price + priceSurcharge + planCost + Number(props.baseData.additionalCost) + extraOptionsSum.value) * 1.2)
      //  const finalPriceMax = Math.ceil((price + priceSurcharge + planCost + Number(props.baseData.additionalCost) + extraOptionsSum.value) * 1.2)

      let finalPrice = ((tariff.value.kWpPrice * ((100 + tariff.value.kWpPriceSurchargeDirectBuy) / 100) * props.baseData.nominalPower) + planFee + Number(props.baseData.additionalCost) + extraOptionsSum.value) * 1.2
      if (props.baseData.buildingPermitCosts === true) {
        finalPrice += tariff.value.buildingPermitCosts
      }

      //  Double prePaymentPrice = ((tariff.kWpPrice * ((100+tariff.kWpPriceSurcharge)/100) * plant.nominalPower) + plant.planFee + extrasSum) * 1.2;

      /* eslint-disable no-param-reassign */
      props.baseData.unitPrice = finalPrice
      props.baseData.unitPriceMinPrepayment = 0
      props.baseData.unitPriceMaxPrepayment = 0
      props.baseData.prePaymentMin = minPrePaymentRaw
      props.baseData.prePaymentMax = maxprePaymentRaw
      /* eslint-enable no-param-reassign */
    }

    const recalculateFinalPricePrepayment = () => {
      let serviceFee = tariff.value.serviceFee * props.baseData.nominalPower
      if (serviceFee < tariff.value.serviceFeeMin) {
        serviceFee = tariff.value.serviceFeeMin
      } else if (serviceFee > tariff.value.serviceFeeMax) {
        serviceFee = tariff.value.serviceFeeMax
      }

      let planFee = tariff.value.planningFlatRate * props.baseData.nominalPower
      if (planFee < tariff.value.planningFlatRateMin) {
        planFee = tariff.value.planningFlatRateMin
      } else if (planFee > tariff.value.planningFlatRateMax) {
        planFee = tariff.value.planningFlatRateMax
      }

      /* eslint-disable no-param-reassign */
      props.baseData.serviceFee = serviceFee
      props.baseData.planFee = planFee

      //  Planung Pauschale
      const planCost = props.baseData.planFee

      //  min prePayment
      const minPrePayment = Math.ceil((tariff.value.kWpPrice * props.baseData.nominalPower * 0.10) / 50) * 50
      const minPrePaymentRaw = tariff.value.kWpPrice * props.baseData.nominalPower * 0.10

      //  max subvention  -- new formula
      //  const maxSubvention = props.baseData.subventionCoefficient * props.baseData.nominalPower
      let maxSubvention = 0
      if (props.baseData.nominalPower > 10) {
        //  maxSubvention = 10 * tariff.value.subventionTo10Kw + ((props.baseData.nominalPower - 10) * tariff.value.subventionFrom10Kw)
        //  powerplat specific subvention
        maxSubvention = 10 * tariff.value.subventionTo10Kw + ((props.baseData.nominalPower - 10) * props.baseData.subventionFrom10Kw)
      } else {
        maxSubvention = props.baseData.nominalPower * tariff.value.subventionTo10Kw
      }

      const maxprePayment = Math.ceil((maxSubvention / 0.35) / 50) * 50
      const maxprePaymentRaw = maxSubvention / 0.35

      prePaymentInterval.value.min = minPrePayment
      prePaymentInterval.value.max = maxprePayment

      let subvention = props.baseData.prePayment * 0.35
      if (props.baseData.prePayment >= maxprePayment) {
        subvention = maxSubvention
      }

      infoObj.value.maxSubvention = maxSubvention
      infoObj.value.subvention = subvention

      //  overrider max subvention only!!!
      infoObj.value.subvention = maxSubvention

      //  kwp-Preis x kWp Wert
      let price = tariff.value.kWpPrice * props.baseData.nominalPower

      if (props.baseData.buildingPermitCosts === true) {
        price += tariff.value.buildingPermitCosts
      }

      //  Zuschlag
      const priceSurcharge = price * (tariff.value.kWpPriceSurcharge / 100)

      //  const extras = props.baseData.prePayment - props.baseData.additionalCost - extraOptionsSum.value  -- // - extraOptionsSum.value out of calculation
      //  const extras = props.baseData.prePayment - props.baseData.additionalCost
      //  old brutto
      //  const interest = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extras), 1, 150, 0)) * -1

      //  const interest = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) - extras), 1, 150, 0)) * -1
      const extrasMin = minPrePaymentRaw - props.baseData.additionalCost
      const interestMin = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extrasMin), 1, 150, 0)) * -1

      const extrasMax = maxprePaymentRaw - props.baseData.additionalCost
      const interestMax = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, ((price + planCost + priceSurcharge) * 1.2 - extrasMax), 1, 150, 0)) * -1

      /*
      console.log('price', price)
      console.log('gleich', extras)
      console.log('Zuschlag', priceSurcharge)
      console.log('interest', interest)
      console.log('additionalCost', props.baseData.additionalCost)
      console.log('planCost', planCost)
      console.log('price + Zuschlag', price + priceSurcharge)
      */
      /*
      console.log('price alles', price + priceSurcharge + extraOptionsSum.value + planCost)
      console.log('price alles mit mvst', (price + priceSurcharge + extraOptionsSum.value + planCost) * 1.2)
      console.log('price alles ohne anz', ((price + priceSurcharge + extraOptionsSum.value + planCost) * 1.2 - props.baseData.prePayment) / 1.2)

      const all1 = (price + priceSurcharge + extraOptionsSum.value + planCost) * 1.2
      const temp1 = ((price + priceSurcharge + extraOptionsSum.value + planCost) * 1.2 - props.baseData.prePayment) / 1.2
      */

      /*
      console.log('price alles', price + priceSurcharge + planCost)
      console.log('price alles mit mvst', (price + priceSurcharge + planCost) * 1.2)
      console.log('price alles ohne anz', ((price + priceSurcharge + planCost) * 1.2 - props.baseData.prePayment) / 1.2)

      const all1 = (price + priceSurcharge + planCost) * 1.2
      const temp1 = ((price + priceSurcharge + planCost) * 1.2 - props.baseData.prePayment) / 1.2

      const comp1 = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, temp1, 1, 150, 0)) * -1

      console.log('CUMIPMT res', comp1)
      console.log('CUMIPMT res mit', comp1 * 1.2)
      console.log('Gesamtkosten PV-Anlage inkl. USt.', all1 + (comp1 * 1.2))
      */

      //  Preis Photovoltaik Anlage
      //  new after Andy
      //  todo how to calculate interest rate
      //  const finalPrice = Math.ceil((price + priceSurcharge + planCost + interest + Number(props.baseData.additionalCost) + extraOptionsSum.value) * 1.2)
      //  const finalPrice = Math.ceil((price + priceSurcharge + planCost + interest + Number(props.baseData.additionalCost)) * 1.2)

      //  new
      const basePriceWithoutTax = price + priceSurcharge + planCost + Number(props.baseData.additionalCost)
      const basePriceWithTax = basePriceWithoutTax * 1.2
      const priceWithoutPrepaymentWithoutTax = (basePriceWithTax - props.baseData.prePayment) / 1.2
      const interestWithoutTax = (formulajs.CUMIPMT((tariff.value.interestRate / 100) / 12, 12.5 * 12, priceWithoutPrepaymentWithoutTax, 1, 150, 0)) * -1
      const interestWithTax = interestWithoutTax * 1.2

      const finalPrice = basePriceWithTax + interestWithTax
      console.log('finalPrice', finalPrice)

      //  old
      const finalPriceMin = Math.ceil((price + priceSurcharge + planCost + interestMin + Number(props.baseData.additionalCost)) * 1.2)
      const finalPriceMax = Math.ceil((price + priceSurcharge + planCost + interestMax + Number(props.baseData.additionalCost)) * 1.2)

      /* eslint-disable no-param-reassign */
      props.baseData.unitPrice = finalPrice
      props.baseData.unitPriceMinPrepayment = finalPriceMin
      props.baseData.unitPriceMaxPrepayment = finalPriceMax
      props.baseData.prePaymentMin = minPrePaymentRaw
      props.baseData.prePaymentMax = maxprePaymentRaw
      /* eslint-enable no-param-reassign */
    }

    const recalculateFinalPrice = () => {
      if (tariff.value.directBuy === true) {
        recalculateFinalPriceDirectBuy()
      } else {
        recalculateFinalPricePrepayment()
      }
    }

    const fetchTariff = id => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchTarriff`, { id })
        .then(response => {
          tariff.value = response.data.payload

          if (props.baseData.subventionFrom10Kw === null) {
            /* eslint-disable no-param-reassign */
            props.baseData.subventionFrom10Kw = response.data.payload.subventionFrom10Kw
            /* eslint-enable no-param-reassign */
          }

          recalculateFinalPrice()
          plantSize.value = Math.ceil(props.baseData.nominalPower / tariff.value.kwpSizePewKw)
        })
    }

    const reloadTariff = e => {
      fetchTariff(e.target.value)
    }

    const fetchCampaignOptions = () => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchCampaignOptions`)
        .then(response => {
          campaignOptions.value.push({
            text: 'Keine Kampagne',
            value: null,
          })

          selectedCampaign.value = campaignOptions.value[0]
          response.data.forEach(element => {
            const options = {
              text: element.title,
              value: element.id,
            }
            campaignOptions.value.push(options)
            /* eslint prefer-destructuring: ["error", {VariableDeclarator: {object: true}}] */
            //  postData.value.campaign = campaignOptions.value[0]

            if (props.baseData.campaignId === element.id) {
              selectedCampaign.value = options
            }
          })
        })
    }

    const fetchActiveExtras = () => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchActiveExtras`, { id: props.baseData.id })
        .then(response => {
          extraOptionsSum.value = 0
          response.data.forEach(element => {
            const option = {
              text: element.label,
              value: element.value,
            }
            extraSelected.value.push(option)
            extraOptionsSum.value += element.price
          })

          recalculateFinalPrice()
        })
    }

    const fetchExtras = () => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchExtras`)
        .then(response => {
          extraOptionsApi.value = response.data
          response.data.forEach(element => {
            const option = {
              text: element.label,
              value: element.value,
            }
            extraOptions.value.push(option)
          })
          fetchActiveExtras()
        })
    }

    const extrasChanged = () => {
      const extraOptionsIds = []
      extraOptionsSum.value = 0
      extraSelected.value.forEach(element => {
        extraOptionsSum.value += extraOptionsApi.value.find(eo => eo.value === element.value).price
        extraOptionsIds.push(extraOptionsApi.value.find(eo => eo.value === element.value).value)
      })

      recalculateFinalPrice()

      store.dispatch('app-power-plant/mapExtras', { id: props.baseData.id, extras: extraOptionsIds })
        .then(res => {
          console.log(res)
          if (res.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Zusatzleistungen gespeichert.',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Zusatzleistungen nicht gespeichert.',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateDocumentStatus error response')
            /* eslint-enable no-console */
          }
        })
    }

    setTimeout(() => {
      fetchCampaignOptions()
      fetchTariff(props.baseData.tariff)
      fetchExtras()
    }, 750)

    return {
      onSubmitBaseData,
      recalculateFinalPrice,
      reloadTariff,
      tariff,
      prePaymentInterval,
      plantSize,
      infoObj,
      campaignOptions,
      selectedCampaign,
      extraOptions,
      extraSelected,
      extrasChanged,
      extraOptionsSum,
    }
  },
  /*
  mounted(props) {
    this.$nextTick(() => {
      console.log('mounted')
      console.log(props)
    })
  },
  */
}
</script>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
