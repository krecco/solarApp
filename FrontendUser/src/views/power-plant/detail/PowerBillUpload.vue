<template>
  <div>
    <h1
      class="font-weight-bolder"
    >
      Letzte Energieabrechnung
    </h1>
    <b-link
      :to="{ name: 'power-plant-detail', params: { id: this.$router.currentRoute.params.plantId } }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zur PV-Anlage
    </b-link>
    <br>
    <br>
    Damit wir Ihre PV-Anlage zielgerichtet planen können, benötigten wir einige Daten aus Ihrer letzten Energieabrechnung, wie z.B. den Jahresstromverbrauch (Wert in kWh) und den zugehörigen Rechnungsbetrag.
    <br>
    <br>
    <br>
    <b-card no-body>
      <b-card-body
        style="padding: 0;"
      >
        <div
          style="padding:20px 20px 10px 20px; background-color:rgba(124, 157, 43, 0.25);"
        >
          <h4
            class="font-weight-bolder"
          >Was ist zu tun:</h4>
        </div>
        <b-card-text
          style="padding:20px 20px 10px 20px;"
        >
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="CameraIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Machen Sie ein <b>Foto oder scannen</b> die letzte Energieabrechnung.
            </b-col>
          </b-row>
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="CheckIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Prüfen Sie, ob Ihre <b>Stromverbräuche</b> auf der Rechnung (z.B. 8.456 kWh) und der Rechnungsbetrag <b>gut sichtbar</b> sind.
            </b-col>
          </b-row>
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="UploadIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Laden Sie entweder <b>alle Fotos einzeln hoch</b>, oder machen Sie aus den Fotos <b>EINE *.pdf Datei</b> zum hochladen.
            </b-col>
          </b-row>
          <b-row>
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="InfoIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Für <b>Rückfragen</b> stehen wir Ihnen unter <b>+43 (0)3326 52496-100</b> jederzeit gerne zur Verfügung.
            </b-col>
          </b-row>
        </b-card-text>
      </b-card-body>
    </b-card>
    <br>
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          <!-- :stacked="true"  -->
          <b-table
            ref="powerbilltable"
            striped
            responsive
            :hover="true"
            :stacked="isMobile"
            :items="containerFiles"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(fileName)="data"
            >
              <div
                v-if="isMobile !== true && isMobileDevice() !== true"
              >
                <a
                  v-if="data.item.fileContentType === 'application/pdf'"
                  v-b-tooltip.hover.top="'Vorschau'"
                  v-b-modal.modal-previewPDFPower
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.item.fileName }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-tooltip.hover.top="'Vorschau'"
                  v-b-modal.modal-previewImagePower
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.item.fileName }}
                </a>
              </div>
              <div
                v-else
              >
                {{ data.item.fileName }}
              </div>
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
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                style="color:#df4d12"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div class="text-left">
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                v-if="isMobile !== true"
                class="text-right"
              >

                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-tooltip.hover.top="'Vorschau'"
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
                  v-b-tooltip.hover.top="'Vorschau'"
                  v-b-modal.modal-previewImagePower
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <feather-icon
                    class="mr-1"
                    icon="EyeIcon"
                  />
                </a>
                -->
                <a
                  v-if="isMobileDevice() === true"
                  v-b-tooltip.hover.top="'Download'"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                >
                  <feather-icon
                    class="mr-1"
                    icon="ArrowDownCircleIcon"
                  />
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-b-tooltip.hover.top="'Download'"
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
                  v-b-tooltip.hover.top="'Löschen'"
                  nohref
                  @click="deleteFile(row.item.id, row.item.idFileContainer)"
                >
                  <feather-icon
                    class="mr-1"
                    icon="XCircleIcon"
                  />
                </a>
              </div>
              <div
                v-if="isMobile === true"
              >
                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                >
                  <b-button
                    variant="primary"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                >
                  <b-button
                    variant="primary"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <br><br>
                <a
                  v-if="isDisabled() !== true"
                  v-b-tooltip.hover.top="'Löschen'"
                  nohref
                  @click="deleteFile(row.item.id, row.item.idFileContainer)"
                >
                  <b-button
                    variant="outline-danger"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="XCircleIcon"
                    />
                    Löschen
                  </b-button>
                </a>
              </div>
            </template>
          </b-table>
          <br>
          <br
            v-if="isMobile === true"
          >
          <b-button
            v-if="isDisabled() !== true"
            v-b-modal.modal-uploadSingle
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
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
    <br>
    <b-button
      variant="primary"
      class="mb-1 mb-sm-0 mr-0 mr-sm-1"
      type="button"
      block
    >
      <b-link
        :to="{ name: 'power-plant-detail', params: { id: this.$router.currentRoute.params.plantId } }"
        variant="primary"
        class="font-weight-bold d-block text-nowrap"
        style="color:white;"
      >
        <feather-icon
          icon="CheckCircleIcon"
          size="16"
          class="mr-0 mr-sm-50"
        />
        Hochladen beendet
      </b-link>
    </b-button>
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
  BButton,
  BRow,
  BCol,
  BTable,
  BCard,
  BLink,
  BCardText,
  BCardBody,
  BImg,
  VBTooltip,
  BSpinner,
} from 'bootstrap-vue'
import { localize } from 'vee-validate'
import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'
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

//  import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'
import storeModule from '../storeModule'
import router from '@/router'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BTable,
    BCard,
    BLink,
    BCardText,
    BCardBody,
    BImg,
    BSpinner,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    /*
    fileContainersData: {
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
    */
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
      const authUser = JSON.parse(localStorage.getItem('userData'))

      let fileName = `${authUser.firstName.toLowerCase()}_${authUser.lastName.toLowerCase()}_verbrauchsabrechnung`
      fileName = `${fileName}_${uuidv4()}`
      return `${fileName}${file.extension}`
    },
    isDisabled() {
      let isDisabled = false
      if (this.$router.currentRoute.params.canChange === true) {
        isDisabled = true
      }
      return isDisabled
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
                console.log(response)
                /* eslint-disable no-param-reassign */
                //  this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */

                store.dispatch('app-power-plant/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    //  this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    this.$props.containerFiles = response1.data.payload
                    /* eslint-ebable no-param-reassign */
                    //  containerFiles.value = response1.data.payload

                    //  this should be optimized! -- being reasonable with tasks and available time would be much appreciated in development!!!
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
    isMobileDevice() {
      let isMobileDevice = false
      if ((this.$device.mobile === true) || (this.$device.ios === true) || (this.$device.android === true)) {
        isMobileDevice = true
      }
      return isMobileDevice
    },
  },
  setup(props) {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const fileContainerSelectedId = ref('')
    const fileContainersData = ref({})

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

    //  const containerFiles = ref([])
    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)

    const loadDocumentFiles = id => {
      console.log('loading loadDocumentFiles')
      store.dispatch('app-power-plant/getDocumentFiles', { id })
        .then(response => {
          /* eslint-disable no-param-reassign */
          props.containerFiles = response.data.payload
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

    const hadleFileUploadClose = () => {
      loadDocumentFiles(fileContainerSelectedId.value)
    }

    //  fetch container with type 20
    const getContainerByTypeId = () => {
      const container = fileContainersData.value.filter(c => (c.type === 20))[0]
      loadDocumentFiles(container.id)
      fileContainerSelectedId.value = container.id
    }

    store.dispatch('app-power-plant/fetchFileContainers', { id: router.currentRoute.params.plantId })
      .then(response => {
        fileContainersData.value = response.data.payload
        getContainerByTypeId()
      })

    const userDocumentFields = [
      {
        key: 'icon',
        label: 'Info',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '120px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '150px !important' } },
    ]

    /*
    const userDocumentFields = [
      {
        key: 'icon',
        label: '',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '120px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '150px !important' } },
    ]
    */

    return {
      userDocumentFields,
      fileContainerSelectedId,
      FilePond,
      //  containerFiles,
      hadleFileUploadClose,
      isMobile,
    }
  },
}
</script>

<style>
.table.b-table.b-table-stacked > tbody > tr > [data-label]::before {
    width: 90px !important;
}
</style>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
