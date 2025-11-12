<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        md="12"
      >
        <br>
        <h5>Daten der Solaranlage</h5>
        <b-card
          border-variant="primary"
        >
          <base-card
            :base-data="baseData"
            :p-user="pUser"
            :param-id="this.$router.currentRoute.params.id"
            :tarrif-options="tarrifOptions"
          />
        </b-card>
        <br>
        <h5>Kunde zuordnen</h5>
        <b-card
          border-variant="primary"
        >
          <project-user
            :base-data="baseData"
            :p-user="pUser"
            class="mt-2 pt-75"
          />
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BCard,
} from 'bootstrap-vue'

import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import router from '@/router'

import storeModule from '../../storeModule'
import BaseCard from './BaseCard.vue'
import ProjectUser from './ProjectUser.vue'
//  import PropertyOwner from './PropertyOwner.vue'

export default {
  components: {
    BaseCard,
    //  Files,
    ProjectUser,
    //  PropertyOwner,

    BRow,
    BCol,
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
    const propertyOwnerList = ref([])
    const tarrifOptions = ref([])
    const powerBillData = ref({})

    const fetchBaseData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          baseData.value = response.data.payload
        })
    }

    /*
    store.dispatch(`${STORE_MODULE_NAME}/fetchFileContainers`, { id: router.currentRoute.params.id })
      .then(response => { fileContainersData.value = response.data.payload })
    */

    store.dispatch(`${STORE_MODULE_NAME}/getProjectUser`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { pUser.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          pUser.value = undefined
        }
      })

    /*
    store.dispatch(`${STORE_MODULE_NAME}/getPropertyOwnerList`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { propertyOwnerList.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          propertyOwnerList.value = undefined
        }
      })
      */

    store.dispatch(`${STORE_MODULE_NAME}/fetchTarrifeOptions`)
      .then(response => {
        response.data.forEach(element => {
          const options = {
            text: element.title,
            value: element.id,
          }
          tarrifOptions.value.push(options)
        })
      })

    //  load data
    fetchBaseData()

    return {
      baseData,
      //  fileContainersData,
      pUser,
      propertyOwnerList,
      tarrifOptions,
      powerBillData,
    }
  },
}
</script>
