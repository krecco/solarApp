<template>
  <div>
    <b-row>
      <b-col
        cols="12"
        md="12"
        xs="12"
      >
        <h4 class="mb-0 ml-50">
          <feather-icon
            icon="FileIcon"
            size="19"
            inline
          />
          Dokumente hochladen
        </h4>
      </b-col>
    </b-row>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <b-table
          ref="fileUserTable"
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
          <!--  @change="row.toggleDetails; loadDocumentFiles(row)"  :ref="`fileUser_${row.index}`" -->
          <template #cell(show_details)="row">
            <b-form-checkbox
              :id="`idFileUser_${row.item.id}`"
              v-model="row.detailsShowing"
              @change="loadDocumentFiles(row)"
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
                  <div v-if="row.item.type === 11">
                    Information: Laden Sie bitte einen gültigen Personalausweis (Vorder- u. Rückseite) oder Reisepass hoch.
                  </div>
                  <div v-if="row.item.type === 12">
                    Information: Laden Sie das Dokument herunter, unterschreiben es (am Ausdruck oder digital) und laden es wieder hoch.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/mandate-completion/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                  </div>
                  <div v-if="row.item.type === 13">
                    Information: Laden Sie das Dokument herunter, unterschreiben es (am Ausdruck oder digital) und laden es wieder hoch.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/mandate-billing/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                    <!--
                    <a
                      :href="`${apiUrl}/file-generator/mandate-billing/${$router.currentRoute.params.id}`"
                    >
                      Download novauth
                    </a>
                    -->
                  </div>
                  <div v-if="row.item.type === 14">
                    Information: Laden Sie das SEPA-Dokument herunter, unterschreiben es (am Ausdruck oder digital) und laden es wieder hoch.
                    <br>
                    Click Here:
                    <a
                      v-auth-href
                      :href="`${apiUrl}/file-generator/sepa/${$router.currentRoute.params.id}`"
                    >
                      Download
                    </a>
                  </div>
                </b-col>
              </b-row>
              <b-row
                v-if="row.item.downloadOnly === false"
                class="mb-2"
              >
                <b-col
                  md="12"
                  lg="12"
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
                    Neue Dokumente hochladen
                  </b-button>
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
            <div v-if="data.value === 11">
              <feather-icon
                icon="UserCheckIcon"
              />
            </div>
            <div v-if="data.value === 12">
              <feather-icon
                icon="EyeIcon"
              />
            </div>
            <div v-if="data.value === 13">
              <feather-icon
                icon="EyeIcon"
              />
            </div>
            <div v-if="data.value === 14">
              <feather-icon
                icon="CreditCardIcon"
              />
            </div>
          </template>
          <template #cell(type)="data">
            <div
              style="font-size:18px; font-weight:bold;"
            >
              <div v-if="data.value === 11">
                Personalausweis / Reisepass
              </div>
              <div v-if="data.value === 12">
                Vollmacht Abwicklung
              </div>
              <div v-if="data.value === 13">
                Vollmacht Energieabrechnung
              </div>
              <div v-if="data.value === 14">
                SEPA Lastschriftmandat
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
      <!-- :server="`${apiUrl}/user/upload-multipart-file/${fileContainerSelectedId}`" -->
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
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import router from '@/router'
import store from '@/store'
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
      console.log(file)
      console.log(this.$props)
      let fileName = `${this.$props.userData.firstName.toLowerCase()}_${this.$props.userData.lastName.toLowerCase()}_`
      const documentType = this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).type

      //  leave here for now, update do store global data
      if (documentType === 11) {
        fileName = `${fileName}personalausweis`
      } else if (documentType === 12) {
        fileName = `${fileName}vollmacht-abwicklung`
      } else if (documentType === 13) {
        fileName = `${fileName}vollmacht-energieabrechnung`
      } else if (documentType === 14) {
        fileName = `${fileName}SEPA-lastschriftmandat`
      }

      fileName = `${fileName}_${uuidv4()}`

      return `${fileName}${file.extension}`
    },
    selectedFileContainer(id) {
      this.fileContainerSelectedId = id
      store.dispatch('app-user/fetchUserFileContainers', { id: router.currentRoute.params.id })
    },
    deleteFile(id, containerId) {
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
            store.dispatch('app-user/deleteFile', { id })
              .then(response => {
                /* eslint-disable no-param-reassign */
                this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */

                store.dispatch('app-user/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    /* eslint-ebable no-param-reassign */
                  })
                  .catch(error => {
                    if (error.status === 404) {
                      /* eslint-disable no-console */
                      console.log('deleteFile error response')
                      /* eslint-enable no-console */
                    }
                  })
              })
              .catch(error => {
                if (error.status === 404) {
                  /* eslint-disable no-console */
                  console.log('deleteFile error response')
                  /* eslint-enable no-console */
                }
              })
          }
        })
    },
    hadleFileUploadClose() {
      store.dispatch('app-user/getDocumentFiles', { id: this.fileContainerSelectedId })
        .then(response => {
          /* eslint-disable no-param-reassign */
          this.$props.fileContainersData.find(x => x.id === this.fileContainerSelectedId).files = response.data.payload
          /* eslint-ebable no-param-reassign */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('getDocumentFiles error response')
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

    const userData = ref({})
    const authUser = JSON.parse(localStorage.getItem('userData'))

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
      { key: 't0', label: 'Erstellt am', thStyle: { width: '200px !important' } },
    ]

    const userDocumentFields = [
      {
        key: 'icon',
        label: '',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '40px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
    ]

    const updateDocumentStatus = (id, status) => {
      // FilePond instance methods are available on `this.$refs.pond`
      store.dispatch('app-user/updateDocumentStatus', { id, status })
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
            console.log('updateDocument error response')
            /* eslint-enable no-console */
          }
        })
    }

    store.dispatch('app-user/fetchUser', { id: authUser.uid })
      .then(response => {
        userData.value = response.data.payload
        props.userData = response.data.payload
      })
      .catch(error => {
        if (error.response.status === 404) {
          userData.value = undefined
        }
      })

    const loadDocumentFiles = row => {
      row.toggleDetails()

      if (row.detailsShowing === true) {
        store.dispatch('app-user/getDocumentFiles', { id: row.item.id })
          .then(response => {
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
      authUser,
      userData,
    }
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        if (typeof (router.currentRoute.params.fileContainerId) !== 'undefined') {
          document.getElementById(`idFileUser_${router.currentRoute.params.fileContainerId}`).click()
        }
      }, 250)
    })
  },
}
</script>
