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
        <validation-observer ref="validateCustomerContactForm">
          <b-form
            @submit.prevent="activateUser"
          >
            Begehungstermin auswahlen<br><br>
            <validation-provider
              #default="{ errors }"
              name="Begehungstermin Datum"
              rules="required"
            >
              <b-calendar
                v-model="inspectionCheckDateInView.date"
                :value="inspectionCheckDateInView.date"
                locale="de"
                v-bind="labels['de'] || {}"
              />
              <small class="text-warning">{{ errors[0] }}</small>
            </validation-provider>
            <validation-provider
              #default="{ errors }"
              name="Begehungstermin Uhrzeit"
              rules="required"
            >
              <b-time
                v-model="inspectionCheckDateInView.time"
                :value="inspectionCheckDateInView.time"
                locale="de"
                v-bind="labels['de'] || {}"
              />
              <small class="text-warning">{{ errors[0] }}</small>
            </validation-provider>
            <br><br><br><br>
            <b-form-group>
              <b-form-checkbox
                id="inspectionEnergyComunity"
                v-model="baseData.inspectionEnergyComunity"
                switch
                inline
              >
                Teilnehmer Energiegemeinschaft
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="inspectionEnergyComunityText"
            >
              <b-form-textarea
                v-if="baseData.inspectionEnergyComunity"
                id="inspectionEnergyComunityText"
                v-model="baseData.inspectionEnergyComunityText"
                placeholder="Beschreibung"
                rows="4"
              />
            </b-form-group>

            <br>
            <b-form-group>
              <b-form-checkbox
                id="inspectionWaterHeating"
                v-model="baseData.inspectionWaterHeating"
                switch
                inline
              >
                Warmwasserbereitung
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="inspectionWaterHeatingText"
            >
              <b-form-textarea
                v-if="baseData.inspectionWaterHeating"
                id="inspectionWaterHeatingText"
                v-model="baseData.inspectionWaterHeatingText"
                placeholder="Beschreibung"
                rows="4"
              />
            </b-form-group>

            <br>
            <b-form-group>
              <b-form-checkbox
                id="inspectionStorage"
                v-model="baseData.inspectionStorage"
                switch
                inline
              >
                Speicher
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="inspectionStorageText"
            >
              <b-form-textarea
                v-if="baseData.inspectionStorage"
                id="inspectionStorageText"
                v-model="baseData.inspectionStorageText"
                placeholder="Beschreibung"
                rows="4"
              />
            </b-form-group>

            <br>
            <b-form-group>
              <b-form-checkbox
                id="inspectionEmergencyPower"
                v-model="baseData.inspectionEmergencyPower"
                switch
                inline
              >
                Notstromfunktionalität
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="inspectionEmergencyPowerText"
            >
              <b-form-textarea
                v-if="baseData.inspectionEmergencyPower"
                id="inspectionEmergencyPowerText"
                v-model="baseData.inspectionEmergencyPowerText"
                placeholder="Beschreibung"
                rows="4"
              />
            </b-form-group>

            <br>
            <b-form-group>
              <b-form-checkbox
                id="inspectionChargingInfrastructure"
                v-model="baseData.inspectionChargingInfrastructure"
                switch
                inline
              >
                Ladeinfrastruktur
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              label=""
              label-for="inspectionChargingInfrastructureText"
            >
              <b-form-textarea
                v-if="baseData.inspectionChargingInfrastructure"
                id="inspectionChargingInfrastructureText"
                v-model="baseData.inspectionChargingInfrastructureText"
                placeholder="Beschreibung"
                rows="4"
              />
            </b-form-group>

            <br><br>
            <b-form-group
              label="Allgemeine Anmerkungen"
              label-for="inspectionComments"
            >
              <b-form-textarea
                id="inspectionComments"
                v-model="baseData.inspectionComments"
                placeholder="Allgemeine Anmerkungen"
                rows="4"
              />
            </b-form-group>

            <br><br>
            <b-form-group
              label="solar.family Bearbeiter (Kalendereintrag senden)"
              label-for="inspectionMailBackendUserSendTo"
            >
              <validation-provider
                #default="{ errors }"
                name="Email-Adresse"
                rules="required|email"
              >
                <b-form-input
                  id="inspectionMailBackendUserSendTo"
                  v-model="baseData.inspectionMailBackendUserSendTo"
                  trim
                  placeholder="Email-Adresse"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>

            <br><br><br>
            <b-row>
              <b-col
                cols="12"
                md="7"
              >
                <b-button
                  variant="primary"
                  class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                  type="button"
                  block
                  @click.prevent="validateCustomerContactForm"
                >
                  Speichern
                </b-button>
              </b-col>
              <b-col
                cols="12"
                md="5"
                style="text-align:right;"
              >
                <b-button
                  v-if="baseData.inspectionCheckInProgress === true"
                  variant="outline-danger"
                  class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                  type="button"
                  block
                  @click="activateUser()"
                >
                  <feather-icon
                    v-if="showLoading === false"
                    icon="CheckCircleIcon"
                    size="15"
                  />
                  <b-spinner
                    v-if="showLoading"
                    small
                  />
                  <!-- v-if="showLoading" -->
                  Mailversand/Registrierung
                </b-button>
              </b-col>
            </b-row>
          </b-form>
        </validation-observer>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BFormGroup,
  BFormCheckbox,
  BForm,
  BFormTextarea,
  BButton,
  BCalendar,
  BTime,
  BFormInput,
  VBModal,
  BSpinner,
} from 'bootstrap-vue'

import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
//  let self

import store from '@/store'
import storeModule from '../storeModule'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BFormGroup,
    BForm,
    BFormCheckbox,
    BFormTextarea,
    BButton,
    BCalendar,
    BTime,
    BFormInput,
    ValidationProvider,
    ValidationObserver,
    BSpinner,
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
    /*
    inspectionCheckDate: {
      type: String,
      required: true,
      default: () => '',
    },
    */
  },
  data: () => ({
    showLoading: false,
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
        labelHours: 'Stunden',
        labelMinutes: 'Minuten',
        labelSeconds: 'Sekunden',
        labelIncrement: 'Erhöhen',
        labelDecrement: 'Verringern',
        labelTimeSelected: 'Ausgewählte Zeit',
        labelNoTimeSelected: 'Keine Zeit ausgewählt',
      },
    },
  }),
  methods: {
    validateCustomerContactForm() {
      this.$refs.validateCustomerContactForm.validate().then(success => {
        if (success) {
          this.save()
        }
      })
    },
    activateUser() {
      this.$bvModal
        .msgBoxConfirm('Soll das mail mit dem Vor-Ort-Begehungstermin an den Kunden gesendet werden ?', {
          title: 'Mailversand/Registrierung',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'Ja',
          cancelTitle: 'abbrechen',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            console.log('activate user true')
            this.showLoading = true
            store.dispatch('app-power-plant/updateWorkflowStatus', { plantId: this.$props.baseData.id, status: 'inspectionMailSent' })
              .then(res => {
                /* eslint-disable no-console */
                console.log(res)
                /* eslint-enable no-console */
                //  this.$props.baseData.inspectionMailSent = true
                if (res.data.status === 200) {
                  store.commit('app-power-plant/updateSolarPlantUpdatedAt')
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Registrierung Email versandt',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                } else {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Fehler beim speichern!',
                      icon: 'AlertIcon',
                      variant: 'danger',
                    },
                  })
                }
                this.showLoading = false
              })
              .catch(() => {
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Fehler beim speichern!',
                    icon: 'AlertIcon',
                    variant: 'danger',
                  },
                })
                this.showLoading = false
              })
          }
        })
    },
  },
  setup(props) {
    const toast = useToast()
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    const inspectionCheckDateInView = ref({ date: '', time: '' })

    const setInspectionCheckDate = () => {
      if (props.baseData.inspectionCheckDate != null) {
        const inspectionCheckDate = props.baseData.inspectionCheckDate.split('T')

        inspectionCheckDateInView.value.date = inspectionCheckDate['0']
        inspectionCheckDateInView.value.time = inspectionCheckDate['1']
      }
    }

    setTimeout(() => {
      setInspectionCheckDate()
    }, 1000)

    const save = () => {
      const inspectionCheckDate = `${inspectionCheckDateInView.value.date}T${inspectionCheckDateInView.value.time}`

      //  inspectionCheckDate
      const postData = {
        inspectionCheckDate,
        inspectionEnergyComunity: props.baseData.inspectionEnergyComunity,
        inspectionEnergyComunityText: props.baseData.inspectionEnergyComunityText,
        inspectionWaterHeating: props.baseData.inspectionWaterHeating,
        inspectionWaterHeatingText: props.baseData.inspectionWaterHeatingText,
        inspectionStorage: props.baseData.inspectionStorage,
        inspectionStorageText: props.baseData.inspectionStorageText,
        inspectionEmergencyPower: props.baseData.inspectionEmergencyPower,
        inspectionEmergencyPowerText: props.baseData.inspectionEmergencyPowerText,
        inspectionChargingInfrastructure: props.baseData.inspectionChargingInfrastructure,
        inspectionChargingInfrastructureText: props.baseData.inspectionChargingInfrastructureText,
        inspectionComments: props.baseData.inspectionComments,
        inspectionMailBackendUserSendTo: props.baseData.inspectionMailBackendUserSendTo,
      }

      store.dispatch('app-power-plant/updateInspection', { plantId: props.baseData.id, postData })
        .then(response => {
          if (response.data.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Daten wurden aktualisiert',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Daten wurden nicht aktualisiert!',
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
              title: 'Fehler bei der Verbindung zum Server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    return {
      save,
      inspectionCheckDateInView,
      //  activateUser,
    }
  },
  /*
  created() {
    self = this
  },
  */
}
</script>
