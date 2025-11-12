<template>
  <div>
    <b-link
      :to="{ name: 'user-detail' }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum Kundenportal
    </b-link>
    <br>
    <b-card>
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
        </b-row>
        <b-row>
          <b-col
            cols="12"
            lg="12"
          >
          <files
              :file-containers-data="fileContainersData"
              :user="pUser"
              :base-data="baseData"
              class="mt-2 pt-75"
            />
          </b-col>
        </b-row>
        <b-row>
          <b-col
            cols="12"
            lg="12"
          >
          <br>
          <br>
          <hr>
          <power-bill
              :power-bill-data="powerBillData"
              :base-data="baseData"
              class="mt-2 pt-75"
            />
          </b-col>
        </b-row>
    </b-card>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BLink,
  BCard,
} from 'bootstrap-vue'

import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
import BaseCard from './BaseCard.vue'
import Files from './Files.vue'
import PowerBill from './PowerBill.vue'

export default {
  components: {
    BaseCard,
    Files,
    PowerBill,

    BRow,
    BCol,
    BLink,
    BCard,
  },

  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const baseData = ref({})
    const pUser = ref({})
    const fileContainersData = ref({})
    const powerBillData = ref({})

    const fetchBaseData = () => {
      console.log(router.currentRoute.params.id)
      console.log(store)

      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          console.log(response)
          baseData.value = response.data.payload
        })
        .catch(error => {
          console.log(error)
        })
    }

    store.dispatch(`${STORE_MODULE_NAME}/getProjectUser`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { pUser.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          pUser.value = undefined
        }
      })

    store.dispatch(`${STORE_MODULE_NAME}/fetchFileContainers`, { id: router.currentRoute.params.id })
      .then(response => { fileContainersData.value = response.data.payload })

    store.dispatch('app-power-plant/getPowerBillData', { id: router.currentRoute.params.id })
      .then(response => { powerBillData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          powerBillData.value = undefined
        }
      })

    //  load data
    fetchBaseData()

    return {
      baseData,
      pUser,
      fileContainersData,
      powerBillData,
    }
  },
}
</script>
