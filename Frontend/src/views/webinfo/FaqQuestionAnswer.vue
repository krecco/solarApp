<template>
  <div v-if="options">
    <div class="d-flex align-items-center">
      <div>
        <h4 class="mb-0">
          {{ options.title }}
        </h4>
        <span>{{ options.subtitle }}</span>
      </div>
    </div>
    <app-collapse
      id="faq-payment-qna"
      accordion
      type="margin"
      class="mt-2"
    >
      <app-collapse-item
        v-for="( data, index) in options.data"
        :id="data.id"
        :key="index"
        :title="`${data.firstName} ${data.lastName}, ${data.addressPostNr} ${data.addressCity}  (${data.email} | ${data.phone})`"
      >
        Kunde: {{ data.titlePrefix }} {{ data.firstName }} {{ data.lastName }}<span v-if="data.titleSuffix">, {{ data.titleSuffix }}</span>
        <br>
        Adresse: {{ data.addressStreet }}, {{ data.addressPostNr }} {{ data.addressCity }}
        <br>
        Telefon: {{ data.phone }}
        <br>
        Email: {{ data.email }}
        <br><br>
        Gewünschter Telefontermin: {{ data.desiredDateTime | moment("DD.MM. YYYY, h:mm:ss") }}
        <br>
        Investor: <span v-if="data.isInvestor === true">Ja</span><span v-if="data.isInvestor === false">Nein</span>
        <br>
        <br>
        Photovoltaik - Anlagerechner:
        <br>
        <hr>
        Stromverbrauch pro Jahr: {{ parseJson("powerusage", data.webCalculation) }} kWh
        <br>
        Objekt für die PV Anlage: {{ parseJson("propertytype", data.webCalculation) }}
        <br>
        Aktuelle Heizung: {{ parseJson("heatingtype", data.webCalculation) }}
        <br>
        Empfohlene Anlagengröße (kWp): {{ parseJson("systemsize", data.webCalculation) }} kWp
        <hr>
        <br>
        <b-row>
          <b-col
            cols="12"
            md="6"
          >
            <b-button
              variant="outline-primary"
              @click="createUserFromWebInfo(data.id)"
            >
              Benutzerkonto aus Webnachricht erstellen
            </b-button>
          </b-col>
          <b-col
            cols="12"
            md="6"
          >
            <b-button
              variant="outline-warning"
              style="float:right"
              @click="rejectWebInfo(data.id)"
            >
              Löschen
            </b-button>
          </b-col>
        </b-row>
      </app-collapse-item>

    </app-collapse>

    <!--/ collapse -->
  </div>
</template>

<script>
import { BButton, BRow, BCol } from 'bootstrap-vue'
import {
  onUnmounted,
} from '@vue/composition-api'

import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItem from '@core/components/app-collapse/AppCollapseItem.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store'
import storeModule from './storeModule'

export default {
  components: {
    BButton,
    AppCollapseItem,
    AppCollapse,
    BRow,
    BCol,
  },
  props: {
    options: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    parseJson(key, jsonString) {
      try {
        return JSON.parse(jsonString)[key]
      } catch (e) {
        return 'N/A'
      }
    },
    getGender(type) {
      const genderOptions = [
        { value: 1, text: 'Herr' },
        { value: 2, text: 'Frau' },
        { value: 3, text: 'Divers' },
        { value: 11, text: 'Familie' },
        { value: 12, text: 'Verein' },
        { value: 13, text: 'Gemeinde' },
      ]

      return genderOptions.find(x => x.value === type).text
    },
  },
  setup(props) {
    const toast = useToast()
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'web-info'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const fetchData = status => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchWebInfoList`, { status })
        .then(response => {
          /* eslint-disable no-param-reassign */
          props.options.data = response.data
          /* eslint-enable no-param-reassign */
        })
        .catch(() => {
          /* eslint-disable no-param-reassign */
          props.options.data = []
          /* eslint-enable no-param-reassign */
        })
    }

    const rejectWebInfo = id => {
      store.dispatch(`${STORE_MODULE_NAME}/updateWebInfoListStatus`, { id, status: 0 })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'WEB Nachricht gelöscht.',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          fetchData(1)
          store.commit('app-navbar/updateUpdatedAt')
        })
    }

    const createUserFromWebInfo = id => {
      store.dispatch(`${STORE_MODULE_NAME}/createUserFromWebInfo`, { id })
        .then(response => {
          if (response.data.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Benutzer erstellt.',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            fetchData(1)
            store.commit('app-navbar/updateUpdatedAt')
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler beim Benutzer anlegen, bitte webinfo Daten überprüfen!',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
    }

    return {
      rejectWebInfo,
      createUserFromWebInfo,
    }
  },
}
</script>
