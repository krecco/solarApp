<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        md="12"
      >
        <b-link
          :to="{ name: 'power-plant-detail', params: { id: this.$router.currentRoute.params.id } }"
          variant="success"
          class="font-weight-bold d-block text-nowrap"
        >
          <feather-icon
            icon="ArrowLeftCircleIcon"
            size="16"
            class="mr-0 mr-sm-50"
          />
          Zurück zu Details
        </b-link>
        <b-link
          :to="{ name: 'user-detail', params: { id: pUser.id } }"
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
        <b-card>
          <power-bill
            :base-data="baseData"
            :power-bill-data="powerBillData"
            class="mt-2 pt-75"
          />
          <br><br>
          <hr>
          <base-card
            :base-data="baseData"
            :p-user="pUser"
            :param-id="this.$router.currentRoute.params.id"
            :tarrif-options="tarrifOptions"
          />
          <br><br>
          <hr>
          <project-user
            :base-data="baseData"
            :p-user="pUser"
            class="mt-2 pt-75"
          />
          <!--
          <br><br>
          <hr>
          <property-owner
            :base-data="baseData"
            :property-owner-list="propertyOwnerList"
            class="mt-2 pt-75"
          />
          -->
        </b-card>
      </b-col>
    </b-row>
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
//  import Files from './Files.vue'
import ProjectUser from './ProjectUser.vue'
//  import PropertyOwner from './PropertyOwner.vue'
import PowerBill from './PowerBill.vue'

export default {
  components: {
    BaseCard,
    //  Files,
    ProjectUser,
    //  PropertyOwner,
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
    //  const fileContainersData = ref({})
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

    store.dispatch(`${STORE_MODULE_NAME}/getPropertyOwnerList`, { solarPlantId: router.currentRoute.params.id })
      .then(response => { propertyOwnerList.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          propertyOwnerList.value = undefined
        }
      })

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

    store.dispatch(`${STORE_MODULE_NAME}/getPowerBillData`, { id: router.currentRoute.params.id })
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
      //  fileContainersData,
      pUser,
      propertyOwnerList,
      tarrifOptions,
      powerBillData,
    }
  },
}
</script>
