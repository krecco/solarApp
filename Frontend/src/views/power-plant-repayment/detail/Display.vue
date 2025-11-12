<template>
  <div>
    <b-link
      :to="{ name: 'user-detail', params: { id: pUser.id } }"
      variant="success"
      class="font-weight-bold text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Kunden
    </b-link>
    &nbsp;|&nbsp;
    <b-link
      :to="{ name: 'power-plant-detail', params: { id: baseData.id } }"
      variant="success"
      class="font-weight-bold text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Anlage
    </b-link>
    <br>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        md="12"
      >
        <base-card
          v-if="baseData.id !== undefined"
          :base-data="baseData"
          :plant-tariff="plantTariff"
          :plant-campaign="plantCampaign"
          :plantUser="pUser"
        />
      </b-col>
    </b-row>
    <repayment-data
      :plant-tariff="plantTariff"
    />
    <repayment-log />
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BLink,

} from 'bootstrap-vue'

import {
  ref,
  onUnmounted,
  //  nextTick,
} from '@vue/composition-api'
//  import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
//  import { useToast } from 'vue-toastification/composition'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
import BaseCard from './BaseCard.vue'
import RepaymentLog from './RepaymentLog.vue'
import RepaymentData from './RepaymentData.vue'

export default {
  components: {
    BaseCard,
    BRow,
    BCol,
    BLink,

    RepaymentLog,
    RepaymentData,
  },
  directives: {
    Ripple,
  },
  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant-repayment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    //  const toast = useToast()

    const baseData = ref({})
    const pUser = ref({})
    const plantTariff = ref({ title: '' })
    const plantCampaign = ref({ title: 'Keine Kampagne' })

    const fetchBaseData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          baseData.value = response.data.payload

          store.dispatch(`${STORE_MODULE_NAME}/fetchTarriff`, { id: response.data.payload.tariff })
            .then(responseTariff => { plantTariff.value = responseTariff.data.payload })
        })
    }

    store.dispatch(`${STORE_MODULE_NAME}/getProjectUser`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { pUser.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          pUser.value = undefined
        }
      })

    //  load data
    fetchBaseData()

    return {
      baseData,
      pUser,
      plantTariff,
      plantCampaign,
    }
  },
}
</script>

<style>
.position-absolute {
  position: fixed !important;
}
</style>
