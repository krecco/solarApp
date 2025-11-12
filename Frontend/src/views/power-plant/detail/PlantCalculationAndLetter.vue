<template>
  <div>
    <b-row>
      <b-col
        cols="12"
        style="padding-bottom: 20px;"
      >
        <b-form-checkbox
          v-if="canFinishSegment===true"
          id="calculationChecked"
          v-model="baseData.calculationChecked"
          style="float:right;"
          switch
          inline
          :disabled="isDisabled()"
          @change="updateCalculationStatus"
        >
          Prognoserechnung erledigt
        </b-form-checkbox>
        <b-form-checkbox
          v-if="canFinishSegment!==true"
          style="float:right;"
          switch
          inline
          disabled="true"
        >
          Prognoserechnung erledigt
        </b-form-checkbox>
      </b-col>
      <b-col
        cols="12"
        md="12"
      >
        <b-table
          striped
          responsive
          :hover="true"
          :items="fileContainersData"
          :fields="userDocumentsFields"
          class="mb-0"
        >
          <!-- Headers -->
          <template v-slot:head(t0)="data">
            <div class="text-right">{{ data.label }}</div>
          </template>
          <template #cell(show_details)="row">
            <b-form-checkbox
              :id="`idFilePlant_${row.item.id}`"
              v-model="row.detailsShowing"
              @change="loadDocumentFiles(row, true)"
            >
              <span class="vs-checkbox">
                <span class="vs-checkbox--check">
                  <i class="vs-icon feather icon-check" />
                </span>
              </span>
              <span class="vs-label">{{ row.detailsShowing ? 'Ausblenden' : 'Mehr' }}</span>
            </b-form-checkbox>
          </template>

          <template #row-details="row">
            <br>
            <div
              v-if="row.item.type === 21"
              style="width:100%; text-align:right; padding-bottom:10px;"
            >
              <!--Information: In der Prognoserechnung sehen Sie abhängig vom Eigenverbrauch und Überschussstrom die voraussichtlichen Kosten der PV-Anlage unter Berücksichtigung der aktuellen Fördermöglichkeiten.-->
              <br><br>
              <b-button
                v-if="baseData.calculationSentToCustomer !== true"
                variant="outline-primary"
                size="sm"
                class="mr-0 mb-sm-0 mr-0 mr-sm-1"
                :block="$store.getters['app/currentBreakPoint'] === 'xs'"
                type="button"
                @click="generateCalculation($router.currentRoute.params.id, User.id, row)"
              >
                <feather-icon
                  icon="CpuIcon"
                  class="mr-50"
                />
                Prognoserechnung neu generieren
              </b-button>
            </div>
            <div
              v-if="row.item.type === 23"
              style="width:100%; text-align:right; padding-bottom:10px;"
            >
              <!--
              Click Here:
              <a
                v-auth-href
                :href="`${apiUrl}/file-generator/project-participation/${$router.currentRoute.params.id}/${User.id}/false`"
              >
                Download
              </a>
              -->
              <b-button
                v-if="baseData.calculationSentToCustomer !== true"
                variant="outline-primary"
                size="sm"
                class="mr-0 mb-sm-0 mr-0 mr-sm-1"
                :block="$store.getters['app/currentBreakPoint'] === 'xs'"
                type="button"
                @click="generateLetter($router.currentRoute.params.id, User.id, row)"
              >
                <feather-icon
                  icon="CpuIcon"
                  class="mr-50"
                />
                Anschreiben neu generieren
              </b-button>
            </div>
            <div>
              <b-row
                class="mb-2"
              >
                <b-col
                  md="12"
                  class="mb-1"
                >
                  <b-table
                    :ref="row.item.id"
                    striped
                    responsive
                    :hover="true"
                    :items="row.item.files"
                    :fields="userDocumentFields"
                    class="mb-0"
                  >
                    <template
                      #cell(fileName)="data"
                    >
                      <a
                        v-if="data.item.fileContentType === 'application/pdf'"
                        v-b-modal.modal-previewPDFCalculation
                        nohref
                        :style="[data.index === 0 ? {'font-weight': 'bold'} : {'font-weight': 'normal'}]"
                        @click="loadPreview(data.item.id)"
                      >
                        {{ data.item.fileName }}
                      </a>
                      <a
                        v-if="data.item.fileContentType !== 'application/pdf'"
                        v-b-modal.modal-previewImageCalculation
                        nohref
                        :style="[data.index === 0 ? {'font-weight': 'bold'} : {'font-weight': 'normal'}]"
                        @click="loadPreview(data.item.id)"
                      >
                        {{ data.item.fileName }}
                      </a>
                    </template>
                    <template
                      #cell(icon)="data"
                    >
                      <feather-icon
                        v-if="data.value === 'application/pdf'"
                        class="mr-1"
                        icon="FileTextIcon"
                      />
                      <feather-icon
                        v-else
                        class="mr-1"
                        icon="ImageIcon"
                      />
                      <feather-icon
                        v-if="data.item.generated === true"
                        class="mr-1"
                        icon="CpuIcon"
                      />
                      <feather-icon
                        v-if="data.item.backendUserUpload === true"
                        class="mr-1"
                        icon="UploadIcon"
                      />
                      <feather-icon
                        v-if="data.item.backendUserUpload !== true && data.item.generated !== true"
                        class="mr-1"
                        icon="UploadIcon"
                        style="color:#df4d12"
                      />
                    </template>
                    <template v-slot:cell(t0)="data">
                      <div class="text-left">{{ data.value | moment("DD.MM. YYYY") }}</div>
                    </template>
                    <!-- Column: Actions -->
                    <template #cell(actions)="row">
                      <div
                        class="text-right"
                      >
                        <a
                          v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                          v-b-modal.modal-previewPDFCalculation
                          nohref
                          @click="loadPreview(row.item.id)"
                        >
                          <feather-icon
                            class="mr-1"
                            icon="EyeIcon"
                          />
                        </a>

                        <a
                          v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                          v-b-modal.modal-previewImageCalculation
                          nohref
                          @click="loadPreview(row.item.id)"
                        >
                          <feather-icon
                            class="mr-1"
                            icon="EyeIcon"
                          />
                        </a>

                        <a
                          v-auth-href
                          :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                        >
                          <feather-icon
                            class="mr-1"
                            icon="ArrowDownCircleIcon"
                          />
                        </a>
                        <a
                          v-if="baseData.calculationSentToCustomer !== true"
                          nohref
                          @click="deleteFile(row.item.id, row.item.idFileContainer)"
                        >
                          <feather-icon
                            class="mr-1"
                            icon="XCircleIcon"
                          />
                        </a>
                      </div>
                    </template>
                  </b-table>
                  <!--
                  <b-button
                    v-b-modal.modal-uploadCalculation
                    size="sm"
                    variant="outline-info"
                    class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                    type="button"
                    block
                    :disabled="isDisabled()"
                    @click="selectedFileContainer(row.item.id)"
                  >
                    <feather-icon
                      icon="PlusCircleIcon"
                      size="15"
                    />
                    Dokument hochladen
                  </b-button>
                  -->
                </b-col>
              </b-row>
              <b-button
                size="sm"
                variant="outline-secondary"
                hidden
                @click="row.toggleDetails"
              >
                Hide Details
              </b-button>
            </div>
          </template>
          <template #cell(rs)="data">
            <!--
            <b-badge
              v-if="data.value === 1"
              pill
              variant="warning"
            >
              Neu
            </b-badge>
            <b-badge
              v-if="data.value === 2"
              pill
              variant="info"
            >
              Hochgeladen
            </b-badge>
            <b-badge
              v-if="data.value === 3"
              pill
              variant="danger"
            >
              Geändert
            </b-badge>
            <b-badge
              v-if="data.value === 4"
              pill
              variant="success"
            >
              Überprüft
            </b-badge>
            -->
            <b-badge
              v-if="data.value === 2"
              pill
              variant="info"
            >
              erstellt
            </b-badge>
            <b-badge
              v-if="data.value === 3"
              pill
              variant="info"
            >
              erstellt
            </b-badge>
          </template>
          <template #cell(icon)>
            <feather-icon
              icon="ImageIcon"
            />
          </template>
          <template #cell(type)="data">
            <div
              style="font-size:18px; font-weight:bold;"
            >
              <div @click="showFileList(data.item.id)">
                <div v-if="data.value === 21">
                  Prognoserechnung
                </div>
                <div v-if="data.value === 23">
                  Anschreiben
                </div>
              </div>
            </div>
          </template>
          <template v-slot:cell(t0)="data">
            <div class="text-right">
              {{ data.value | moment("DD.MM. YYYY") }}
            </div>
          </template>
        </b-table>
        <br><br>
        <b-form-checkbox
          id="calculationCheckedRegMail"
          v-model="baseData.calculationMailWithRegistration"
          switch
          inline
          calculationMailWithRegistration
          @change="calculationMailWithRegistration"
          :disabled="isDisabled()"
        >
          Mailversand MIT Registrierung
        </b-form-checkbox>
        <br><br>
        <b-button
          v-if="baseData.calculationChecked==true"
          size="sm"
          variant="outline-primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          type="button"
          block
          :disabled="isDisabled() || savingCalculationMailWithRegistration"
          @click="updateCalculationSentStatus()"
        >
          <feather-icon
            v-if="showLoading === false"
            icon="SendIcon"
            size="15"
          />
          <b-spinner
            v-if="showLoading"
            small
          />
          Mailversand
        </b-button>
      </b-col>
    </b-row>
    <b-modal
      id="modal-uploadCalculation"
      title="Datei hochladen"
      ok-title="Schließen"
      ok-only
      no-close-on-backdrop
      no-close-on-esc
      size="lg"
      @ok="hadleFileUploadClose"
    >
      <file-pond
        ref="pond"
        name="fileUpload"
        label-idle="Dateien hier ablegen..."
        allow-multiple="true"
        accepted-file-types="image/jpeg, image/png, application/pdf"
        :server="{
          url: apiUrl,
          process: {
            url: `/user/upload-multipart-file/${fileContainerSelectedId}`,
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${accessToken}`
            },
          }
        }"
        allow-file-encode="true"
        allow-file-rename="true"
        allow-image-resize="true"
        image-resize-target-width="1280"
        image-resize-target-height="720"
        image-resize-mode="cover"
        :file-rename-function="fileRenameFunction"
        @processfile="handleProcessFile"
      />
    </b-modal>
    <b-modal
      id="modal-previewImageCalculation"
      title="Bildvorschau"
      ok-title="Schließen"
      ok-only
      size="xl"
    >
      <div
        v-if="loadingPreviewFile === true"
        style="text-align: center;"
      >
        <b-spinner /><br>
        Wird geladen ...
      </div>
      <div
        style="text-align: center; background-color:black;"
      >
        <b-img
          :src="previewFile"
          style="max-height: 85vh;"
          fluid
        />
      </div>
    </b-modal>

    <b-modal
      id="modal-previewPDFCalculation"
      title="PDF vorschau"
      ok-title="Schließen"
      ok-only
      size="xl"
    >
      <div
        v-if="loadingPreviewFile === true"
        style="text-align: center;"
      >
        <b-spinner /><br>
        Wird geladen ...
      </div>
      <object
        :data="previewFile"
        style="width: 100%; height:85vh;"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BRow,
  BCol,
  BButton,
  BTable,
  BModal,
  VBModal,
  BBadge,
  BFormCheckbox,
  BSpinner,
  BImg,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'
import { ref, watch, computed } from '@vue/composition-api'
import { $apiUrl } from '@serverConfig'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import vueFilePond from 'vue-filepond'
import { v4 as uuidv4 } from 'uuid'
import { base64StringToBlob } from 'blob-util'

//  Import plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.esm'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm'
import FilePondPluginFileEncode from 'filepond-plugin-file-encode'
import FilePondPluginFileRename from 'filepond-plugin-file-rename'
import FilePondPluginImageResize from 'filepond-plugin-image-resize'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'

import router from '@/router'
//  import router from '@/router'
import store from '@/store'

//  Import styles
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

export default {
  components: {
    BRow,
    BCol,
    BButton,
    BTable,
    BModal,
    BBadge,
    BFormCheckbox,
    BSpinner,
    BImg,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    fileContainersData: {
      type: Array,
      required: true,
    },
    User: {
      type: Object,
      required: true,
    },
    baseData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      previewFile: '',
      loadingPreviewFile: false,
      savingCalculationMailWithRegistration: false,
      showLoading: false,
      file1: [],
      selected: '',
      fileContainerSelectedId: '',
      option: ['Vollmacht', 'Grundbuchauszug', 'Verträg', 'Angebot', 'Other'],
      myFiles: [],
      apiUrl: $apiUrl,
      accessToken: localStorage.getItem('accessToken'),
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
    }
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        if (typeof (router.currentRoute.params.fileContainerId) !== 'undefined') {
          document.getElementById(`idFilePlant_${router.currentRoute.params.fileContainerId}`).click()
        }
      }, 250)
    })
  },
  methods: {
    showFileList(id) {
      document.getElementById(`idFilePlant_${id}`).click()
    },
    loadPreview(id) {
      this.previewFile = ''
      this.loadingPreviewFile = true
      store.dispatch('app-power-plant/getFileBase', { id })
        .then(response => {
          const blob = base64StringToBlob(response.data, response.headers['content-type'])
          const fileURL = URL.createObjectURL(blob)

          this.loadingPreviewFile = false
          this.previewFile = fileURL
        })
        .catch(error => {
          console.log(error)
        })
    },
    fileRenameFunction(file) {
      let fileName = `${this.$props.User.firstName.toLowerCase()}_${this.$props.User.lastName.toLowerCase()}_`
      const documentType = this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).type

      if (documentType === 21) {
        const plantNominalPower = this.$props.baseData.nominalPower.toFixed(2).toString().replace('.', '-')

        fileName = `${fileName}prognoserechnung_${plantNominalPower}kWp`
      } else if (documentType === 23) {
        fileName = `${fileName}anschreiben`
      }

      fileName = `${fileName}_${uuidv4()}`
      return `${fileName}${file.extension}`
    },

    selectedFileContainer(id) {
      this.fileContainerSelectedId = id
      //  store.dispatch('app-power-plant/fetchUserFileContainers', { id: router.currentRoute.params.id })
    },
    deleteFile(id, containerId) {
      //  open modal!

      this.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie die Datei löschen möchten.', {
          title: 'Datei löschen?',
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
            store.dispatch('app-power-plant/deleteFile', { id })
              .then(response => {
                /* eslint-disable no-param-reassign */
                this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */
                store.commit('app-power-plant/updateSolarPlantUpdatedAt')
                store.commit('app-power-plant/updateCalculationAndLetterChangedAt')
                store.dispatch('app-power-plant/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    /* eslint-ebable no-param-reassign */
                    store.commit('app-power-plant/updateSolarPlantGalleryUpdatedAt')
                  })
                  .catch(error => {
                    if (error.status === 404) {
                      /* eslint-disable no-console */
                      console.log('loadfiles error response')
                      /* eslint-enable no-console */
                    }
                  })
              })
              .catch(error => {
                if (error.status === 404) {
                  /* eslint-disable no-console */
                  console.log('updateDocument error response')
                  /* eslint-enable no-console */
                }
              })
          }
        })
    },
    /*
    setinspectionFinished() {
      store.dispatch('app-power-plant/finishInspection', { plantId: this.$props.baseData.id, postData: this.$props.baseData })
        .then(response => {
          console.log(response)
          if (response.status === 200) {
            store.commit('app-power-plant/updateSolarPlantUpdatedAt')
            this.$toast({
              component: ToastificationContent,
              props: {
                title: 'Begehungstermin durchgeführt',
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
        })
    },
    */
    hadleFileUploadClose() {
      store.dispatch('app-power-plant/getDocumentFiles', { id: this.fileContainerSelectedId })
        .then(response => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).files = response.data.payload
          /* eslint-ebable no-param-reassign */
          console.log('commiting to store')
          store.commit('app-power-plant/updateSolarPlantGalleryUpdatedAt')
          store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          store.commit('app-power-plant/updateCalculationAndLetterChangedAt')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('hadleFileUploadClose error response')
            /* eslint-enable no-console */
          }
        })
    },
    handleProcessFile(error, file) {
      const response = JSON.parse(file.serverId)

      /* eslint-disable no-param-reassign */
      this.$props.fileContainersData.find(x => x.id === response.message).rs = response.status
      /* eslint-ebable no-param-reassign */
    },

    updateCalculationStatus() {
      store.dispatch('app-power-plant/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'calculationChecked', value: this.$props.baseData.calculationChecked })
        .then(() => {
          store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Status aktualisiert',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateFileStatus error response')
            /* eslint-enable no-console */
          }
        })
    },

    calculationMailWithRegistration() {
      this.savingCalculationMailWithRegistration = true
      store.dispatch('app-power-plant/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'calculationMailWithRegistration', value: this.$props.baseData.calculationMailWithRegistration })
        .then(() => {
          this.savingCalculationMailWithRegistration = false
          //  store.commit('app-power-plant/updateSolarPlantUpdatedAt')
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Status aktualisiert',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateFileStatus error response')
            /* eslint-enable no-console */
          }
        })
    },

    updateCalculationSentStatus() {
      this.$bvModal
        .msgBoxConfirm('Anschreiben und Prognoserechnung wird an Kunden geschickt', {
          title: 'Mailversand',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'Ja',
          cancelTitle: 'Abbrechen',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            this.showLoading = true
            //  store.dispatch('app-power-plant/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'calculationSentToCustomer', value: this.$props.baseData.calculationSentToCustomer })
            store.dispatch('app-power-plant/updateWorkflowStatus', { plantId: this.$props.baseData.id, status: 'calculationSentToCustomer' })
              .then(() => {
                store.commit('app-power-plant/updateSolarPlantUpdatedAt')
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Status aktualisiert',
                    icon: 'CheckIcon',
                    variant: 'success',
                  },
                })
                this.showLoading = false
              })
              .catch(error => {
                if (error.status === 404) {
                  /* eslint-disable no-console */
                  console.log('updateFileStatus error response')
                  /* eslint-enable no-console */
                  this.showLoading = false
                }
              })
          }
        })
    },

    //  to be deprecated
    updateFileStatus() {
      store.dispatch('app-power-plant/updateFileStatus', { plantId: this.$props.baseData.id, status: this.$props.baseData.solarPlantFilesVerifiedByBackendUser })
        .then(() => {
          /* eslint-disable no-console */
          console.log('updateFileStatus updated')
          /* eslint-enable no-console */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateFileStatus error response')
            /* eslint-enable no-console */
          }
        })
    },

    /*
    updatePlantStatus(type) {
      let status = false

      if (type === 'filesVerified') {
        status = this.$props.baseData.solarPlantFilesVerifiedByBackendUser
      } else if (type === 'contractFinalized') {
        status = this.$props.baseData.contractFinalized
      } else if (type === 'plantInstalled') {
        status = this.$props.baseData.plantInstalled
      } else if (type === 'plantInUse') {
        status = this.$props.baseData.plantInUse
      }

      store.dispatch('app-power-plant/updatePlantStatus', { plantId: this.$props.baseData.id, type, status })
        .then(() => {
        })
        .catch(error => {
          if (error.status === 404) {
          }
        })
    },
    */

    getSize(item) {
      let size = 9
      if (item.noStatusUpdate === true) {
        size = 12
      }

      return size
    },
    generateCalculation(plantId, userId, row) {
      //  true/prognoserechnung
      store.dispatch('app-power-plant/generateCalculation', { plantId, userId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
          store.commit('app-power-plant/updateCalculationAndLetterChangedAt')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateCalculation')
            /* eslint-enable no-console */
          }
        })
    },
    //  :href="`${apiUrl}/file-generator/project-participation/${$router.currentRoute.params.id}/${User.id}`"
    generateLetter(plantId, userId, row) {
      store.dispatch('app-power-plant/generateLetter', { plantId, userId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
          store.commit('app-power-plant/updateCalculationAndLetterChangedAt')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateProjectParticipation')
            /* eslint-enable no-console */
          }
        })
    },

    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
  },
  setup(props) {
    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)
    const toast = useToast()
    const canFinishSegment = ref(false)

    const userDocumentsFields = [
      { key: 'show_details', label: '', thStyle: { width: '100px !important' } },
      {
        key: 'icon',
        label: '',
        formatter: (value, key, item) => item.type,
        thStyle: { width: '40px !important' },
      },
      { key: 'rs', label: '', thStyle: { width: '80px !important' } },
      { key: 'type', label: 'Dokument' },
      { key: 't0', label: 'ERSTELLT AM', thStyle: { width: '200px !important' } },
    ]

    const userDocumentFields = [
      {
        key: 'icon',
        label: '',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '120px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'ERSTELLT AM', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '150px !important' } },
    ]

    const updateDocumentStatus = (id, status) => {
      // FilePond instance methods are available on `this.$refs.pond`
      store.dispatch('app-power-plant/updateDocumentStatus', { id, status })
        .then(() => {
          /* eslint-disable no-param-reassign */
          props.fileContainersData.find(x => x.id === id).rs = status
          /* eslint-ebable no-param-reassign */
          toast({
            component: ToastificationContent,
            props: {
              title: 'Document Status Updated',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateDocumentStatus error response')
            /* eslint-enable no-console */
          }
        })
    }

    const loadDocumentFiles = (row, toggleDetails) => {
      if (toggleDetails === true) {
        row.toggleDetails()
      } else {
        // from method
        row.detailsShowing = true
      }

      if (row.detailsShowing === true) {
        store.dispatch('app-power-plant/getDocumentFiles', { id: row.item.id })
          .then(response => {
            console.log(response)
            /* eslint-disable no-param-reassign */
            props.fileContainersData.find(x => x.id === row.item.id).files = response.data.payload
            /* eslint-ebable no-param-reassign */
          })
          .catch(error => {
            if (error.status === 404) {
              /* eslint-disable no-console */
              console.log('loadfiles error response')
              /* eslint-enable no-console */
            }
          })
      }
    }

    const saveComment = row => {
      store.dispatch('app-power-plant/saveComment', { id: row.id, comment: row.comment })
        .then(res => {
          if (res.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Kommentar gespeichert.',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Kommentar nicht gespeichert.',
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

    const checkUploadedDocuments = () => {
      store.dispatch('app-power-plant/checkUploadedDocuments', { id: router.currentRoute.params.id, type: 'calculation_letter' })
        .then(res => {
          canFinishSegment.value = false
          if (res.data.status === 200) {
            canFinishSegment.value = true
          }
        })
    }

    checkUploadedDocuments()

    const calculationAndLetterChangedAt = computed(() => store.getters['app-power-plant/calculationAndLetterChangedAt'])
    watch(calculationAndLetterChangedAt, (val, oldVal) => {
      if (val > oldVal) {
        checkUploadedDocuments()
      }
    })

    return {
      loadDocumentFiles,
      updateDocumentStatus,
      userDocumentsFields,
      userDocumentFields,
      FilePond,
      saveComment,
      canFinishSegment,
      checkUploadedDocuments,
    }
  },
}
</script>
