<template>
  <div>
    <h1
      style="font-weight: bold;"
    >
      {{ baseData.title }}
    </h1>
    <b-link
      :to="{ name: 'user-detail' }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Kundenportal
    </b-link>
    <br>
    <br>
    <br>
    <b-card>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
          style="padding-top:5px;"
        >
          <h3>Begehungstermin: {{ baseData.inspectionCheckDate | moment("DD.MM.YYYY | hh:mm") }}</h3>
        </b-col>
      </b-row>
    </b-card>
    <!-- style="background-color:#f8f8f8" -->
    <!-- border-variant="primary" -->
    <b-card>
      <b-card-body>
        <b-row
          align-v="center"
        >
          <b-col
            lg="1"
            md="12"
            style="min-width:100px;"
          >
            <div
              :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
            >
              <b-avatar
                size="70px"
                variant="light-primary"
              >
                <feather-icon
                  icon="FileIcon"
                  size="40px;"
                />
              </b-avatar>
            </div>
          </b-col>
          <b-col
            :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
          >
            <h2>Upload Energieabrechnung</h2>
            Damit wir Sie im Zuge des Begehungstermins effizienter beraten können, laden Sie bitte Ihre letzte Energieabrechnung hoch.
          </b-col>
          <b-col
            lg="2"
            md="12"
          >
            <b-button
              v-ripple.400="'rgba(113, 102, 240, 0.15)'"
              :variant="segmentStatusInfo.powerBill.variant"
              pill
              block
              :to="{ name: 'upload-power-bill', params: { plantId: baseData.id, canChange: baseData.roofImagesUploadedByClient } }"
              :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
            >
              {{ segmentStatusInfo.powerBill.label }}
            </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    <!-- border-variant="primary" -->
    <b-card>
      <b-card-body>
        <b-row
          align-v="center"
        >
          <b-col
            lg="1"
            md="12"
            style="min-width:100px;"
          >
            <div
              :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
            >
              <b-avatar
                size="70px"
                variant="light-primary"
              >
                <feather-icon
                  icon="CameraIcon"
                  size="40px;"
                />
              </b-avatar>
            </div>
          </b-col>
          <b-col
            :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
          >
            <h2>Upload Fotos Dach</h2>
            Damit wir Sie im Zuge des Begehungstermins effizienter beraten können, laden Sie bitte Fotos von Ihrem Dach hoch.
          </b-col>
          <b-col
            lg="2"
            md="12"
          >
            <b-button
              v-ripple.400="'rgba(113, 102, 240, 0.15)'"
              :variant="segmentStatusInfo.planPhotos.variant"
              pill
              block
              :to="{ name: 'upload-roof-images', params: { plantId: baseData.id, canChange: baseData.roofImagesUploadedByClient } }"
              :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
            >
              {{ segmentStatusInfo.planPhotos.label }}
            </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>

    <div
      v-if="baseData.calculationSentToCustomer !== true"
    >
      <b-row>
        <b-col
          cols="12"
        >
          <br>
          <b-button
            v-if="imagesConfirmed !== true && baseData.roofImagesUploadedByClient !== true"
            size="lg"
            variant="primary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="showLoading"
            @click="confirmImages"
          >
            <b-spinner
              v-if="showLoading"
            />
            <div v-if="showLoading === false">
              Upload abschliessen
            </div>
          </b-button>
          <b-card
            v-if="imagesConfirmed === true || baseData.roofImagesUploadedByClient === true"
            border-variant="primary"
            style="text-align: center;"
          >
            <b-row
              class="match-height"
            >
              <b-col
                cols="12"
                md="12"
                style="padding-top:5px;"
              >
                <h3>
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="30"
                    class="mr-0 mr-sm-50"
                  />
                  Upload abgeschlossen
                </h3>
              </b-col>
            </b-row>
          </b-card>
        </b-col>
      </b-row>
    </div>

    <b-card
      v-if="baseData.calculationSentToCustomer === true"
    >
      <b-card-body>
        <b-row
          align-v="center"
        >
          <b-col
            lg="1"
            md="12"
            style="min-width:100px;"
          >
            <div
              :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
            >
              <b-avatar
                size="70px"
                variant="light-primary"
              >
                <feather-icon
                  icon="FileTextIcon"
                  size="40px;"
                />
              </b-avatar>
            </div>
          </b-col>
          <b-col
            :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
          >
            <h2>Ihre PV-Projektierung und Prognoserechnung</h2>
            <!--Laden Sie bitte die Projektierungsunterlagen und die Prognoserechnung für Ihre geplanten Photovoltaik-Anlage herunter.-->
          </b-col>
          <b-col
            lg="2"
            md="12"
          >
            <b-button
              v-ripple.400="'rgba(113, 102, 240, 0.15)'"
              :variant="segmentStatusInfo.offerAndCalculation.variant"
              pill
              block
              :to="{ name: 'download-offer', params: { plantId: baseData.id } }"
              :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
            >
              {{ segmentStatusInfo.offerAndCalculation.label }}
            </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    <b-card
      v-if="baseData.contractsSentToCustomer === true"
    >
      <b-card-body>
        <b-row
          align-v="center"
        >
          <b-col
            lg="1"
            md="12"
            style="min-width:100px;"
          >
            <div
              :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
            >
              <b-avatar
                size="70px"
                variant="light-primary"
              >
                <feather-icon
                  icon="InboxIcon"
                  size="40px;"
                />
              </b-avatar>
            </div>
          </b-col>
          <b-col
            :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
          >
            <h2>Ihre Vertragsunterlagen</h2>
          </b-col>
          <b-col
            lg="2"
            md="12"
          >
            <b-button
              v-ripple.400="'rgba(113, 102, 240, 0.15)'"
              :variant="segmentStatusInfo.contracts.variant"
              pill
              block
              :to="{ name: 'contracts', params: { plantId: baseData.id, status: baseData.contractSignedAndUploaded } }"
              :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
            >
              {{ segmentStatusInfo.contracts.label }}
            </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BLink,
  BCard,
  BCardBody,
  BButton,
  BAvatar,
  BSpinner,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'

import {
  ref,
  onUnmounted,
  watch,
  computed,
} from '@vue/composition-api'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
/*
import BaseCard from './BaseCard.vue'
import Files from './Files.vue'
import PowerBill from './PowerBill.vue'
*/

export default {
  components: {
    //  BaseCard,
    //  Files,
    //  PowerBill,

    BRow,
    BCol,
    BLink,
    BCard,
    BCardBody,
    BButton,
    BAvatar,
    BSpinner,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      showLoading: false,
      imagesConfirmed: false,
    }
  },
  methods: {
    confirmImages() {
      this.$bvModal
        .msgBoxConfirm('Upload abschliessen', {
          title: '',
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
            this.showLoading = true
            store.dispatch('app-power-plant/simpleStatusUpdateFrontend', { id: router.currentRoute.params.id, status: 'roofImagesUploadedByClient', value: true })
              .then(() => {
                this.showLoading = false
                this.imagesConfirmed = true
                this.fetchBaseData()
              })
          }
        })
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

    const isMobile = ref(true)
    if (store.getters['app/currentBreakPoint'] === 'xl' || store.getters['app/currentBreakPoint'] === 'lg') {
      isMobile.value = false
    }

    const currentBreakPoint = computed(() => store.getters['app/currentBreakPoint'])
    watch(currentBreakPoint, val => {
      if (val === 'xl' || val === 'lg') {
        isMobile.value = false
      } else {
        isMobile.value = true
      }
    })

    const statusOptions = {
      new: { value: 0, label: 'Hochladen', variant: 'primary' },
      new_download: { value: 0, label: 'Herunterladen', variant: 'primary' },
      new_sign: { value: 0, label: 'Prüfen und unterfertigen', variant: 'primary' },
      inProgress: { value: 1, label: 'Hochgeladen', variant: 'outline-primary' },
      finished: { value: 2, label: 'Erledigt', variant: 'outline-secondary' },
      acceptedOffer: { value: 2, label: 'Heruntergeladen', variant: 'primary' },
      finishedOffer: { value: 2, label: 'Auftragsabsicht bekundet', variant: 'outline-secondary' },
      finishedContract: { value: 2, label: 'Auftrag abgeschlossen', variant: 'outline-secondary' },
      signedUploadedContract: { value: 2, label: 'in Prüfung', variant: 'outline-secondary' },
    }

    const segmentStatusInfo = ref({
      powerBill: statusOptions.new,
      planPhotos: statusOptions.new,
      offerAndCalculation: statusOptions.new_download,
      contracts: statusOptions.new_sign,
    })

    const baseData = ref({})
    const powerBillStatus = ref(0)

    const checkStatusProgress = () => {
      store.dispatch(`${STORE_MODULE_NAME}/getProgressStatus`, { id: router.currentRoute.params.id })
        .then(res => {
          if (segmentStatusInfo.value.powerBill.value < 2) {
            if (res.data.powerBillNr > 0) {
              segmentStatusInfo.value.powerBill = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.powerBill = statusOptions.new
            }
          }

          if (segmentStatusInfo.value.planPhotos.value < 2) {
            if (res.data.planPhotosNr > 0) {
              segmentStatusInfo.value.planPhotos = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.planPhotos = statusOptions.new
            }
          }
        })
    }

    const fetchBaseData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          baseData.value = response.data.payload

          if (response.data.payload.powerBillUploaded === true) {
            segmentStatusInfo.value.powerBill = statusOptions.finished
          } else {
            segmentStatusInfo.value.powerBill = statusOptions.new
          }

          if (response.data.payload.inspectionCheckFinished === true) {
            segmentStatusInfo.value.planPhotos = statusOptions.finished
          }

          if (response.data.payload.orderInterestAccepted === true) {
            segmentStatusInfo.value.offerAndCalculation = statusOptions.finished
          }

          if (response.data.payload.orderInterest === true) {
          //  if (response.data.payload.orderInterestAccepted === true) {
            segmentStatusInfo.value.offerAndCalculation = statusOptions.finishedOffer
          }

          if (response.data.payload.contractSignedAndUploaded === true) {
          //  if (response.data.payload.orderInterestAccepted === true) {
            segmentStatusInfo.value.contracts = statusOptions.signedUploadedContract
          }

          if (response.data.payload.contractFilesChecked === true) {
          //  if (response.data.payload.orderInterestAccepted === true) {
            segmentStatusInfo.value.contracts = statusOptions.finishedContract
          }

          checkStatusProgress()
        })
        .catch(error => {
          console.log(error)
        })
    }

    //  load data
    fetchBaseData()

    store.dispatch('app-power-plant/updateViewBy', { id: router.currentRoute.params.id })
      .then(() => {
        console.log('OK')
      })

    return {
      baseData,
      powerBillStatus,
      segmentStatusInfo,
      isMobile,
      fetchBaseData,
    }
  },
}
</script>
