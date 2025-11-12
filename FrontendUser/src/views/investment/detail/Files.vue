<template>
  <div>
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
        md="2"
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
              <span class="vs-label">{{ row.detailsShowing ? 'Hide' : 'Details' }}</span>
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
                  <div v-if="row.item.type === 31">
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
                  <div v-if="row.item.type === 32">
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
                <div v-if="row.item.type === 33">
                    Some backend files info text
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
                    striped
                    responsive
                    :hover="true"
                    :items="row.item.files"
                    :fields="userDocumentFields"
                    :ref="row.item.id"
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
                    @click="selectedFileContainer(row.item.id)"
                  >
                    <feather-icon
                      icon="PlusCircleIcon"
                      size="15"
                    />
                    Upload new documents
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
                      New
                    </b-list-group-item>
                    <b-list-group-item
                      :active="row.item.rs==2"
                      button
                      @click="updateDocumentStatus(row.item.id, 2)"
                    >
                      Uploaded
                    </b-list-group-item>
                    <b-list-group-item
                      :active="row.item.rs==3"
                      button
                      @click="updateDocumentStatus(row.item.id, 3)"
                    >
                      Modified
                    </b-list-group-item>
                    <b-list-group-item
                      button
                      :active="row.item.rs==4"
                      @click="updateDocumentStatus(row.item.id, 4)"
                    >
                      Verified
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
              No File
            </b-badge>
            <b-badge
              v-if="data.value === 2"
              pill
              variant="info"
            >
              Uploaded
            </b-badge>
            <b-badge
              v-if="data.value === 3"
              pill
              variant="danger"
            >
              Modified
            </b-badge>
            <b-badge
              v-if="data.value === 4"
              pill
              variant="success"
            >
              Verified
            </b-badge>
          </template>
          <template #cell(icon)="data">
            <div v-if="data.value === 31">
               <feather-icon
                icon="FilePlusIcon"
              />
            </div>
            <div v-if="data.value === 32">
               <feather-icon
                icon="EditIcon"
              />
            </div>
            <div v-if="data.value === 33">
                <feather-icon
                icon="EyeOffIcon"
              />
            </div>
          </template>
          <template #cell(type)="data"
          >
            <div
              style="font-size:18px; font-weight:bold;"
            >
              <div v-if="data.value === 31">
                Document Placeholder 1
              </div>
              <div v-if="data.value === 32">
                Document Placeholder 2
              </div>
              <div v-if="data.value === 33">
                Diverse Dokumente
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
      title="Upload File"
      ok-title="Close"
      ok-only
      no-close-on-backdrop
      no-close-on-esc
      size="lg"
      @ok="hadleFileUploadClose"
    >
      <file-pond
        ref="pond"
        name="fileUpload"
        label-idle="Drop files here..."
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
        :file-rename-function="fileRenameFunction"
        @:processfile="handleProcessFile"
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
      console.log('rename')

      let fileName = `${this.$props.User.firstName.toLowerCase()}_${this.$props.User.lastName.toLowerCase()}_`
      const documentType = this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).type

      //  leave here for now, update do store global data
      if (documentType === 31) {
        fileName = `${fileName}doc1`
      } else if (documentType === 32) {
        fileName = `${fileName}doc2`
      } else if (documentType === 33) {
        fileName = `${fileName}doc3`
      }

      fileName = `${fileName}_${uuidv4()}`

      return `${fileName}${file.extension}`
    },

    selectedFileContainer(id) {
      console.log(id)
      this.fileContainerSelectedId = id
      //  store.dispatch('app-investment/fetchUserFileContainers', { id: router.currentRoute.params.id })
    },
    deleteFile(id, containerId) {
      store.dispatch('app-investment/deleteFile', { id })
        .then(response => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
          /* eslint-ebable no-param-reassign */

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
    },
    hadleFileUploadClose() {
      store.dispatch('app-investment/getDocumentFiles', { id: this.fileContainerSelectedId })
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

    getSize(item) {
      let size = 9
      if (item.noStatusUpdate === true) {
        size = 12
      }

      return size
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
      { key: 'type' },
      { key: 't0', label: 'Date Added', thStyle: { width: '200px !important' } },
    ]

    const userDocumentFields = [
      {
        key: 'icon',
        label: '',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '40px !important' },
      },
      { key: 'fileName', label: 'File' },
      { key: 't0', label: 'Date Added', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
    ]

    const updateDocumentStatus = (id, status) => {
      console.log('insetuip')
      // FilePond instance methods are available on `this.$refs.pond`
      store.dispatch('app-investment/updateDocumentStatus', { id, status })
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
