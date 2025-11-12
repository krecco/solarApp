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
              <!--
              <br>
              <b-row>
                <b-col>
                  Prognose Stromertrag
                  <h2>{{ baseData.powerProductionForecast | numFormat('0,0') }} kWh/a</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Prognose Eigenverbrauch
                  <h2>{{ baseData.powerConsumptionForecast | numFormat('0,0') }} kWh/a</h2>
                </b-col>
              </b-row>
              -->
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
                <span
                  v-if="baseData.solarPlantFilesVerifiedByBackendUser != true"
                >
                  Bearbeiten - wird gelöscht
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
              md="12"
            >
              <h3>Photovoltaik-Anlage Status</h3>
              <br>
              <b-row>
                <b-col
                  cols="12"
                  md="12"
                >
                  <b-progress
                    v-model="completetionStatus"
                    variant="primary"
                    max="100"
                  />
                </b-col>
              </b-row>
              <br>
              <br>
              <b-row
                v-if="baseData.inspectionMailSent !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Begehungstermin vereinbart</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.inspectionMailSent === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Begehungstermin vereinbart ({{ baseData.inspectionCheckDate | moment("DD.MM. YYYY HH:mm") }} | {{ baseData.inspectionMailBackendUserSendTo }} )</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.inspectionCheckFinished !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Begehungstermin durchgeführt </h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.inspectionCheckFinished === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Begehungstermin durchgeführt ({{ baseData.inspectionCheckFinishedDate | moment("DD.MM. YYYY") }} | {{ baseData.inspectionCheckFinishedMail }} )</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.calculationSentToCustomer !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Planungsunterlagen erstellt und an Kunden übermittelt</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.calculationSentToCustomer === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Planungsunterlagen erstellt und an Kunden übermittelt ({{ baseData.calculationSentToCustomerDate | moment("DD.MM. YYYY HH:mm") }})</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.orderInterestAccepted !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Auftragsabsicht</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.orderInterestAccepted === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Auftragsabsicht ({{ baseData.orderInterestAcceptedDate | moment("DD.MM. YYYY HH:mm") }})</h4>
                </b-col>
              </b-row>

              <b-row
                v-if="baseData.contractsSentToCustomer !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Vertragsunterlagen erstellt und an den Kunden übermittelt</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.contractsSentToCustomer === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Vertragsunterlagen erstellt und an den Kunden übermittelt ({{ baseData.contractsSentToCustomerDate | moment("DD.MM. YYYY HH:mm") }})</h4>
                </b-col>
              </b-row>

              <b-row
                v-if="baseData.contractFilesChecked !== true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                >
                  <feather-icon
                    icon="CircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Auftrag erhalten</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.contractFilesChecked === true"
                style="margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="1"
                  class="text-primary"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="25"
                  />
                </b-col>
                <b-col
                  cols="12"
                  md="11"
                >
                  <h4>Auftrag erhalten ({{ baseData.contractFilesCheckedDate | moment("DD.MM. YYYY HH:mm") }})</h4>
                </b-col>
              </b-row>
              <b-row
                v-if="baseData.contractFilesChecked === true"
                style="margin-top:30px; margin-bottom:5px;"
              >
                <b-col
                  cols="12"
                  md="12"
                >
                  <h3>Anlage im Betrieb</h3>
                </b-col>
                <b-col
                  cols="12"
                  md="12"
                >
                  <b-row>
                    <b-col
                      cols="12"
                      md="4"
                    >
                      <b-form-datepicker
                        v-model="baseData.plantInUseDate"
                        v-bind="labels[locale] || {}"
                        :locale="locale"
                        :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                        class="mb-1"
                      />
                    </b-col>
                    <b-col
                      cols="12"
                      md="2"
                    >
                      <b-button
                        variant="primary"
                        @click="updatePlantInUse"
                      >
                        Speichern
                      </b-button>
                    </b-col>
                  </b-row>
                </b-col>
              </b-row>
            </b-col>
          </b-row>
          <br>
          <b-button
            v-if="baseData.cloneSource === null && baseData.calculationSentToCustomer === true"
            variant="primary"
            style="position: absolute; top: -50px; right: 0px;"
            @click="clonePlant"
          >
            Anlage kopieren
            {{ baseData.cloneSource }}
          </b-button>
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
  BProgress,
  VBModal,
  BFormDatepicker,
} from 'bootstrap-vue'

import { ref, onUnmounted } from '@vue/composition-api'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BButton,
    BProgress,
    BFormDatepicker,
  },
  data: () => ({
    locale: 'de',
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
  }),
  directives: {
    'b-modal': VBModal,
  },
  props: {
    baseData: {
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
    plantUser: {
      type: Object,
      required: true,
      default: () => {},
    },
    completetionStatus: {
      type: Number,
      required: false,
      default: () => 0,
    },
  },
  setup(props, ctx) {
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const toast = useToast()

    const plantTitle = ref({})

    const getPlantTitle = titleString => {
      const titleArray = titleString.split(',')

      plantTitle.value.title = `${titleArray[0] ? titleArray[0] : ''}, ${titleArray[1] ? titleArray[1] : ''}`
      plantTitle.value.googleLink = `https://www.google.com/maps/place/${titleArray[0] ? titleArray[0] : ''}, ${titleArray[1] ? titleArray[1] : ''}`
      plantTitle.value.person = titleArray[2] ? titleArray[2] : ''
    }
    getPlantTitle(props.baseData.title)

    const clonePlant = () => {
      ctx.root.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie Anlage kopieren möchten.', {
          title: 'Anlage kopieren?',
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
            store.dispatch(`${STORE_MODULE_NAME}/clonePlant`, { plantId: router.currentRoute.params.id })
              .then(res => {
                if (res.data.status === 200) {
                  //  router.replace({ name: 'power-plant-detail', params: { id: res.data.message } })
                  router.replace({ name: 'user-detail', params: { id: props.plantUser.id } })
                  //  router.replace({ name: 'power-plant-detail', params: { id: res.data.message } })
                }
              })
          }
        })
    }

    const updatePlantInUse = () => {
      console.log(props.baseData)
      store.dispatch(`${STORE_MODULE_NAME}/updatePlantStatusWithDate`, { plantId: props.baseData.id, status: 'plantInUse', date: props.baseData.plantInUseDate })
        .then(() => {
          /* eslint-disable no-console */
          toast({
            component: ToastificationContent,
            props: {
              title: 'Status aktualisiert',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          /* eslint-enable no-console */
        })
        .catch(() => {
          /* eslint-disable no-console */
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
          /* eslint-enable no-console */
        })
    }

    return {
      plantTitle,
      clonePlant,
      updatePlantInUse,
    }
  },
}
</script>
