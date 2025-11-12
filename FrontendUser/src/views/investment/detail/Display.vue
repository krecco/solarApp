<template>
  <div>
    <b-link
      :to="{ name: 'user-detail', params: { id: baseData.userId } }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Kunden
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
          />
        </b-col>
        <!--
        <b-col
          cols="12"
          md="8"
        >
          <repayment-plan
            v-if="baseData.id !== undefined"
            :base-data="baseData"
          />
        </b-col>
        -->
      </b-row>
      <!--
      <b-row>
        <b-col
          cols="12"
          lg="12"
        >
          <b-card>
            <files
              :base-data="baseData"
              :file-containers-data="fileContainersData"
              :user="pUser"
              class="mt-2 pt-75"
            />
          </b-card>
        </b-col>
      </b-row>
      -->
      <!--
      <b-row>
        <b-col
          cols="12"
          lg="12"
        >
          <b-card>
            <contracts
              :base-data="baseData"
              :file-containers-data="fileContainersData"
              :user="pUser"
              class="mt-2 pt-75"
            />
          </b-card>
        </b-col>
      </b-row>
      -->
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BLink,
  //  BCard,
} from 'bootstrap-vue'

import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
import BaseCard from './BaseCard.vue'
//  import Files from './Files.vue'
//  import Contracts from './Contracts.vue'
//  import RepaymentPlan from './RepaymentPlan.vue'

export default {
  components: {
    BaseCard,
    //  Files,
    //  Contracts,
    // RepaymentPlan,

    BRow,
    BCol,
    BLink,
    //  BCard,
  },

  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-investment'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const baseData = ref({})
    const pUser = ref({})
    const fileContainersData = ref([])

    const fetchBaseData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          baseData.value = response.data.payload
        })
    }

    store.dispatch(`${STORE_MODULE_NAME}/getInvestmentUser`, { investmentId: router.currentRoute.params.id })
      .then(response => { pUser.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          pUser.value = undefined
        }
      })

    store.dispatch(`${STORE_MODULE_NAME}/fetchFileContainers`, { id: router.currentRoute.params.id })
      .then(response => { fileContainersData.value = response.data.payload })

    //  load data
    fetchBaseData()

    return {
      baseData,
      pUser,
      fileContainersData,
    }
  },
}
</script>
