<template>
  <div>
    <b-row class="match-height">
      <b-col
        lg="4"
        md="12"
      >
        <!-- bg-variant="secondary" -->
        <b-card
          text-variant="center"
          bg-variant="light-primary"
        >
          <br>
          <br>
          <br>
          <b-img
            src="@/assets/images/logo/logo-solar.svg"
            alt="Solar.Family Logo"
            style="height:40px;"
          />
          <br><br>
          <b-card-text class="m-auto w-75">
            Werden Sie Teil der solar.family
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="MailIcon"
          color="info"
          :statistic="stats.webInfoNr[0].last"
          :statistic-title="stats.webInfoNr[0].name"
          :chart-data="stats.webInfoNr"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="UsersIcon"
          color="primary"
          :statistic="stats.userNr[0].last"
          :statistic-title="stats.userNr[0].name"
          :chart-data="stats.userNr"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="ShuffleIcon"
          color="danger"
          :statistic="stats.plantForecastSent[0].last"
          :statistic-title="stats.plantForecastSent[0].name"
          :chart-data="stats.plantForecastSent"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="ClockIcon"
          color="danger"
          :statistic="stats.plantContractSent[0].last"
          :statistic-title="stats.plantContractSent[0].name"
          :chart-data="stats.plantContractSent"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="LayersIcon"
          color="primary"
          :statistic="stats.plantContractFinalized[0].last"
          :statistic-title="stats.plantContractFinalized[0].name"
          :chart-data="stats.plantContractFinalized"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="ZapIcon"
          color="primary"
          :statistic="stats.plantInUseNr[0].last"
          :statistic-title="stats.plantInUseNr[0].name"
          :chart-data="stats.plantInUseNr"
        />
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BImg,
  BRow,
  BCol,
  BCard,
  BCardText,
  //  BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import StatisticCardWithAreaChart from '@core/components/statistics-cards/StatisticCardWithAreaChart.vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import storeModule from './storeModule'

export default {
  components: {
    StatisticCardWithAreaChart,
    BRow,
    BCol,
    BCard,
    //  BAvatar,
    BCardText,
    BImg,
  },
  directives: {
    Ripple,
  },
  setup() {
    const stats = ref({
      investmentContractFinalized: [{ name: 'investmentContractFinalized', data: [], last: 0 }],
      investmentContractSent: [{ name: 'investmentContractSent', data: [], last: 0 }],
      plantContractFinalized: [{ name: 'Verträge abgeschlossen', data: [], last: 0 }],
      plantContractSent: [{ name: 'Verträge gesendet', data: [], last: 0 }],
      plantForecastSent: [{ name: 'Prognoserechnungen gesendet', data: [], last: 0 }],
      plantInstalled: [{ name: 'Anlagen installiert', data: [], last: 0 }],
      plantInUseNr: [{ name: 'Anlagen im Betrieb', data: [], last: 0 }],
      userNr: [{ name: 'Kunden', data: [], last: 0 }],
      webInfoNr: [{ name: 'WEB Nachrichten', data: [], last: 0 }],
    })

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-dash'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    store
      .dispatch(`${STORE_MODULE_NAME}/fetchStats`)
      .then(response => {
        const res = response.data.reverse()
        const investmentContractFinalized = [{ name: 'investmentContractFinalized', data: [], last: 0 }]
        const investmentContractSent = [{ name: 'investmentContractSent', data: [], last: 0 }]
        const plantContractFinalized = [{ name: 'Verträge abgeschlossen', data: [], last: 0 }]
        const plantContractSent = [{ name: 'Verträge gesendet', data: [], last: 0 }]
        const plantForecastSent = [{ name: 'Prognoserechnungen gesendet', data: [], last: 0 }]
        const plantInstalled = [{ name: 'Anlagen installiert', data: [], last: 0 }]
        const plantInUseNr = [{ name: 'Anlagen im Betrieb', data: [], last: 0 }]
        const userNr = [{ name: 'Kunden', data: [], last: 0 }]
        const webInfoNr = [{ name: 'WEB Nachrichten', data: [], last: 0 }]

        res.forEach(r => {
          investmentContractFinalized[0].data.push(r.investmentContractFinalized)
          investmentContractSent[0].data.push(r.investmentContractSent)
          plantContractFinalized[0].data.push(r.plantContractFinalized)
          plantContractSent[0].data.push(r.plantContractSent)
          plantForecastSent[0].data.push(r.plantForecastSent)
          plantInstalled[0].data.push(r.plantInstalled)
          plantInUseNr[0].data.push(r.plantInUseNr)
          userNr[0].data.push(r.userNr)
          webInfoNr[0].data.push(r.webInfoNr)
        })

        investmentContractFinalized[0].last = investmentContractFinalized[0].data[investmentContractFinalized[0].data.length - 1]
        investmentContractSent[0].last = investmentContractSent[0].data[investmentContractSent[0].data.length - 1]
        plantContractFinalized[0].last = plantContractFinalized[0].data[plantContractFinalized[0].data.length - 1]
        plantContractSent[0].last = plantContractSent[0].data[plantContractSent[0].data.length - 1]
        plantForecastSent[0].last = plantForecastSent[0].data[plantForecastSent[0].data.length - 1]
        plantInstalled[0].last = plantInstalled[0].data[plantInstalled[0].data.length - 1]
        plantInUseNr[0].last = plantInUseNr[0].data[plantInUseNr[0].data.length - 1]
        userNr[0].last = userNr[0].data[userNr[0].data.length - 1]
        webInfoNr[0].last = webInfoNr[0].data[webInfoNr[0].data.length - 1]

        stats.value.investmentContractFinalized = investmentContractFinalized
        stats.value.investmentContractSent = investmentContractSent
        stats.value.plantContractFinalized = plantContractFinalized
        stats.value.plantContractSent = plantContractSent
        stats.value.plantForecastSent = plantForecastSent
        stats.value.plantInstalled = plantInstalled
        stats.value.plantInUseNr = plantInUseNr
        stats.value.userNr = userNr
        stats.value.webInfoNr = webInfoNr
      })

    return {
      stats,
    }
  },
}
</script>
