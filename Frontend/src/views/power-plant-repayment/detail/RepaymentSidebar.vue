<template>
  <b-sidebar
    id="solar-repayment"
    :visible="isAddNewRepaymentActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @change="(val) => $emit('update:is-add-new-repayment-active', val)"
    @hidden="resetForm"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Abrechnungsdaten
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />
      </div>

      <!-- BODY -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Form -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >
          <h6>manuelle Eingabe</h6>
          <hr>
          <!-- Title -->
          <validation-provider
            #default="validationContext"
            name="Berechnungsperiode"
            rules="required"
          >
            <b-form-group
              label="Berechnungsperiode"
              label-for="repaymentPeriod"
            >
              <b-form-input
                id="repaymentPeriod"
                v-model="repaymentDataObject.repaymentPeriod"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Datum von"
            rules="required"
          >
            <b-form-group
              label="Datum von"
              label-for="repaymentFromDate"
            >
              <b-form-datepicker
                id="repaymentFromDate"
                v-model="repaymentDataObject.repaymentFromDate"
                v-bind="labels['de'] || {}"
                locale="de"
                class="mb-1"
              />
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Datum bis"
            rules="required"
          >
            <b-form-group
              label="Datum bis"
              label-for="repaymentToDate"
            >
              <b-form-datepicker
                id="repaymentToDate"
                v-model="repaymentDataObject.repaymentToDate"
                v-bind="labels['de'] || {}"
                locale="de"
                class="mb-1"
              />
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Stromertrag kWh/a"
            rules="required"
          >
            <b-form-group
              label="Stromertrag kWh/a"
              label-for="powerProduction"
            >
              <cleave
                id="powerProduction"
                v-model="repaymentDataObject.powerProduction"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.number"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Eigenverbrauch kWh/a"
            rules="required"
          >
            <b-form-group
              label="Eigenverbrauch kWh/a"
              label-for="powerConsumption"
            >
              <cleave
                id="powerConsumption"
                v-model="repaymentDataObject.powerConsumption"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.numberDeep"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Tarif(e) Eigenverbrauch"
            rules="required"
          >
            <b-form-group
              label="Tarif(e) Eigenverbrauch"
              label-for="consumptionTariff"
            >
              <cleave
                id="consumptionTariff"
                v-model="repaymentDataObject.consumptionTariff"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.numberDeep"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Tarif(e) Überschussstrom"
            rules="required"
          >
            <b-form-group
              label="Tarif(e) Überschussstrom"
              label-for="productionTariff"
            >
              <cleave
                id="productionTariff"
                v-model="repaymentDataObject.productionTariff"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.numberDeep"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Tarif(e) PV Überschuß"
            rules="required"
          >
            <b-form-group
              label="Tarif(e) PV Überschuß"
              label-for="productionExtraTariff"
            >
              <cleave
                id="productionExtraTariff"
                v-model="repaymentDataObject.productionExtraTariff"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.numberDeep"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>
          <br>
          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Hinzufügen
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="button"
              variant="outline-secondary"
              @click="hide"
            >
              Abbrechen
            </b-button>
          </div>
        </b-form>
      </validation-observer>
    </template>
  </b-sidebar>
</template>

<script>
import {
  BSidebar,
  BButton,
  BForm,
  BFormGroup,
  BFormInput,
  BFormInvalidFeedback,
  BFormDatepicker,
} from 'bootstrap-vue'
import Cleave from 'vue-cleave-component'
import Ripple from 'vue-ripple-directive'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store'
import router from '@/router'

localize('de')
export default {
  components: {
    BSidebar,
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BFormDatepicker,
    // Form Validation
    ValidationProvider,
    ValidationObserver,
    Cleave,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewRepaymentActive',
    event: 'update:is-add-new-repayment-active',
  },
  props: {
    repaymentDataObject: {
      type: Object,
      required: true,
      default: () => {},
    },
    isAddNewRepaymentActive: {
      type: Boolean,
      required: true,
    },
  },
  watch: {
    isAddNewRepaymentActive: (newVal, oldVal) => {
      console.log('Prop changed: ', newVal, ' | was: ', oldVal)
    },
    repaymentDataObject: (newVal, oldVal) => {
      console.log('Prop changed: ', newVal, ' | was: ', oldVal)
    },
  },
  data() {
    return {
      required,
      alphaNum,
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
      labels: {
        de: {
          labelPrevDecade: 'Vorheriges Jahrzehnt',
          labelPrevYear: 'Vorheriges Jahr',
          labelPrevMonth: 'Vorheriger Monat',
          labelCurrentMonth: 'Aktueller Monat',
          labelNextMonth: 'Nächster Monat',
          labelNextYear: 'Nächstes Jahr',
          labelNextDecade: 'Nächstes Jahrzehnt',
          labelToday: 'Heute',
          labelSelected: 'Ausgewähltes Datum',
          labelNoDateSelected: 'Kein Datum gewählt',
          labelCalendar: 'Kalender',
          labelNav: 'Kalendernavigation',
          labelHelp: 'Mit den Pfeiltasten durch den Kalender navigieren',
        },
      },
    }
  },
  setup(props, { emit }) {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant-repayment'

    const blankPostData = {
      plantId: router.currentRoute.params.id,
    }
    const resetPostData = () => {
      props.repaymentDataObject = blankPostData // eslint-disable-line no-param-reassign
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetPostData)

    const onSubmit = () => {
      store.dispatch(`${STORE_MODULE_NAME}/addEditPlantRepayment`, { postData: props.repaymentDataObject })
        .then(response => {
          if (response.data.status === 200) {
            store.commit('app-power-plant-repayment/updateRepaymentDataUpdatedAt')
            toast({
              component: ToastificationContent,
              props: {
                title: 'OK',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            emit('update:is-add-new-repayment-active', false)
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler',
                icon: 'AlertTriangleIcon',
                variant: 'warning',
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

    return {
      onSubmit,
      refFormObserver,
      getValidationState,
      resetForm,
      //  changeSidebar,
    }
  },
}
</script>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
