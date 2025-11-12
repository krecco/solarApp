<template>
  <div>
    <b-link
      :to="{ name: 'user-detail', params: { id: pUser.id } }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Kunden
    </b-link>
    <br>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        md="12"
      >
        <base-card
          v-if="baseData.id !== undefined"
          :base-data="baseData"
          :plant-tariff="plantTariff"
          :plant-campaign="plantCampaign"
          :completetion-status="completetionStatus"
          :plantUser="pUser"
        />
      </b-col>
    </b-row>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        md="12"
      >
        <b-card>
          <b-row>
            <b-col
              cols="12"
              sm="10"
              style="margin-top:5px;"
            >
              <div class="d-flex">
                <feather-icon
                  icon="PhoneCallIcon"
                  size="25"
                />
                <h3 class="mb-0 ml-50">
                  Kundenkontakt
                </h3>
              </div>
            </b-col>
            <b-col
              cols="12"
              sm="1"
              style="text-align:right; font-size:1.3em; padding-top:5px;"
            >
              <b-badge
                v-if="segmentStatusInfo.customerContact.value >=0"
                :variant="segmentStatusInfo.customerContact.variant"
              >
                {{ segmentStatusInfo.customerContact.label }}
              </b-badge>
            </b-col>
            <b-col
              cols="12"
              sm="1"
              style="text-align:right;"
            >
              <b-button
                v-b-toggle.customerContactCard
                variant="outline-primary"
                class="btn-icon rounded-circle"
              >
                <feather-icon :icon="customerContactVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
              </b-button>
            </b-col>
          </b-row>
          <b-collapse
            id="customerContactCard"
            v-model="customerContactVisible"
            class="mt-2"
          >
            <customer-contact
              :base-data="baseData"
              class="mt-2 pt-75"
            />
          </b-collapse>
        </b-card>
      </b-col>
    </b-row>
    <b-overlay
      :show="showOverlay"
      position-static
    >
      <div
        style="padding-top:5px;"
      >
        <br>
        <h2 class="mb-0 ml-50">
          <b>Planung</b>
        </h2>
        <br>
      </div>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="TrendingUpIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Letzte Energieabrechnung
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.powerBill.value >=0"
                  :variant="segmentStatusInfo.powerBill.variant"
                >
                  {{ segmentStatusInfo.powerBill.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.powerBillCard
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="powerBillVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="powerBillCard"
              v-model="powerBillVisible"
              class="mt-2"
            >
              <power-bill-upload
                :power-bill-data="powerBillData"
                :base-data="baseData"
                :file-containers-data="fileContainersData"
                :file-clone-containers-data="fileCloneContainersData"
                :user="pUser"
                class="mt-2 pt-75"
              />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="CameraIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Planung  Fotos
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.planPhotos.value >=0"
                  :variant="segmentStatusInfo.planPhotos.variant"
                >
                  {{ segmentStatusInfo.planPhotos.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.planPhotos
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="planPhotosVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="planPhotos"
              v-model="planPhotosVisible"
              class="mt-2"
            >
              <plan-photos
                :base-data="baseData"
                :file-containers-data="fileContainersDataPlanPhotos"
                :user="pUser"
                class="mt-2 pt-75"
              />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="ColumnsIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Planungsdokument
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.planDocument.value >=0"
                  :variant="segmentStatusInfo.planDocument.variant"
                >
                  {{ segmentStatusInfo.planDocument.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.planDocumentCard
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="planDocumentVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="planDocumentCard"
              v-model="planDocumentVisible"
              class="mt-2"
            >
              <plan-document-upload
                :power-bill-data="powerBillData"
                :base-data="baseData"
                :file-containers-data="fileContainersData"
                :user="pUser"
                :segment-status="segmentStatusInfo.planDocument.value"
                class="mt-2 pt-75"
              />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="CpuIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Anlagedaten und Prognoserechnung
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.plantCalculation.value >=0"
                  :variant="segmentStatusInfo.plantCalculation.variant"
                >
                  {{ segmentStatusInfo.plantCalculation.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.plantCalculationCard
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="plantCalculationVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="plantCalculationCard"
              v-model="plantCalculationVisible"
              class="mt-2"
            >
              <b-form-checkbox
                id="calculationfinished"
                v-model="baseData.calculationFinished"
                style="float:right;"
                switch
                inline
                :disabled="!baseData.planDocumentUploaded"
                @change="updateCalcumationStatus"
              >
                <!--
                implement later
                :disabled="isDisabled()"
                -->
                Prognoserechnung erledigt
              </b-form-checkbox>
              <power-bill
                :power-bill-data="powerBillData"
                :base-data="baseData"
                class="mt-2 pt-75"
              />
              <!-- prognoserechnung -->
              <calculation />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="BriefcaseIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Angebot mit Prognoserechnung
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.plantCalculationDocument.value >=0"
                  :variant="segmentStatusInfo.plantCalculationDocument.variant"
                >
                  {{ segmentStatusInfo.plantCalculationDocument.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.plantCalculationDocument
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="plantCalculationDocumentVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="plantCalculationDocument"
              v-model="plantCalculationDocumentVisible"
              class="mt-2"
            >
              <plant-calculation-and-letter
                :base-data="baseData"
                :file-containers-data="PlantCalculationAndLetterContainers"
                :user="pUser"
                class="mt-2 pt-75"
              />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
      <div
        style="padding-top:5px;"
      >
        <br>
        <h2 class="mb-0 ml-50">
          <b>Vertragsdaten</b>
        </h2>
        <br>
      </div>
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <b-card>
            <b-row>
              <b-col
                cols="12"
                sm="10"
                style="margin-top:5px;"
              >
                <div class="d-flex">
                  <feather-icon
                    icon="InboxIcon"
                    size="25"
                  />
                  <h3 class="mb-0 ml-50">
                    Verträge und Vollmachten
                  </h3>
                </div>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right; font-size:1.3em; padding-top:5px;"
              >
                <b-badge
                  v-if="segmentStatusInfo.contractFiles.value >=0"
                  :variant="segmentStatusInfo.contractFiles.variant"
                >
                  {{ segmentStatusInfo.contractFiles.label }}
                </b-badge>
              </b-col>
              <b-col
                cols="12"
                sm="1"
                style="text-align:right;"
              >
                <b-button
                  v-b-toggle.contractFiles
                  variant="outline-primary"
                  class="btn-icon rounded-circle"
                >
                  <feather-icon :icon="contractFilesVisible ? 'ArrowUpIcon' : 'ArrowDownIcon'" />
                </b-button>
              </b-col>
            </b-row>
            <b-collapse
              id="contractFiles"
              v-model="contractFilesVisible"
              class="mt-2"
            >
              <!--
              <b-button
                v-if="baseData.propertyOwnerListFinished==true && baseData.contractsReviewed !== true"
                variant="primary"
                block
                style="margin-top:50px; margin-bottom:20px;"
                @click="updateOrderInterest"
              >
                Auftragsabsicht angenommen, Verträge und Vollmachten generieren!
              </b-button>
              -->
              <contracts
                :base-data="baseData"
                :file-containers-data="PlantContractContainers"
                :user="pUser"
                class="mt-2 pt-75"
              />
            </b-collapse>
          </b-card>
        </b-col>
      </b-row>
     </b-overlay>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BLink,
  BCard,
  BCollapse,
  VBToggle,
  BButton,
  BBadge,
  BFormCheckbox,
  BOverlay,
} from 'bootstrap-vue'

import {
  ref,
  onUnmounted,
  watch,
  computed,
  //  nextTick,
} from '@vue/composition-api'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { useToast } from 'vue-toastification/composition'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
import BaseCard from './BaseCard.vue'
import Contracts from './Contracts.vue'
//  import Gallery from './Gallery.vue'
import CustomerContact from './CustomerContact.vue'
import PowerBillUpload from './PowerBillUpload.vue'
import PlanDocumentUpload from './PlanDocumentUpload.vue'
import PlanPhotos from './PlanPhotos.vue'
import PowerBill from './PowerBill.vue'
import Calculation from './calculation/Display.vue'
import PlantCalculationAndLetter from './PlantCalculationAndLetter.vue'

export default {
  components: {
    BaseCard,
    Contracts,
    //  Gallery,
    CustomerContact,
    PowerBillUpload,
    PlanDocumentUpload,
    PlantCalculationAndLetter,
    BRow,
    BCol,
    BLink,
    BCard,
    BCollapse,
    BButton,
    BBadge,
    PlanPhotos,
    PowerBill,
    Calculation,
    BFormCheckbox,
    BOverlay,
  },
  directives: {
    'b-toggle': VBToggle,
    Ripple,
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

    const toast = useToast()

    const solarPlantUpdatedAt = computed(() => store.getters[`${STORE_MODULE_NAME}/solarPlantUpdatedAt`])

    const baseData = ref({})
    const pUser = ref({})
    const plantTariff = ref({ title: '' })
    const plantCampaign = ref({ title: 'Keine Kampagne' })
    const fileContainersData = ref([])
    const powerBillData = ref({})
    const powerBillVisible = ref(false)
    const customerContactVisible = ref(false)
    const planPhotosVisible = ref(false)
    const planDocumentVisible = ref(false)
    const plantCalculationVisible = ref(false)
    const plantCalculationDocumentVisible = ref(false)
    const contractFilesVisible = ref(false)
    const completetionStatus = ref(0)
    const showOverlay = ref(false)

    const statusOptions = {
      none: { value: -1, label: '', variant: '' },
      new: { value: 0, label: 'offen', variant: 'light-secondary' },
      newFile: { value: 0, label: 'offen', variant: 'light-secondary' },
      inProgress: { value: 1, label: 'in Arbeit', variant: 'light-info' },
      finished: { value: 2, label: 'erledigt', variant: 'light-primary' },
    }

    const segmentStatusInfo = ref({
      customerContact: statusOptions.new,
      powerBill: statusOptions.new,
      planPhotos: statusOptions.new,
      planDocument: statusOptions.new,
      plantCalculation: statusOptions.new,
      plantCalculationDocument: statusOptions.new,
      contractFiles: statusOptions.new,
    })

    // check for inprogress status -- autoverify will be added later if needed
    const checkStatusProgress = () => {
      store.dispatch(`${STORE_MODULE_NAME}/getProgressStatus`, { id: router.currentRoute.params.id })
        .then(res => {
          if (segmentStatusInfo.value.powerBill.value < 2) {
            if (res.data.powerBillNr > 0) {
              segmentStatusInfo.value.powerBill = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.powerBill = statusOptions.newFile
            }
          }

          if (segmentStatusInfo.value.planPhotos.value < 2) {
            if (res.data.planPhotosNr > 0) {
              segmentStatusInfo.value.planPhotos = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.planPhotos = statusOptions.new
            }
          }

          if (segmentStatusInfo.value.planDocument.value < 2) {
            if (res.data.planDocumentNr > 0) {
              segmentStatusInfo.value.planDocument = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.planDocument = statusOptions.newFile
            }
          }
        })
    }

    const fileContainersDataPlanPhotos = ref([])
    const PlantCalculationAndLetterContainers = ref([])
    const PlantContractContainers = ref([])
    const fileCloneContainersData = ref([])

    const fetchCloneContainers = cloneSource => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchFileContainers`, { id: cloneSource })
        .then(response => {
          fileCloneContainersData.value = response.data.payload

          setTimeout(() => {
            //  Planung Fotos
            fileContainersDataPlanPhotos.value = response.data.payload.filter(c => (c.contextType === 3))
          }, 1000)
        })
    }

    const fetchBaseData = baseOnly => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          baseData.value = response.data.payload
          //  inspectionCheckDate.value = response.data.payload.inspectionCheckDate
          if (baseData.value.cloneSource !== '') {
            fetchCloneContainers(baseData.value.cloneSource)
          }

          //  set statuses
          //  it cant be turned off -- no else
          completetionStatus.value = 0
          if (response.data.payload.inspectionMailSent === true) {
            completetionStatus.value += 16.67
            segmentStatusInfo.value.customerContact = statusOptions.finished
          } else {
            console.log('cheking status')
            if (response.data.payload.inspectionCheckInProgress === true) {
              segmentStatusInfo.value.customerContact = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.customerContact = statusOptions.new
            }
          }

          if (response.data.payload.powerBillUploaded === true) {
            segmentStatusInfo.value.powerBill = statusOptions.finished
          } else {
            segmentStatusInfo.value.powerBill = statusOptions.new
          }

          if (response.data.payload.planDocumentUploaded === true) {
            segmentStatusInfo.value.planDocument = statusOptions.finished
          } else {
            segmentStatusInfo.value.planDocument = statusOptions.new
          }

          //  it cant be turned off -- no else
          if (response.data.payload.inspectionCheckFinished === true) {
            completetionStatus.value += 16.66
            segmentStatusInfo.value.planPhotos = statusOptions.finished
          }

          if (response.data.payload.calculationFinished === true) {
            segmentStatusInfo.value.plantCalculation = statusOptions.finished
            segmentStatusInfo.value.plantCalculationDocument = statusOptions.inProgress
          } else {
            console.log('cheking status')
            if (response.data.payload.calculationInProgress === true) {
              segmentStatusInfo.value.plantCalculation = statusOptions.inProgress
            } else {
              segmentStatusInfo.value.plantCalculation = statusOptions.new
            }
          }

          if (response.data.payload.calculationSentToCustomer === true) {
            segmentStatusInfo.value.plantCalculationDocument = statusOptions.finished
            completetionStatus.value += 16.66
          }

          if (response.data.payload.orderInterest === true) {
            segmentStatusInfo.value.contractFiles = statusOptions.inProgress
            completetionStatus.value += 16.66
          }

          if (response.data.payload.contractsSentToCustomer === true) {
            completetionStatus.value += 16.66
          }

          if (response.data.payload.contractFilesChecked === true) {
            segmentStatusInfo.value.contractFiles = statusOptions.finished
            completetionStatus.value += 16.66
          }

          checkStatusProgress()

          if (baseOnly === false) {
            if (response.data.payload.tariff != null) {
              store.dispatch(`${STORE_MODULE_NAME}/fetchTarriff`, { id: response.data.payload.tariff })
                .then(responseTariff => { plantTariff.value = responseTariff.data.payload })
            }

            if (response.data.payload.campaignId != null) {
              store.dispatch(`${STORE_MODULE_NAME}/fetchCampaign`, { id: response.data.payload.campaignId })
                .then(responseCampaign => { plantCampaign.value = responseCampaign.data })
            }
          }
        })
    }

    const updateCalcumationStatus = () => {
      console.log(baseData.value.calculationFinished)
      store.dispatch('app-power-plant/simpleStatusUpdate', { id: baseData.value.id, status: 'calculationFinished', value: baseData.value.calculationFinished })
        .then(() => {
          store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          toast({
            component: ToastificationContent,
            props: {
              title: 'Status aktualisiert',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })

          //  generate files
          if (baseData.value.calculationFinished === true) {
            //  fake generating ui
            showOverlay.value = true
            setTimeout(() => {
              store.commit('app-power-plant/updateCalculationAndLetterChangedAt')
              showOverlay.value = false
            }, 3000)

            store.dispatch('app-power-plant/generateCalculation', { plantId: baseData.value.id, userId: pUser.value.id })
              .then(() => {
                PlantCalculationAndLetterContainers.value[0].rs = 2
              })
            store.dispatch('app-power-plant/generateLetter', { plantId: baseData.value.id, userId: pUser.value.id })
              .then(() => {
                PlantCalculationAndLetterContainers.value[1].rs = 2
              })
          }
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateFileStatus error response')
            /* eslint-enable no-console */
          }
        })
    }

    store.dispatch(`${STORE_MODULE_NAME}/getPowerBillData`, { id: router.currentRoute.params.id })
      .then(response => { powerBillData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          powerBillData.value = undefined
        }
      })

    store.dispatch(`${STORE_MODULE_NAME}/getProjectUser`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { pUser.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          pUser.value = undefined
        }
      })

    const fetchContainers = () => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchFileContainers`, { id: router.currentRoute.params.id })
        .then(response => {
          fileContainersData.value = response.data.payload

          //  Planung Fotos
          fileContainersDataPlanPhotos.value = response.data.payload.filter(c => (c.contextType === 3))

          /*  Anschreiben und Prognoserechnung (upload)
          21 = Prognoserechnung
          23 = Anschreiben
          */
          PlantCalculationAndLetterContainers.value = []
          PlantCalculationAndLetterContainers.value.push(response.data.payload.filter(c => (c.type === 21))[0])
          PlantCalculationAndLetterContainers.value.push(response.data.payload.filter(c => (c.type === 23))[0])

          /* Vertragsdaten
          24 = Vertrag Energieeinsparung
          25 = Vertrag Verrechnungsblatt
          27 = Vollmacht Abwicklung
          29 = Vollmacht Energieabrechnung
          291 = Vollmacht Netzbetreiber
          201 = SEPA

          28 = Backend only files
          */
          PlantContractContainers.value = []
          PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 24))[0])
          PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 25))[0])
          PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 27))[0])
          PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 29))[0])
          PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 291))[0])

          if (typeof (response.data.payload.filter(c => (c.type === 201))[0]) !== 'undefined') {
            PlantContractContainers.value.push(response.data.payload.filter(c => (c.type === 201))[0])
          }
        })
    }
    //  load data
    fetchBaseData(false)
    fetchContainers()

    setTimeout(() => {
      watch(solarPlantUpdatedAt, (val, oldVal) => {
        if (val > oldVal) {
          fetchBaseData(true)
        }
      })
    }, 1500)

    const contractsGeneratedAt = computed(() => store.getters['app-power-plant/contractsGeneratedAt'])
    watch(contractsGeneratedAt, (val, oldVal) => {
      if (val > oldVal) {
        fetchContainers()
      }
    })

    let preventAdminUpdate = false
    if (typeof router.currentRoute.params.preventAdminUpdate !== 'undefined') {
      preventAdminUpdate = true
    }

    store.dispatch('app-power-plant/updateViewBy', { id: router.currentRoute.params.id, preventAdminUpdate })
      .then(() => {
        console.log('OK')
      })

    //  temp placeholder
    /*
    const generateContractFiles = () => {
      store.dispatch('app-power-plant/generateEnergySavingContract', { plantId: baseData.value.id, userId: pUser.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateEnergySavingContract generated')
        })

      store.dispatch('app-power-plant/generateContractBillingSheet', { plantId: baseData.value.id, userId: pUser.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateContractBillingSheet generated')
        })

      store.dispatch('app-power-plant/generateMandateCompletion', { plantId: baseData.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateCompletion generated')
        })

      store.dispatch('app-power-plant/generateMandateBilling', { plantId: baseData.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateBilling generated')
        })

      store.dispatch('app-power-plant/generateMandateBillingNet', { plantId: baseData.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateBillingNet generated')
        })

      store.dispatch('app-power-plant/generateSepa', { plantId: baseData.value.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateSepa generated')
        })

      setTimeout(() => {
        fetchContainers()
      }, 1500)
    }
    */

    /*
    const updateOrderInterest = () => {
      store.dispatch('app-power-plant/updateWorkflowStatus', { plantId: baseData.value.id, status: 'orderInterest' })
        .then(() => {
          generateContractFiles()
          store.commit('app-power-plant/updateSolarPlantUpdatedAt')
        })
        .catch(error => {
          if (error.status === 404) {
          }
        })
    }
    */

    return {
      baseData,
      pUser,
      fileContainersData,
      plantTariff,
      powerBillData,
      powerBillVisible,
      customerContactVisible,
      planPhotosVisible,
      plantCampaign,
      //  inspectionCheckDate,
      fileContainersDataPlanPhotos,
      PlantCalculationAndLetterContainers,
      PlantContractContainers,
      completetionStatus,
      segmentStatusInfo,
      planDocumentVisible,
      plantCalculationVisible,
      plantCalculationDocumentVisible,
      updateCalcumationStatus,
      contractFilesVisible,
      fileCloneContainersData,
      showOverlay,

      //  temp
      //  updateOrderInterest,
    }
  },
}
</script>

<style>
.position-absolute {
  position: fixed !important;
}
</style>
