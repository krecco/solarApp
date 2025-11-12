<template>
  <div>
    <b-row>
      <b-col
        cols="12"
        style="padding-bottom: 20px;"
      >
        <b-form-checkbox
          id="powerBillUploadedStatus"
          v-model="baseData.powerBillUploaded"
          style="float:right;"
          switch
          inline
          :disabled="isDisabled()"
          @change="updateStatus"
        >
          Uploads geprüft
        </b-form-checkbox>
      </b-col>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          <b-table
            ref="powerbilltable"
            striped
            responsive
            :hover="true"
            :items="containerFiles"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(fileName)="data"
            >
              <a
                v-if="data.item.fileContentType === 'application/pdf'"
                v-b-modal.modal-previewPDFPower
                nohref
                @click="loadPreview(data.item.id)"
              >
                {{ data.item.fileName }}
              </a>
              <a
                v-if="data.item.fileContentType !== 'application/pdf'"
                v-b-modal.modal-previewImagePower
                nohref
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
                v-if="data.item.backendUserUpload === true"
                class="mr-1"
                icon="UploadIcon"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
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
                  v-b-modal.modal-previewPDFPower
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
                  v-b-modal.modal-previewImagePower
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
                  v-if="isDisabled() !== true"
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
            v-b-modal.modal-uploadSingle
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
          >
            <feather-icon
              icon="PlusCircleIcon"
              size="15"
            />
            Neue Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <b-modal
      id="modal-uploadSingle"
      title="Datei hochladen"
      ok-title="Schließen"
      ok-only
      no-close-on-backdrop
      no-close-on-esc
      size="lg"
      @ok="hadleFileUploadClose()"
    >
      <!--@ok="hadleFileUploadClose"-->
      <file-pond
        ref="pondOne"
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
      />
      <!--@processfile="handleProcessFile"-->
    </b-modal>
    <b-modal
      id="modal-previewImagePower"
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
      id="modal-previewPDFPower"
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
  BButton, BRow, BCol, BTable, BCard, BFormCheckbox, BImg, BSpinner,
} from 'bootstrap-vue'
import { localize } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { $apiUrl } from '@serverConfig'
import { required } from '@validations'

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

//  Import styles
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BTable,
    BCard,
    BFormCheckbox,
    BImg,
    BSpinner,
  },
  props: {
    fileContainersData: {
      type: Array,
      required: true,
      default: () => [],
    },
    fileCloneContainersData: {
      type: Array,
      required: true,
      default: () => [],
    },
    powerBillData: {
      type: Object,
      required: true,
    },
    baseData: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
    containerFiles: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  data() {
    return {
      previewFile: '',
      loadingPreviewFile: false,
      apiUrl: $apiUrl,
      accessToken: localStorage.getItem('accessToken'),
      gender_options: [
        { value: null, text: 'Please select an option' },
        { value: '1', text: 'Mr.' },
        { value: '2', text: 'Mrs.' },
      ],
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
        iban: {
          blocks: [4, 4, 4, 4, 4],
          uppercase: true,
        },
      },
      required,
    }
  },
  methods: {
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
      let fileName = `${this.$props.user.firstName.toLowerCase()}_${this.$props.user.lastName.toLowerCase()}_verbrauchsabrechnung`
      fileName = `${fileName}_${uuidv4()}`
      return `${fileName}${file.extension}`
    },
    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
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
            store.dispatch('app-power-plant/deleteFile', { id })
              .then(response => {
                /* eslint-disable no-param-reassign */
                this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */

                store.dispatch('app-power-plant/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    //  this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    this.$props.containerFiles = response1.data.payload
                    /* eslint-ebable no-param-reassign */
                    //  containerFiles.value = response1.data.payload

                    //  this should be optimized! -- being reasonable with tasks and available time would be much appreciated in development!!!
                    store.commit('app-power-plant/updateSolarPlantUpdatedAt')
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
    updateStatus() {
      store.dispatch('app-power-plant/simpleStatusUpdate', { id: this.$props.baseData.id, status: 'powerBillUploaded', value: this.$props.baseData.powerBillUploaded })
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
  },
  setup(props) {
    const fileContainerSelectedId = ref('')
    //  const containerFiles = ref([])
    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)

    const loadDocumentFiles = id => {
      store.dispatch('app-power-plant/getDocumentFiles', { id })
        .then(response => {
          //  containerFiles.value = response.data.payload

          /* eslint-disable no-param-reassign */
          props.containerFiles = response.data.payload
          /* eslint-ebable no-param-reassign */

          store.commit('app-power-plant/updateSolarPlantUpdatedAt')
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('loadfiles error response')
            /* eslint-enable no-console */
          }
        })
    }

    const hadleFileUploadClose = () => {
      console.log('close')
      loadDocumentFiles(fileContainerSelectedId.value)
    }

    //  fetch container with type 20
    const getContainerByTypeId = () => {
      //  let container = {}
      if (typeof props.fileCloneContainersData.filter(c => (c.type === 20))[0] !== 'undefined') {
        //  container = props.fileCloneContainersData.filter(c => (c.type === 20))[0]
        loadDocumentFiles(props.fileCloneContainersData.filter(c => (c.type === 20))[0].id)
        fileContainerSelectedId.value = props.fileCloneContainersData.filter(c => (c.type === 20))[0].id
      } else {
        //  container = props.fileContainersData.filter(c => (c.type === 20))[0]
        loadDocumentFiles(props.fileContainersData.filter(c => (c.type === 20))[0].id)
        fileContainerSelectedId.value = props.fileContainersData.filter(c => (c.type === 20))[0].id
      }

      //  loadDocumentFiles(container.id)
      //  fileContainerSelectedId.value = container.id
    }

    setTimeout(() => {
      getContainerByTypeId()
    }, 800)

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

    return {
      userDocumentFields,
      fileContainerSelectedId,
      FilePond,
      //  containerFiles,
      hadleFileUploadClose,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
