<template>
  <div>
    <b-overlay
      :show="showOverlay"
      position-static
    >
      <b-row>
        <b-col
          cols="12"
          md="12"
        >
           <div class="d-flex">
              <feather-icon
                icon="FileIcon"
                size="19"
              />
              <h4 class="mb-0 ml-50">
                Dokumente Investition
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
                  v-model=baseData.investmentFilesVerifiedByBackendUser
                  name="check-button"
                  switch
                  v-on:change="updateFileStatus"
                >
                  Dokumente geprüft
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="12"
                md="3"
                xs="12"
                class="text-left"
              >
                <b-form-checkbox
                  v-model=baseData.contractFinalized
                  name="check-button"
                  switch
                  v-on:change="updateInvestmentStatus('contractFinalized')"
                >
                  Vertrag unterschreiben
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="12"
                md="2"
                xs="12"
                class="text-left"
              >
                <b-form-checkbox
                  v-model=baseData.contractPaid
                  name="check-button"
                  switch
                  v-on:change="updateInvestmentStatus('contractPaid')"
                >
                  Vertrag bezahlt
                </b-form-checkbox>
              </b-col>
            </b-row>
            <br>

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
              <div>
                <b-row
                  class="mb-2"
                >
                  <b-col
                    md="12"
                    :lg="getSize(row.item)"
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
                          v-b-modal.modal-previewPDFContract
                          nohref
                          :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                          @click="loadPreview(data.item.id)"
                        >
                          {{ data.item.fileName }}
                        </a>
                        <a
                          v-if="data.item.fileContentType !== 'application/pdf'"
                          v-b-modal.modal-previewImageContract
                          nohref
                          :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                          @click="loadPreview(data.item.id)"
                        >
                          {{ data.item.fileName }}
                        </a>
                      </template>
                      <template
                        #cell(icon)="data"
                      >
                        <span
                          :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                            v-if="data.item.backendUserUpload === true"
                            class="mr-1"
                            icon="UploadIcon"
                          />

                          <feather-icon
                            v-if="data.item.backendUserUpload === false"
                            class="mr-1"
                            icon="UploadIcon"
                          />
                          <feather-icon
                            v-if="data.item.generated === true"
                            class="mr-1"
                            icon="CpuIcon"
                          />
                        </span>
                      </template>
                      <template v-slot:cell(t0)="data">
                        <div
                          class="text-left"
                          :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                        >
                          {{ data.value | moment("DD.MM. YYYY") }}
                        </div>
                      </template>
                      <!-- Column: Actions -->
                      <template #cell(actions)="row">
                        <div
                          class="text-right"
                        >

                          <a
                            v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                            v-b-modal.modal-previewPDFContract
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
                            v-b-modal.modal-previewImageContract
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
                      <!--
                      <b-list-group-item
                        :active="row.item.rs==1"
                        button
                        @click="updateDocumentStatus(row.item.id, 1)"
                      >
                        Neu
                      </b-list-group-item>
                      -->
                      <b-list-group-item
                        :active="row.item.rs==2"
                        button
                        @click="updateDocumentStatus(row.item.id, 2)"
                      >
                        erstellt
                      </b-list-group-item>
                      <!--
                      <b-list-group-item
                        :active="row.item.rs==3"
                        button
                        @click="updateDocumentStatus(row.item.id, 3)"
                      >
                        Geändert
                      </b-list-group-item>
                      -->
                      <b-list-group-item
                        button
                        :active="row.item.rs==4"
                        @click="updateDocumentStatus(row.item.id, 4)"
                      >
                        überprüft
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
              <!--
              <b-badge
                v-if="data.value === 1"
                pill
                variant="warning"
              >
                Neu
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
                variant="warning"
              >
                geändert
              </b-badge>
              <b-badge
                v-if="data.value === 4"
                pill
                variant="success"
              >
                überprüft
              </b-badge>
            </template>
            <template #cell(icon)="data">
              <div v-if="data.value === 41">
                <feather-icon
                  icon="DollarSignIcon"
                />
              </div>
              <div v-if="data.value === 42">
                <feather-icon
                  icon="FilePlusIcon"
                />
              </div>
              <div v-if="data.value === 43">
                <feather-icon
                  icon="EditIcon"
                />
              </div>
            </template>
            <template #cell(type)="data">
              <div
                style="font-size:18px; font-weight:bold;"
              >
                <div v-if="data.value === 41">
                  Dokument 1
                </div>
                <div v-if="data.value === 42">
                  Dokument 2
                </div>
                <div v-if="data.value === 43">
                  Dokument 3
                </div>
              </div>
            </template>
            <template v-slot:cell(t0)="data">
              <div class="text-right">{{ data.value | moment("DD.MM. YYYY") }}</div>
            </template>
          </b-table>
        </b-col>
      </b-row>
    </b-overlay>
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
    <b-modal
      id="modal-previewImageContract"
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
      id="modal-previewPDFContract"
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
  BListGroup,
  BListGroupItem,
  BSpinner,
  BImg,
  BOverlay,
} from 'bootstrap-vue'

import { localize } from 'vee-validate'
import { ref } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
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

localize('de')
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
    BSpinner,
    BImg,
    BOverlay,
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
      showLoading: false,
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
    loadPreview(id) {
      this.previewFile = ''
      this.loadingPreviewFile = true
      store.dispatch('app-investment/getFileBase', { id })
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

      //  leave here for now, update do store global data
      if (documentType === 41) {
        fileName = `${fileName}dokument-1`
      } else if (documentType === 42) {
        fileName = `${fileName}dokument-2`
      } else if (documentType === 43) {
        fileName = `${fileName}dokument-3`
      }

      fileName = `${fileName}_${uuidv4()}`

      return `${fileName}${file.extension}`
    },

    selectedFileContainer(id) {
      this.fileContainerSelectedId = id
      //  store.dispatch('app-investment/fetchUserFileContainers', { id: router.currentRoute.params.id })
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
            store.dispatch('app-investment/deleteFile', { id })
              .then(response => {
                /* eslint-disable no-param-reassign */
                this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */

                store.commit('app-investment/updateContractsChangedAt')
                store.dispatch('app-investment/getDocumentFiles', { id: containerId })
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
      store.dispatch('app-investment/getDocumentFiles', { id: this.fileContainerSelectedId })
        .then(response => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).files = response.data.payload
          /* eslint-ebable no-param-reassign */
          store.commit('app-investment/updateContractsChangedAt')
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
      console.log('here a')
      store.dispatch('app-investment/updateFileStatus', { plantId: this.$props.baseData.id, status: this.$props.baseData.investmentFilesVerifiedByBackendUser })
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

      store.dispatch('app-investment/updatePlantStatus', { plantId: this.$props.baseData.id, type, status })
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
      //  true/prognoserechnung
      store.dispatch('app-investment/generateCalculation', { plantId, userId })
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
      return false
    },

    generateEnergySavingContract(plantId, userId, row) {
      store.dispatch('app-investment/generateEnergySavingContract', { plantId, userId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateProjectParticipation')
            /* eslint-enable no-console */
          }
        })
    },

    generateContractBillingSheet(plantId, userId, row) {
      store.dispatch('app-investment/generateContractBillingSheet', { plantId, userId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateContractBillingSheet')
            /* eslint-enable no-console */
          }
        })
    },

    generateMandateCompletion(plantId, row) {
      store.dispatch('app-investment/generateMandateCompletion', { plantId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateMandateCompletion')
            /* eslint-enable no-console */
          }
        })
    },

    generateMandateBilling(plantId, row) {
      store.dispatch('app-investment/generateMandateBilling', { plantId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateMandateBilling')
            /* eslint-enable no-console */
          }
        })
    },

    generateMandateBillingNet(plantId, row) {
      store.dispatch('app-investment/generateMandateBillingNet', { plantId })
        .then(() => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === row.item.id).rs = 2
          /* eslint-enable no-console */

          this.loadDocumentFiles(row, false)
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('generateMandateBillingNet')
            /* eslint-enable no-console */
          }
        })
    },

    /*
    validateContractsSentToCustomerBackend() {
      this.$refs.validateContractsSentToCustomerBackend.validate().then(success => {
        console.log(success)
        if (success) {
          this.save()
        }
      })
    },
    */

    updateContractSentStatus() {
      this.$refs.validateContractsSentToCustomerBackend.validate().then(success => {
        if (success) {
          this.$bvModal
            .msgBoxConfirm('Verträge und Vollmachten werden an Kunden geschickt', {
              title: 'Mailversand - Vertragsunterlagen',
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
                store.dispatch('app-investment/updateWorkflowStatusEventNotify', { plantId: this.$props.baseData.id, status: 'contractsSentToCustomer', email: this.$props.baseData.contractsSentToCustomerBackendUserSendTo })
                  .then(() => {
                    store.commit('app-investment/updateSolarPlantUpdatedAt')
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
        }
      })
    },

    updateContractFilesChecked() {
      this.$bvModal
        .msgBoxConfirm('Verträge und Vollmachten sind überprüft', {
          title: 'Mailversand - Vertragsunterlagen überprüft',
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
            store.dispatch('app-investment/updateWorkflowStatus', { plantId: this.$props.baseData.id, status: 'contractFilesChecked' })
              .then(() => {
                store.commit('app-investment/updateSolarPlantUpdatedAt')
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

    updateContractsReviewedStatus() {
      console.log('aaa')
      store.dispatch('app-investment/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'contractsReviewed', value: this.$props.baseData.contractsReviewed })
        .then(() => {
          store.commit('app-investment/updateSolarPlantUpdatedAt')
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

    updateContractsSignedStatus() {
      store.dispatch('app-investment/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'contractsSigned', value: this.$props.baseData.contractsSigned })
        .then(() => {
          store.commit('app-investment/updateSolarPlantUpdatedAt')
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

    propertyOwnerListFinished() {
      store.dispatch('app-investment/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'propertyOwnerListFinished', value: this.$props.baseData.propertyOwnerListFinished })
        .then(() => {
          store.commit('app-investment/updateSolarPlantUpdatedAt')
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

    updateInvestmentStatus(type) {
      console.log('here b')
      let status = false
      if (type === 'contractPaid') {
        status = this.$props.baseData.contractPaid
      } else if (type === 'contractFinalized') {
        status = this.$props.baseData.contractFinalized
      }

      store.dispatch('app-investment/updateInvestmentStatus', { plantId: this.$props.baseData.id, type, status })
        .then(() => {
          /* eslint-disable no-console */
          console.log('updateInvestmentStatus updated')
          /* eslint-enable no-console */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateInvestmentStatus error response')
            /* eslint-enable no-console */
          }
        })
    },
  },
  setup(props) {
    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)
    const toast = useToast()
    const canFinishSegment = ref(false)
    const showOverlay = ref(false)

    const propertyOwnerList = ref([])

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
      store.dispatch('app-investment/updateDocumentStatus', { id, status })
        .then(() => {
          /* eslint-disable no-param-reassign */
          props.fileContainersData.find(x => x.id === id).rs = status
          /* eslint-ebable no-param-reassign */
          toast({
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
        store.dispatch('app-investment/getDocumentFiles', { id: row.item.id })
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

    //  temp placeholder
    const generateContractFiles = () => {
      store.dispatch('app-investment/generateEnergySavingContract', { plantId: props.baseData.id, userId: props.User.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateEnergySavingContract generated')
        })

      store.dispatch('app-investment/generateContractBillingSheet', { plantId: props.baseData.id, userId: props.User.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateContractBillingSheet generated')
        })

      store.dispatch('app-investment/generateMandateCompletion', { plantId: props.baseData.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateCompletion generated')
        })

      store.dispatch('app-investment/generateMandateBilling', { plantId: props.baseData.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateBilling generated')
        })

      store.dispatch('app-investment/generateMandateBillingNet', { plantId: props.baseData.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateMandateBillingNet generated')
        })

      store.dispatch('app-investment/generateSepa', { plantId: props.baseData.id })
        .then(() => {
          //  PlantCalculationAndLetterContainers.value[1].rs = 2
          console.log('generateSepa generated')
        })

      showOverlay.value = true
      setTimeout(() => {
        //  fetchContainers()
        store.commit('app-investment/updateContractsGeneratedAt')
        store.commit('app-investment/updateContractsChangedAt')
        showOverlay.value = false
      }, 4000)
    }

    const updateOrderInterest = () => {
      store.dispatch('app-investment/updateWorkflowStatus', { plantId: props.baseData.id, status: 'orderInterest' })
        .then(() => {
          generateContractFiles()
          store.commit('app-investment/updateSolarPlantUpdatedAt')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('updateFileStatus error response')
            /* eslint-enable no-console */
          }
        })
    }

    return {
      loadDocumentFiles,
      updateDocumentStatus,
      userDocumentsFields,
      userDocumentFields,
      FilePond,
      propertyOwnerList,
      updateOrderInterest,
      canFinishSegment,
      showOverlay,
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
