<template>
  <div>

    <!-- Alert: No item found -->
    <b-alert
      variant="danger"
      :show="userData === undefined"
    >
      <h4 class="alert-heading">
        Error fetching user data
      </h4>
      <div class="alert-body">
        No user found with this user id. Check
        <b-link
          class="alert-link"
          :to="{ name: 'apps-users-list'}"
        >
          User List
        </b-link>
        for other users.
      </div>
    </b-alert>

    <template v-if="userData">
      <!-- First Row -->
      <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          xl="8"
          lg="8"
          md="5"
        >
          <user-view-user-info-card
            :user-data="userData"
            :address-data="addressData"
          />
        </b-col>
        <b-col
          cols="12"
          md="4"
          xl="2"
          lg="2"
        >
          <user-view-user-plan-card
            :user-data="userData"
            :plant-title="newPowerPlantTitle"
          />
        </b-col>
        <b-col
          cols="12"
          md="4"
          xl="2"
          lg="2"
        >
          <user-view-user-investment-card
            :user-data="userData"
          />
        </b-col>
      </b-row>
      <!--
      <b-row
        style="display:none"
      >
        <b-col
          cols="12"
          lg="12"
        >
          <b-card>
            <user-files
              :user-data="userData"
              :file-containers-data="fileContainersData"
              class="mt-2 pt-75"
            />
          </b-card>
        </b-col>
      </b-row>
      -->

      <b-row>
        <b-col
          cols="12"
          lg="6"
        >
          <!--
          <user-view-user-permissions-card />
          -->
          <solar-plant-card
            :solar-plant-data="solarPlantData"
            :investment-data="investmentData"
          />
        </b-col>
        <b-col
          cols="12"
          lg="6"
        >
          <user-view-user-timeline-card
            :user-activity-data="userActivityData"
          />
        </b-col>
      </b-row>

      <!--
      <invoice-list />
      -->
    </template>

  </div>
</template>

<script>

import { ref, onUnmounted } from '@vue/composition-api'
import {
  BRow, BCol, BAlert, BLink,
} from 'bootstrap-vue'
//  import InvoiceList from '@/views/apps/invoice/invoice-list/InvoiceList.vue'
import store from '@/store'
import router from '@/router'
import userStoreModule from '../userStoreModule'
import UserViewUserInfoCard from './UserViewUserInfoCard.vue'
import UserViewUserPlanCard from './UserViewUserNewSolarPlant.vue'
import UserViewUserInvestmentCard from './UserViewUserNewInvestment.vue'
import UserViewUserTimelineCard from './UserViewUserTimelineCard.vue'
//  import UserViewUserPermissionsCard from './UserViewUserPermissionsCard.vue'
//  import UserFiles from './UserFiles.vue'
import SolarPlantCard from './SolarPlantCard.vue'

export default {
  components: {
    BRow,
    BCol,
    BAlert,
    BLink,
    //  BCard,

    // Local Components
    UserViewUserInfoCard,
    UserViewUserPlanCard,
    UserViewUserTimelineCard,
    //  UserViewUserPermissionsCard,
    //  UserFiles,
    SolarPlantCard,
    UserViewUserInvestmentCard,

    //  InvoiceList,
  },
  setup() {
    const userData = ref({})
    const addressData = ref({})
    const userActivityData = ref([])
    const solarPlantData = ref([])
    const investmentData = ref([])
    const fileContainersData = ref([])
    const newPowerPlantTitle = ref('')

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    store.dispatch('app-user/fetchUser', { id: router.currentRoute.params.id })
      .then(response => {
        userData.value = response.data.payload

        store.dispatch('app-user/fetchUserAddress', { id: router.currentRoute.params.id })
          .then(response1 => {
            addressData.value = response1.data.payload

            newPowerPlantTitle.value = `${response1.data.payload.postNr} ${response1.data.payload.city}, ${response1.data.payload.street}, ${response.data.payload.lastName} ${response.data.payload.firstName}`
          })
          .catch(error => {
            if (error.response.status === 404) {
              addressData.value = undefined
            }
          })
      })
      .catch(error => {
        if (error.response.status === 404) {
          userData.value = undefined
        }
      })

    /*
    store.dispatch('app-user/fetchUserAddress', { id: router.currentRoute.params.id })
      .then(response => {
        addressData.value = response.data.payload

        newPowerPlantTitle.value = `${response.data.payload.postNr} ${response.data.payload.city} ${response.data.payload.street} ${response.data.payload.city}`
      })
      .catch(error => {
        if (error.response.status === 404) {
          addressData.value = undefined
        }
      })
      */

    store.dispatch('app-user/getDashboardActivity', { userId: router.currentRoute.params.id })
      .then(response => { userActivityData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          userActivityData.value = undefined
        }
      })

    store.dispatch('app-user/getPowerPlantList', { userId: router.currentRoute.params.id })
      .then(response => { solarPlantData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          solarPlantData.value = undefined
        }
      })

    store.dispatch('app-user/fetchUserFileContainers', { id: router.currentRoute.params.id })
      .then(response => { fileContainersData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          fileContainersData.value = undefined
        }
      })

    store.dispatch('app-user/getInvestmentList', { userId: router.currentRoute.params.id })
      .then(response => { investmentData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          investmentData.value = undefined
        }
      })

    return {
      userData,
      addressData,
      userActivityData,
      solarPlantData,
      fileContainersData,
      investmentData,
      newPowerPlantTitle,
    }
  },
}
</script>

<style>

</style>
