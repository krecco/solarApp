<template>
  <div>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <div class="d-flex">
          <feather-icon
            icon="TrendingUpIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            Letzte Verbrauchsabrechnung
          </h4>
        </div>
        <br>
        <!-- User Info: Input Fields -->
        <validation-observer ref="validatePowerBillForm">
          <b-form
            @submit.prevent="onSubmitPowerBill"
          >
            <b-row>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Jahresverbrauch (Kw) Zähler 1"
                  label-for="consumption"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Jahresverbrauch (Kw)"
                    rules="required"
                  >
                    <cleave
                      id="consumption"
                      class="form-control"
                      v-model="powerBillData.consumption"
                      autofocus
                      trim
                      placeholder="Eingabe Jahresverbrauch in Kw"
                      :options="options.number"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Kosten/Jahr (EUR) Zähler 1"
                  label-for="consumptionValue"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Cost (EUR)"
                    rules="required"
                  >
                    <cleave
                      id="consumptionValue"
                      class="form-control"
                      v-model="powerBillData.consumptionValue"
                      autofocus
                      trim
                      placeholder="Enter Consumption Value"
                      :options="options.number"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Vertrag Zähler 1"
                  label-for="contract"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Vertrag"
                    rules="required"
                  >
                    <b-form-input
                      id="contract"
                      v-model="powerBillData.contract"
                      autofocus
                      trim
                      placeholder="Eingabe Vertrags-Option"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Jahresverbrauch (Kw) Zähler 2"
                  label-for="consumption2"
                >
                  <cleave
                    id="consumption2"
                    class="form-control"
                    v-model="powerBillData.consumption2"
                    autofocus
                    trim
                    placeholder="Eingabe Jahresverbrauch in Kw"
                    :options="options.number"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Kosten/Jahr (EUR) Zähler 2"
                  label-for="consumptionValue2"
                >
                  <cleave
                    id="consumptionValue2"
                    class="form-control"
                    v-model="powerBillData.consumptionValue2"
                    autofocus
                    trim
                    placeholder="Enter Consumption Value"
                    :options="options.number"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Vertrag Zähler 2"
                  label-for="contract2"
                >
                  <b-form-input
                    id="contract2"
                    v-model="powerBillData.contract2"
                    autofocus
                    trim
                    placeholder="Eingabe Vertrags-Option"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Rechnungs-Nr."
                  label-for="billNo"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Rechnungs-Nr."
                    rules="required"
                  >
                    <b-form-input
                      id="billNo"
                      v-model="powerBillData.billNo"
                      autofocus
                      trim
                      placeholder="Eingabe Rechnungs-Nr."
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Rechnungsperiode"
                  label-for="billPeriod"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Rechnungsperiode"
                    rules="required"
                  >
                    <b-form-input
                      id="billPeriod"
                      v-model="powerBillData.billPeriod"
                      autofocus
                      trim
                      placeholder="Eingabe von-bis"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Energielieferant"
                  label-for="provider"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Energielieferant"
                    rules="required"
                  >
                    <b-form-input
                      id="provider"
                      v-model="powerBillData.provider"
                      autofocus
                      trim
                      placeholder="Eingabe Energielieferant"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="12"
              >
                <b-form-group
                  label="Netzbetreiber"
                  label-for="netProvider"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Netzbetreiber"
                    rules="required"
                  >
                    <b-form-input
                      id="netProvider"
                      v-model="powerBillData.netProvider"
                      autofocus
                      trim
                      placeholder="Eingabe Netzbetreiber"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

            </b-row>
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              type="submit"
              @click.prevent="validationFormPowerBill"
            >
              Speichern
            </b-button>
          </b-form>
        </validation-observer>
        <!--
        <b-button
          variant="outline-secondary"
          type="reset"User() {
      this.$refs.validters['app/currentBreakPoint'] === 'xs'"
        >
          Reset
        </b-button>
        -->
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BButton, BRow, BCol, BFormGroup, BFormInput, BForm,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
import Cleave from 'vue-cleave-component'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    ValidationProvider,
    ValidationObserver,
    Cleave,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    powerBillData: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  data() {
    return {
      gender_options: [
        { value: null, text: 'Please select an option' },
        { value: '1', text: 'Mr.' },
        { value: '2', text: 'Mrs.' },
      ],
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
        iban: {
          blocks: [4, 4, 4, 4, 4],
          uppercase: true,
        },
      },
      required,
    }
  },
  methods: {
    validationFormPowerBill() {
      this.$refs.validatePowerBillForm.validate().then(success => {
        if (success) {
          this.onSubmitPowerBill()
        }
      })
    },
  },
  setup(props) {
    const toast = useToast()

    const onSubmitPowerBill = () => {
      store.dispatch('app-power-plant/editBillData', { plantId: props.baseData.id, billData: props.powerBillData })
        .then(response => {
          if (response.data.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'BillData updated successfully',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Error updating BillData, request data not ok',
                text: response.data.payload.message,
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
              title: 'Error updating BillData, Error connecting to server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    return {
      onSubmitPowerBill,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
