<template>
  <div>
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
      <b-col
        cols="12"
      >
        <br><br>
        <b-card
          border-variant="primary"
        >
          <b-table
            hover
            :items="items"
            :fields="userDocumentFields"
          >
            <template #cell(actions)="row">
              <div
                class="text-right"
              >
                <a
                  v-auth-href
                  :href="`${row.item.id}`"
                >
                  <feather-icon
                    class="mr-1"
                    icon="ArrowDownCircleIcon"
                  />
                </a>
                <a
                  nohref
                >
                  <feather-icon
                    class="mr-1"
                    icon="XCircleIcon"
                  />
                </a>
              </div>
            </template>
          </b-table>
          <br>
          <b-button
            v-b-modal.modal-uploadFileNew
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
          >
            <feather-icon
              icon="PlusCircleIcon"
              size="15"
            />
            Neue Datei hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BButton, BRow, BCol, BFormGroup, BFormInput, BForm, BTable,
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
    BTable,
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
    },
  },
  data() {
    return {
      items: [
        {
          fileName: 'user_energieabrechnung_78086757-bba5-414f-af80-24bb9f9cc84c.jpg', t0: '19.11.2021',
        },
        {
          fileName: 'user_energieabrechnung_122086757-erds-414f-af80-dsdsdsawdwc.jpg', t0: '19.11.2021',
        },
      ],
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

    const userDocumentFields = [
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
    ]

    return {
      onSubmitPowerBill,
      userDocumentFields,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
