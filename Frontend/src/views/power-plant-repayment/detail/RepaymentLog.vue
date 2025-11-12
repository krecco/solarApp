<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        lg="12"
        md="12"
      >
        <b-card>
          <h3>Rückzahlung Log</h3>
          <b-table
            responsive="sm"
            :items="repaymenLog"
            :fields="tableColumns"
            sticky-header="true"
            style="height:350px;"
          >
            <template
              #cell(documentName)="data"
            >
              <a
                v-b-modal.modal-previewPDFRepayment
                nohref
                @click="loadPreview(data.item.id, 'repayment')"
              >
                {{ data.item.documentName }}
              </a>
            </template>
            <template #cell(amountProduction)="data">
              {{ Number(data.item.amountProduction) | numFormat('0,0.00') }}
            </template>
            <template #cell(amountToPay)="data">
              - {{ Number(data.item.amountToPay) | numFormat('0,0.00') }}
            </template>
            <template #cell(amount)="data">
              {{ Number(data.item.amount) | numFormat('0,0.00') }}
            </template>
            <template #cell(datumGenerated)="data">
              {{ data.item.datumGenerated | moment("DD.MM. YYYY") }}
            </template>
            <template #cell(datumPaid)="data">
              <span
                v-if="data.item.datumPaid !== null"
              >
                {{ data.item.datumPaid | moment("DD.MM. YYYY") }}
              </span>
              <b-button
                v-if="data.item.hasReminders === true"
                v-b-modal.modal-repaymenyReminder
                variant="flat-danger"
                @click="loadRepaymentReminder(data.item.id)"
              >
                Mahnung
              </b-button>
            </template>

            <!-- Column: Actions -->
            <template #cell(actions)="data">
              <b-dropdown
                variant="link"
                no-caret
                :right="$store.state.appConfig.isRTL"
              >

                <template #button-content>
                  <feather-icon
                    icon="MoreVerticalIcon"
                    size="16"
                    class="align-middle text-body"
                  />
                </template>

                <b-dropdown-item @click="markPaid(data.item.id)">
                  <feather-icon icon="CheckIcon" />
                  <span class="align-middle ml-50">Bezahlt</span>
                </b-dropdown-item>
              </b-dropdown>
            </template>

          </b-table>
        </b-card>
      </b-col>
    </b-row>
    <b-modal
      id="modal-previewPDFRepayment"
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
    <b-modal
      id="modal-repaymenyReminder"
      ok-only
      ok-title="Schliesen"
      button-size="sm"
      centered
      size="xl"
      title="Mahnung Log"
    >
      <b-card-text>
        <b-table
          responsive="sm"
          :items="repaymenReminderLog"
          :fields="tableRepaymentReminderColumns"
          sticky-header="true"
          style="height:350px;"
        >
          <template
            #cell(documentName)="data"
          >
            <a
              v-b-modal.modal-previewPDFRepayment
              nohref
              @click="loadPreview(data.item.id, 'repayment_reminder')"
            >
              {{ data.item.documentName }}
            </a>
          </template>
          <template #cell(amountProduction)="data">
            {{ Number(data.item.amountProduction) | numFormat('0,0.00') }}
          </template>
          <template #cell(amountToPay)="data">
            - {{ Number(data.item.amountToPay) | numFormat('0,0.00') }}
          </template>
          <template #cell(amount)="data">
            {{ Number(data.item.amount) | numFormat('0,0.00') }}
          </template>
          <template #cell(datumGenerated)="data">
            {{ data.item.datumGenerated | moment("DD.MM. YYYY") }}
          </template>
          <template #cell(datumPaid)="data">
            {{ data.item.datumPaid | moment("DD.MM. YYYY") }}
          </template>

          <!-- Column: Actions -->
          <!--
          <template #cell(actions)="data">
            <b-dropdown
              variant="link"
              no-caret
              :right="$store.state.appConfig.isRTL"
            >
              <template #button-content>
                <feather-icon
                  icon="MoreVerticalIcon"
                  size="16"
                  class="align-middle text-body"
                />
              </template>

              <b-dropdown-item @click="markPaid(data.item.id)">
                <feather-icon icon="CheckIcon" />
                <span class="align-middle ml-50">Bezahlt</span>
              </b-dropdown-item>
            </b-dropdown>
          </template>
          -->
        </b-table>
      </b-card-text>
    </b-modal>
  </div>
</template>

<script>
import {
  BButton,
  BCard,
  BRow,
  BCol,
  BTable,
  BModal,
  BSpinner,
  BDropdown,
  BDropdownItem,
  BCardText,
} from 'bootstrap-vue'
import {
  ref,
  onUnmounted,
  watch,
  computed,
  //  nextTick,
} from '@vue/composition-api'
import { base64StringToBlob } from 'blob-util'
import router from '@/router'
import store from '@/store'
import storeModule from '../storeModule'

export default {
  components: {
    BButton,
    BCard,
    BRow,
    BCol,
    BTable,
    BModal,
    BSpinner,
    BDropdown,
    BDropdownItem,
    BCardText,
  },
  data() {
    return {
      previewFile: '',
      loadingPreviewFile: false,
    }
  },
  methods: {
    loadPreview(id, type) {
      this.previewFile = ''
      this.loadingPreviewFile = true
      store.dispatch('app-power-plant-repayment/getFileBaseGenerated', { id, type })
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
  },
  setup(props, ctx) {
    const STORE_MODULE_NAME = 'app-power-plant-repayment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    //  const toast = useToast()

    const repaymenLog = ref([])
    const repaymenReminderLog = ref([])

    const fetchRepaymentLog = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchRepaymentLog`, { id: router.currentRoute.params.id })
        .then(response => {
          repaymenLog.value = response.data

          //  this is just a fake calculation to show placeholders
          let amount = 0
          for (let i = 0; i < response.data.length; i += 1) {
            if (response.data[i].paymentVerified === true) {
              amount += response.data[i].amount
            }
          }

          store.commit('app-power-plant-repayment/updateContractRepaidSum', amount)
        })
    }

    //  load data
    fetchRepaymentLog()

    const markPaid = id => {
      ctx.root.$bvModal
        .msgBoxConfirm('Rechnung als bezahlt markieren?', {
          title: 'Bezahlt markieren',
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
            store.dispatch(`${STORE_MODULE_NAME}/markPaid`, { id })
              .then(() => {
                fetchRepaymentLog()
              })
          }
        })
    }

    const tempRefreshListAt = computed(() => store.getters['app-power-plant-repayment/tempRefreshListAt'])
    watch(tempRefreshListAt, (val, oldVal) => {
      console.log(val)
      console.log(oldVal)
      if (val > oldVal) {
        fetchRepaymentLog()
      }
    })

    const loadRepaymentReminder = id => {
      store
        .dispatch(`${STORE_MODULE_NAME}/getPlantReminderList`, { id })
        .then(response => {
          repaymenReminderLog.value = response.data
        })
    }

    const tableColumns = [
      { key: 'repaymentPeriod', label: 'Berechnungsp.' },
      { key: 'documentName', label: 'Dokument' },
      { key: 'amountProduction', label: 'Produktion' },
      { key: 'amountToPay', label: 'Ruckzahlung' },
      { key: 'amount', label: 'Betrag' },
      { key: 'datumGenerated', label: 'Datum', thStyle: { width: '150px !important' } },
      { key: 'datumPaid', label: 'Bezahlt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen' },
    ]

    const tableRepaymentReminderColumns = [
      { key: 'reminderNr', label: 'Version' },
      { key: 'documentName', label: 'Dokument' },
      { key: 'amount', label: 'Betrag' },
      { key: 'datumGenerated', label: 'Datum', thStyle: { width: '150px !important' } },
      { key: 'datumPaid', label: 'Bezahlt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen' },
    ]

    return {
      repaymenLog,
      tableColumns,
      markPaid,
      loadRepaymentReminder,
      tableRepaymentReminderColumns,
      repaymenReminderLog,
    }
  },
}
</script>
