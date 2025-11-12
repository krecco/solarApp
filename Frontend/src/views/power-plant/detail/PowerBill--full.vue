<template>
  <div>
    <b-row>
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
              <div class="text-left">
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
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
      <b-col
        cols="12"
        md="12"
      >
        <br><br>
        <!-- User Info: Input Fields -->
        <validation-observer ref="validatePowerBillForm">
          <b-form
            @submit.prevent="onSubmitPowerBill"
          >
            <b-row>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Jahresverbrauch (Kw) Zähler 1"
                  label-for="consumption"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Jahresverbrauch (Kw)"
                    rules="required"
                  >
                    <cleave
                      id="consumption"
                      class="form-control"
                      v-model="powerBillData.consumption"
                      trim
                      placeholder="Eingabe Jahresverbrauch in Kw"
                      :options="options.number"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Kosten/Jahr (EUR) Zähler 1"
                  label-for="consumptionValue"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Cost (EUR)"
                    rules="required"
                  >
                    <cleave
                      id="consumptionValue"
                      class="form-control"
                      v-model="powerBillData.consumptionValue"
                      trim
                      placeholder="Enter Consumption Value"
                      :options="options.number"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Vertrag Zähler 1"
                  label-for="contract"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Vertrag"
                    rules="required"
                  >
                    <b-form-input
                      id="contract"
                      v-model="powerBillData.contract"
                      trim
                      placeholder="Eingabe Vertrags-Option"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Jahresverbrauch (Kw) Zähler 2"
                  label-for="consumption2"
                >
                  <cleave
                    id="consumption2"
                    class="form-control"
                    v-model="powerBillData.consumption2"
                    trim
                    placeholder="Eingabe Jahresverbrauch in Kw"
                    :options="options.number"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Kosten/Jahr (EUR) Zähler 2"
                  label-for="consumptionValue2"
                >
                  <cleave
                    id="consumptionValue2"
                    class="form-control"
                    v-model="powerBillData.consumptionValue2"
                    trim
                    placeholder="Enter Consumption Value"
                    :options="options.number"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Vertrag Zähler 2"
                  label-for="contract2"
                >
                  <b-form-input
                    id="contract2"
                    v-model="powerBillData.contract2"
                    trim
                    placeholder="Eingabe Vertrags-Option"
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Rechnungs-Nr."
                  label-for="billNo"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Rechnungs-Nr."
                    rules="required"
                  >
                    <b-form-input
                      id="billNo"
                      v-model="powerBillData.billNo"
                      trim
                      placeholder="Eingabe Rechnungs-Nr."
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Rechnungsperiode"
                  label-for="billPeriod"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Rechnungsperiode"
                    rules="required"
                  >
                    <b-form-input
                      id="billPeriod"
                      v-model="powerBillData.billPeriod"
                      trim
                      placeholder="Eingabe von-bis"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Energielieferant"
                  label-for="provider"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Energielieferant"
                    rules="required"
                  >
                    <b-form-input
                      id="provider"
                      v-model="powerBillData.provider"
                      trim
                      placeholder="Eingabe Energielieferant"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="12"
              >
                <b-form-group
                  label="Netzbetreiber"
                  label-for="netProvider"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Netzbetreiber"
                    rules="required"
                  >
                    <b-form-input
                      id="netProvider"
                      v-model="powerBillData.netProvider"
                      trim
                      placeholder="Eingabe Netzbetreiber"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

            </b-row>
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              type="submit"
              @click.prevent="validationFormPowerBill"
            >
              Speichern
            </b-button>
          </b-form>
        </validation-observer>
        <!--
        <b-button
          variant="outline-secondary"
          type="reset"User() {
      this.$refs.validters['app/currentBreakPoint'] === 'xs'"
        >
          Reset
        </b-button>
        -->
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
  </div>
</template>

<script>
import {
  BButton, BRow, BCol, BFormGroup, BFormInput, BForm, BTable, BCard,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { $apiUrl } from '@serverConfig'
import { required } from '@validations'
import Cleave from 'vue-cleave-component'
import { useToast } from 'vue-toastification/composition'

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

import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'
import router from '@/router'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    ValidationProvider,
    ValidationObserver,
    Cleave,
    BTable,
    BCard,
  },
  props: {
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
  },
  data() {
    return {
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
    fileRenameFunction(file) {
      let fileName = `${this.$props.user.firstName.toLowerCase()}_${this.$props.user.lastName.toLowerCase()}_Verbrauchsabrechnung`

      fileName = `${fileName}_${uuidv4()}`

      /*
      console.log(uuidv4())
      const fileName = 'klocna_'
      */

      return `${fileName}${file.extension}`
    },
    validationFormPowerBill() {
      this.$refs.validatePowerBillForm.validate().then(success => {
        if (success) {
          this.onSubmitPowerBill()
        }
      })
    },
    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },

  },
  setup(props) {
    const toast = useToast()
    const fileContainerSelectedId = ref('')
    const containerFiles = ref([])
    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)

    const onSubmitPowerBill = () => {
      store.dispatch('app-power-plant/editBillData', { plantId: router.currentRoute.params.id, billData: props.powerBillData })
        .then(response => {
          if (response.data.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Daten wurden aktualisiert',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Daten wurden nicht aktualisiert!',
                text: response.data.payload.message,
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler bei der Verbindung zum Server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    const loadDocumentFiles = id => {
      store.dispatch('app-power-plant/getDocumentFiles', { id })
        .then(response => {
          console.log(response)
          /* eslint-disable no-param-reassign */
          //  props.fileContainersData.find(x => x.id === row.item.id).files = response.data.payload
          /* eslint-ebable no-param-reassign */
          containerFiles.value = response.data.payload
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

    //  fetch container with type 29
    const getContainerByTypeId = () => {
      console.log(props.fileContainersData)
      const container = props.fileContainersData.filter(c => (c.type === 29))[0]

      loadDocumentFiles(container.id)
      fileContainerSelectedId.value = container.id
    }

    setTimeout(() => {
      getContainerByTypeId()
    }, 800)

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

    return {
      onSubmitPowerBill,
      userDocumentFields,
      fileContainerSelectedId,
      FilePond,
      containerFiles,
      hadleFileUploadClose,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
