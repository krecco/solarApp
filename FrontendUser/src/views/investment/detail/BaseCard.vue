<template>
  <div>
    <b-row
      class="match-height"
    >
    <b-col
      cols="12"
      md="3"
    >
      <b-card>
        <h3>{{ baseData.title }}</h3>
        <b-row
            class="match-height"
          >
            <b-col
              cols="12"
              md="12"
            >
              <b-row>
                <b-col>
                  Investment
                  <h2>{{ baseData.amount | numFormat('0,0') }}  EUR</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                    Zinsen
                    <h2>{{ baseData.interestRate | numFormat('0.00') }}% / Jahr</h2>
                </b-col>
              </b-row>
              <br>
            </b-col>
            <b-col
              cols="12"
              md="12"
            >
              <b-row>
                <b-col>
                    Dauer
                  <h2>{{ baseData.duration | numFormat('0,0') }} Jahren</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Rückzahlungsintervall
                  <h2>{{ baseData.repaymentInterval | numFormat('0,0') }} Jahr</h2>
                </b-col>
              </b-row>
              <!--
              <b-row>
                <b-col>
                  <br>
                  Rückzahlungsstart
                  <h2>{{ baseData.repaymentStart | moment("DD.MM. YYYY") }}</h2>
                </b-col>
              </b-row>
            </b-col>
            <b-col
              cols="12"
              md="12"
            >
              <br>
              <b-row>
                <b-col>
                  Rückzahlung zinsen
                  <h2>{{ sumInterests | numFormat('0,0.00') }}  EUR</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Gesamtaufwand
                  <h2>{{ sumRepayment | numFormat('0,0.00') }}  EUR</h2>
                </b-col>
              </b-row>

              <br>
              -->
            </b-col>
          </b-row>
      </b-card>
    </b-col>
    <b-col
      cols="12"
      md="9"
    >
      <b-card>
        <h3>{{ baseData.title }}</h3>
        <b-row
            class="match-height"
          >
            <b-col
              cols="12"
              md="12"
            >
              <b-table
                responsive="sm"
                :hover="true"
                :items="repaymentPlan"
                :fields="tableFileds"
                sticky-header="true"
                style="max-height:700px;"
              />
              <i>Alle werte im EUR.</i>
            </b-col>
        </b-row>
      </b-card>
    </b-col>
    </b-row>
    <b-modal
      id="modal-editInvestment"
      cancel-variant="outline-secondary"
      ok-title="Save"
      cancel-title="Close"
      centered
      title="Edit investment"
    >
      <!-- @ok="updateInvestment" -->
      <b-form>
        <b-form-group>
          <label for="email">Investment:</label>
          <cleave
            id="investment"
            v-model="baseData.amount"
            placeholder="Investment"
            :options="options.number"
            class="form-control"
          />
        </b-form-group>
      </b-form>
    </b-modal>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BCard,
  BTable,
  BModal,
  BForm,
  BFormGroup,
} from 'bootstrap-vue'

import { ref } from '@vue/composition-api'
import numeral from 'numeral'
import Cleave from 'vue-cleave-component'
import { numberFormat } from '@core/utils/localSettings'
import store from '@/store'
import router from '@/router'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BTable,
    BModal,
    BForm,
    BFormGroup,
    Cleave,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  methods: {
    formatEuro(value) {
      //  const response = `${numeral(value).format('0,0.00').toString()} EUR`
      return numeral(value).format('0,0.00')
    },
  },
  data() {
    return {
      options: {
        number: numberFormat,
      },
    }
  },
  setup() {
    const STORE_MODULE_NAME = 'app-investment'

    const repaymentPlan = ref([])
    const sumRepayment = ref(0)
    const sumInterests = ref(0)

    const fetchCalculation = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchCalculationData`, { id: router.currentRoute.params.id })
        .then(response => {
          sumRepayment.value = 0
          sumInterests.value = 0
          for (let i = 0; i < response.data.length; i += 1) {
            if (response.data[i].repaid === true) {
              //  eslint-disable-next-line no-param-reassign
              response.data[i]._rowVariant = 'primary'
            }

            sumRepayment.value += response.data[i].yearlyRePayment
            sumInterests.value += response.data[i].interest
          }
          repaymentPlan.value = response.data
          // eslint-disable-next-line no-param-reassign
          // props.baseData = response.data.payload
        })
    }
    fetchCalculation()

    const tableFileds = [
      { key: 'year', label: 'Jahr' },
      { key: 'remainingPayment', label: 'Restdarlehen', formatter: 'formatEuro' },
      { key: 'repaymentPerYear', label: 'Tilgung', formatter: 'formatEuro' },
      { key: 'interest', label: 'Zinsen', formatter: 'formatEuro' },
      { key: 'yearlyRePayment', label: 'Jahresrate', formatter: 'formatEuro' },
    ]

    return {
      sumRepayment,
      sumInterests,
      tableFileds,
      repaymentPlan,
    }
  },
}
</script>
