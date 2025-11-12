<template>
  <div>
    <b-link
      :to="{ name: 'admin-settings-list' }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zur Liste
    </b-link>
    <br>
    <b-card>
      <validation-observer ref="validateSettingsForm">
        <b-form
          @submit.prevent="onSubmitSettingsData"
        >
          <b-row>
            <b-col
              cols="12"
              lg="8"
            >
              <b-form-group
                label="Titel"
                label-for="title"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Title"
                  rules="required"
                >
                  <b-form-input
                    id="title"
                    v-model="settingsData.title"
                    autofocus
                    trim
                    placeholder=""
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Aktiv"
                label-for="active"
              >
                <b-form-checkbox
                  v-model="settingsData.active"
                  name="check-button"
                  switch
                  inline
                >
                  Tariff wählbar
                </b-form-checkbox>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="solar.family Tarif(e) Eigenverbrauch inkl. USt. in €"
                label-for="rateConsumption"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Tarif(e) Eigenverbrauch"
                  rules="required"
                >
                  <cleave
                    id="rateConsumption"
                    v-model="settingsData.rateConsumption"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="solar.family Tarif(e) Überschussstrom inkl. USt. in €"
                label-for="rateExcessProduction"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Tarif(e) Überschussstrom"
                  rules="required"
                >
                  <cleave
                    id="rateExcessProduction"
                    v-model="settingsData.rateExcessProduction"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Netzbetreiber Tarif(e) PV Überschuß inkl. USt. in €"
                label-for="rateExcessProductionExternal"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Tarif(e) PV Überschuß"
                  rules="required"
                >
                  <cleave
                    id="rateExcessProductionExternal"
                    v-model="settingsData.rateExcessProductionExternal"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="kWp Preis Photovoltaik-Anlage excl. USt. in €"
                label-for="kWpPrice"
              >
                <validation-provider
                  #default="{ errors }"
                  name="kWp Preis Photovoltaik-Anlage"
                  rules="required"
                >
                  <cleave
                    id="kWpPrice"
                    v-model="settingsData.kWpPrice"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Aufschlag bei Anzahlung kWp Preis Photovoltaik-Anlage in %"
                label-for="kWpPriceSurcharge"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Aufschlag kWp Preis"
                  rules="required"
                >
                  <cleave
                    id="kWpPriceSurcharge"
                    v-model="settingsData.kWpPriceSurcharge"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Aufschlag bei Direktkauf kWp Preis Photovoltaik-Anlage in %"
                label-for="kWpPriceSurchargeDirectBuy"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Aufschlag kWp Preis"
                  rules="required"
                >
                  <cleave
                    id="kWpPriceSurchargeDirectBuy"
                    v-model="settingsData.kWpPriceSurchargeDirectBuy"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Verzinsung per a in %"
                label-for="interestRate"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Verzinsung per a"
                  rules="required"
                >
                  <cleave
                    id="interestRate"
                    v-model="settingsData.interestRate"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-form-group
                label="Fördergeber"
                label-for="subventionInstitution"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Fördergeber"
                  rules="required"
                >
                  <b-form-input
                    id="subventionInstitution"
                    v-model="settingsData.subventionInstitution"
                    autofocus
                    trim
                    placeholder=""
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-form-group
                label="Förderung EUR per kWp bis 10 kWp"
                label-for="subventionTo10Kw"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Förderung EUR per kWp bis 10 kWp"
                  rules="required"
                >
                  <cleave
                    id="subventionTo10Kw"
                    v-model="settingsData.subventionTo10Kw"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Förderung EUR per kWp ab 10 kWp"
                label-for="subventionFrom10Kw"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Förderung EUR per kWp ab 10 kWp"
                  rules="required"
                >
                  <cleave
                    id="subventionFrom10Kw"
                    v-model="settingsData.subventionFrom10Kw"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Preis anteilige Planungspauschale je kWp excl. USt. in €"
                label-for="planningFlatRate"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis anteilige Planungspauschale"
                  rules="required"
                >
                  <cleave
                    id="planningFlatRate"
                    v-model="settingsData.planningFlatRate"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Preis Min anteilige Planungspauschale excl. USt. in €"
                label-for="planningFlatRateMin"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis anteilige Planungspauschale"
                  rules="required"
                >
                  <cleave
                    id="planningFlatRateMin"
                    v-model="settingsData.planningFlatRateMin"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Preis Max anteilige Planungspauschale excl. USt. in €"
                label-for="planningFlatRateMax"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis anteilige Planungspauschale"
                  rules="required"
                >
                  <cleave
                    id="planningFlatRateMax"
                    v-model="settingsData.planningFlatRateMax"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Preis Servicepauschale je kWp excl. USt. in €"
                label-for="serviceFee"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Servicepauschale je kWp"
                  rules="required"
                >
                  <cleave
                    id="serviceFee"
                    v-model="settingsData.serviceFee"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Preis Min Servicepauschale excl. USt. in €"
                label-for="serviceFeeMin"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Min Servicepauschale"
                  rules="required"
                >
                  <cleave
                    id="serviceFeeMin"
                    v-model="settingsData.serviceFeeMin"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Preis Max Servicepauschale excl. USt. in €"
                label-for="serviceFeeMax"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Max Servicepauschale"
                  rules="required"
                >
                  <cleave
                    id="serviceFeeMax"
                    v-model="settingsData.serviceFeeMax"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Baugenehmigungskosten inkl. USt. in €"
                label-for="buildingPermitCosts"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Baugenehmigungskosten"
                  rules="required"
                >
                  <cleave
                    id="buildingPermitCosts"
                    v-model="settingsData.buildingPermitCosts"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Modulgröße in kWp"
                label-for="kwpSizePewKw"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Modulgröße in kWp"
                  rules="required"
                >
                  <cleave
                    id="kwpSizePewKw"
                    v-model="settingsData.kwpSizePewKw"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="Modulgröße"
                label-for="moduleSize"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Modulgröße"
                  rules="required"
                >
                  <cleave
                    id="moduleSize"
                    v-model="settingsData.moduleSize"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Direktkauf"
                label-for="directBuy"
              >
                <b-form-checkbox
                  v-model="settingsData.directBuy"
                  name="check-button-directbuy"
                  switch
                  inline
                >
                  Kunde will Direkt kaufen
                </b-form-checkbox>
              </b-form-group>
              <b-form-group
                label="*obsolet* Preis Erdspieß inkl. USt. in €"
                label-for="spikePrice"
                style="padding-top:15px;"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Erdspieß"
                  rules="required"
                >
                  <cleave
                    id="spikePrice"
                    v-model="settingsData.spikePrice"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="*obsolet* Preis Überspannungsableier Typ I inkl. USt. in €"
                label-for="surgePrice"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Überspannungsableier"
                  rules="required"
                >
                  <cleave
                    id="surgePrice"
                    v-model="settingsData.surgePrice"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
              <b-form-group
                label="*obsolet* Preis Powerlan-Adapter inkl. USt. in €"
                label-for="pricePowerLanAdapter"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis Powerlan-Adapter"
                  rules="required"
                >
                  <cleave
                    id="pricePowerLanAdapter"
                    v-model="settingsData.pricePowerLanAdapter"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.number"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
          </b-row>
          <br><br>
          <b-button
            variant="primary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            :block="$store.getters['app/currentBreakPoint'] === 'xs'"
            type="submit"
            @click.prevent="validationForm"
          >
            Speichern
          </b-button>
        </b-form>
      </validation-observer>
    </b-card>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BForm,
  BFormGroup,
  BFormInput,
  BButton,
  BCard,
  BFormCheckbox,
  BLink,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
import Cleave from 'vue-cleave-component'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, onUnmounted } from '@vue/composition-api'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import router from '@/router'
import store from '@/store'
import storeModule from '../storeModule'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BForm,
    BFormGroup,
    BFormInput,
    BButton,
    BCard,
    BFormCheckbox,
    ValidationProvider,
    ValidationObserver,
    Cleave,
    BLink,
  },
  data() {
    return {
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
      required,
    }
  },
  methods: {
    validationForm() {
      this.$refs.validateSettingsForm.validate().then(success => {
        if (success) {
          this.onSubmitSettingsData()
        }
      })
    },
  },
  setup() {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-admin-settings'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const settingsData = ref({})

    const fetchSettings = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchSettings`, { settingsId: router.currentRoute.params.id })
        .then(response => {
          settingsData.value = response.data
        })
    }

    const onSubmitSettingsData = () => {
      store.dispatch(`${STORE_MODULE_NAME}/editSettings`, { baseData: settingsData.value })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Updated successfully',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Error updating',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    //  load data
    fetchSettings()

    return {
      settingsData,
      onSubmitSettingsData,
    }
  },
}
</script>
