<template>
  <div>
    <div class="d-flex">
      <feather-icon
        icon="FileIcon"
        size="19"
      />
      <h4 class="mb-0 ml-50">
        Dokumente Solaranlage
      </h4>
      <br><br>
    </div>
    <b-row>
      <b-col
        cols="12"
        md="2"
        xs="12"
        class="text-left"
      >
        <b-form-checkbox
          v-model=baseData.solarPlantFilesVerifiedByBackendUser
          name="check-button"
          switch
          v-on:change="updatePlantStatus('filesVerified')"
        >
          Dokumente geprüft
        </b-form-checkbox>
      </b-col>
      <b-col
        cols="12"
        md="2"
        xs="12"
        class="text-left"
      >
        <b-form-checkbox
          v-model=baseData.contractFinalized
          name="check-button"
          switch
          v-on:change="updatePlantStatus('contractFinalized')"
        >
          Verträge abgeschlossen
        </b-form-checkbox>
      </b-col>
      <b-col
        cols="12"
        md="2"
        xs="12"
        class="text-left"
      >
        <b-form-checkbox
          v-model=baseData.plantInstalled
          name="check-button"
          switch
          v-on:change="updatePlantStatus('plantInstalled')"
        >
          Anlage installiert
        </b-form-checkbox>
      </b-col>
      <b-col
        cols="12"
        md="2"
        xs="12"
        class="text-left"
      >
        <b-form-checkbox
          v-model=baseData.plantInUse
          name="check-button"
          switch
          v-on:change="updatePlantStatus('plantInUse')"
        >
          Anlage im Betrieb
        </b-form-checkbox>
      </b-col>
    </b-row>
    <br>
    <b-row>
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
            <div>
              <b-row class="mb-2">
                <b-col
                  md="12"
                  class="mb-1"
                >
                  <div v-if="row.item.type === 21">
                    Information: In der Prognoserechnung sehen Sie abhängig vom Eigenverbrauch und Überschussstrom die voraussichtlichen Kosten der PV-Anlage unter Berücksichtigung der aktuellen Fördermöglichkeiten.
                    <br><br>
                    <b-button
                      v-if="isDisabled() == false"
                      variant="outline-primary"
                      class="mr-0 mb-sm-0 mr-0 mr-sm-1"
                      :block="$store.getters['app/currentBreakPoint'] === 'xs'"
                      type="button"
                      @click="generateCalculation($router.currentRoute.params.id, User.id, row)"
                    >
                      <feather-icon
                        icon="EyeIcon"
                        class="mr-50"
                      />
                      Prognoserechnung generieren
                    </b-button>
                  </div>
                  <div v-if="row.item.type === 22">
                    Information: Projektierung, backend upload only.
                  </div>
                  <div v-if="row.item.type === 23">
                    Information: Download file.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/project-participation/${$router.currentRoute.params.id}/${User.id}`"
                    >
                      Download
                    </a>
                  </div>
                  <div v-if="row.item.type === 24">
                    Information: Download file.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/contract-energy-saving/${$router.currentRoute.params.id}/${User.id}`"
                    >
                      Download
                    </a>
                  </div>
                  <div v-if="row.item.type === 25">
                    Information: Download file.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/contract-billing-sheet/${$router.currentRoute.params.id}/${User.id}`"
                    >
                      Download
                    </a>
                  </div>
                  <div v-if="row.item.type === 27">
                    Information: Instructions text. Download file and upload it back to the system.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/mandate-completion/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                  </div>

                  <div v-if="row.item.type === 26">
                    Some gallery info text
                  </div>
                  <div v-if="row.item.type === 28">
                    Some backend files info text
                  </div>
                  <div v-if="row.item.type === 29">
                    Information: Laden Sie das Dokument herunter, unterschreiben es (am Ausdruck oder digital) und laden es wieder hoch.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/mandate-billing/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                  </div>
                  <div v-if="row.item.type === 291">
                    Information: Laden Sie das Dokument herunter, unterschreiben es (am Ausdruck oder digital) und laden es wieder hoch.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/mandate-billing-net/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                  </div>
                </b-col>
              </b-row>
              <b-row
                class="mb-2"
              >
                <b-col
                  md="12"
                  :lg="getSize(row.item)"
                  class="mb-1"
                >
                  Uploaded documents:
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
                          v-auth-href
                          :href="`${apiUrl}/user/file/${row.item.id}`"
                        >
                          <feather-icon
                            class="mr-1"
                            icon="ArrowDownCircleIcon"
                          />
                        </a>
                        <a
                          v-if="isDisabled() == false"
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
                  <br>
                  <b-button
                    v-b-modal.modal-uploadFileNew
                    size="sm"
                    variant="outline-secondary"
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
                    Neue Dokumente hochladen
                  </b-button>
                </b-col>
                <b-col
                  v-if="row.item.noStatusUpdate === false"
                  md="12"
                  lg="3"
                  class="mb-1"
                >
                  Status:
                  <b-list-group>
                    <b-list-group-item
                      :active="row.item.rs==1"
                      button
                      @click="updateDocumentStatus(row.item.id, 1)"
                    >
                      Neu
                    </b-list-group-item>
                    <b-list-group-item
                      :active="row.item.rs==2"
                      button
                      @click="updateDocumentStatus(row.item.id, 2)"
                    >
                      Hochgeladen
                    </b-list-group-item>
                    <b-list-group-item
                      :active="row.item.rs==3"
                      button
                      @click="updateDocumentStatus(row.item.id, 3)"
                    >
                      Geändert
                    </b-list-group-item>
                    <b-list-group-item
                      button
                      :active="row.item.rs==4"
                      @click="updateDocumentStatus(row.item.id, 4)"
                    >
                      Überprüft
                    </b-list-group-item>
                  </b-list-group>
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
          </template>
          <template #cell(icon)="data">
            <div v-if="data.value === 21">
              <feather-icon
                icon="DollarSignIcon"
              />
            </div>
            <div v-if="data.value === 22">
              <feather-icon
                icon="FilePlusIcon"
              />
            </div>
            <div v-if="data.value === 23">
              <feather-icon
                icon="EditIcon"
              />
            </div>
            <div v-if="data.value === 24">
              <feather-icon
                icon="BatteryChargingIcon"
              />
            </div>
            <div v-if="data.value === 25">
              <feather-icon
                icon="TargetIcon"
              />
            </div>
            <div v-if="data.value === 26">
              <feather-icon
                icon="ImageIcon"
              />
            </div>
            <div v-if="data.value === 27">
              <feather-icon
                icon="EyeIcon"
              />
            </div>
            <div v-if="data.value === 29">
              <feather-icon
                icon="EyeIcon"
              />
            </div>
            <div v-if="data.value === 291">
              <feather-icon
                icon="EyeIcon"
              />
            </div>
            <div v-if="data.value === 28">
              <feather-icon
                icon="EyeOffIcon"
              />
            </div>
          </template>
          <template #cell(type)="data">
            <div
              style="font-size:18px; font-weight:bold;"
            >
              <div v-if="data.value === 21">
                Prognoserechnung
              </div>
              <div v-if="data.value === 22">
                Projektierung
              </div>
              <div v-if="data.value === 23">
                Anschreiben
              </div>
              <div v-if="data.value === 24">
                Vertrag Energieeinsparung
              </div>
              <div v-if="data.value === 25">
                Vertrag Verrechnungsblatt
              </div>
              <div v-if="data.value === 26">
                Bildergalerie
              </div>
              <div v-if="data.value === 27">
                Vollmacht Abwicklung
              </div>
              <div v-if="data.value === 28">
                Diverse Dokumente
              </div>
              <div v-if="data.value === 29">
                Vollmacht Energieabrechnung
              </div>
              <div v-if="data.value === 291">
                Vollmacht Netzbetreiber
              </div>
            </div>
          </template>
          <template v-slot:cell(t0)="data">
            <div class="text-right">{{ data.value | moment("DD.MM. YYYY") }}</div>
          </template>
        </b-table>
      </b-col>
    </b-row>
    <b-modal
      id="modal-uploadFileNew"
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
        accepted-file-types="image/jpeg, application/pdf"
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
        :file-rename-function=fileRenameFunction
        v-on:processfile="handleProcessFile"
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
  BListGroup,
  BListGroupItem,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'
import { $apiUrl } from '@serverConfig'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import vueFilePond from 'vue-filepond'
import { v4 as uuidv4 } from 'uuid'

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
    BListGroup,
    BListGroupItem,
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
      file1: [],
      selected: '',
      fileContainerSelectedId: '',
      option: ['Vollmacht', 'Grundbuchauszug', 'Verträg', 'Angebot', 'Other'],
      myFiles: [],
      apiUrl: $apiUrl,
      accessToken: localStorage.getItem('accessToken'),
    }
  },
  methods: {
    fileRenameFunction(file) {
      let fileName = `${this.$props.User.firstName.toLowerCase()}_${this.$props.User.lastName.toLowerCase()}_`
      const documentType = this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).type

      //  leave here for now, update do store global data
      if (documentType === 21) {
        fileName = `${fileName}prognoserechnung`
      } else if (documentType === 22) {
        fileName = `${fileName}projektierung`
      } else if (documentType === 23) {
        fileName = `${fileName}anschreiben`
      } else if (documentType === 24) {
        fileName = `${fileName}vertrag-energieeinsparung`
      } else if (documentType === 25) {
        fileName = `${fileName}vertrag-verrechnungsblatt`
      } else if (documentType === 26) {
        fileName = `${fileName}bildergalerie`
      } else if (documentType === 27) {
        fileName = `${fileName}vollmacht-abwicklung`
      } else if (documentType === 28) {
        fileName = `${fileName}backend-files`
      } else if (documentType === 29) {
        fileName = `${fileName}vollmacht-energieabrechnung`
      } else if (documentType === 291) {
        fileName = `${fileName}vollmacht-netzbetreiber`
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

                store.dispatch('app-power-plant/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    /* eslint-ebable no-param-reassign */
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
    hadleFileUploadClose() {
      store.dispatch('app-power-plant/getDocumentFiles', { id: this.fileContainerSelectedId })
        .then(response => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).files = response.data.payload
          /* eslint-ebable no-param-reassign */
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
          /* eslint-disable no-console */
          console.log('updatePlantStatus updated')
          /* eslint-enable no-console */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updatePlantStatus error response')
            /* eslint-enable no-console */
          }
        })
    },

    getSize(item) {
      let size = 9
      if (item.noStatusUpdate === true) {
        size = 12
      }

      return size
    },
    generateCalculation(plantId, userId, row) {
      console.log('generate')
      console.log(plantId)
      console.log(userId)
      console.log(row)
      //  true/prognoserechnung
      store.dispatch('app-power-plant/generateCalculation', { plantId, userId })
        .then(() => {
          /* eslint-disable no-console */
          console.log('updateFileStatus updated')
          /* eslint-enable no-console */

          console.log(this.$props.fileContainersData)
          console.log(row.item.id)

          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)

          console.log('updateFileStatus updated end')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateCalculation')
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
        thStyle: { width: '40px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'ERSTELLT AM', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
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
              icon: 'AlertTriangleIcon',
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
      console.log('load document files')
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

    return {
      loadDocumentFiles,
      updateDocumentStatus,
      userDocumentsFields,
      userDocumentFields,
      FilePond,
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
}
</script>
