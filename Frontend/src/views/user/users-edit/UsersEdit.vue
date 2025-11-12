<template>
  <component :is="userData === undefined ? 'div' : 'div'">
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
    <b-link
      :to="{ name: 'user-detail', params: { id: this.$router.currentRoute.params.id } }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zu Kunden-Cockpit
    </b-link>
    <br>
    <b-card>
      <b-tabs
        v-if="userData"
        pills
      >
        <!-- Tab: Account -->
        <b-tab active>
          <template #title>
            <feather-icon
              icon="UserIcon"
              size="16"
              class="mr-0 mr-sm-50"
            />
            <span class="d-none d-sm-inline">Stammdaten</span>
          </template>
          <user-edit-tab-account
            :user-data="userData"
            :address-data="addressData"
            class="mt-2 pt-75"
          />
        </b-tab>
        <!-- Tab: SEPA -->
        <b-tab>
          <template #title>
            <feather-icon
              icon="CreditCardIcon"
              size="16"
              class="mr-0 mr-sm-50"
            />
            <span class="d-none d-sm-inline">Zahlungsdaten</span>
          </template>
          <user-bill-and-sepa
            :user-data="userData"
            :sepa-data="sepaData"
            class="mt-2 pt-75"
          />
        </b-tab>
        <!-- Tab: ID -->
        <b-tab>
          <template #title>
            <feather-icon
              icon="UserCheckIcon"
              size="16"
              class="mr-0 mr-sm-50"
            />
            <span class="d-none d-sm-inline">Ausweisdokument</span>
          </template>
          <user-files
            :user-data="userData"
            :file-containers-data="fileContainersData"
            class="mt-2 pt-75"
          />
        </b-tab>
        <!-- Tab: Account -->
        <!--
        <b-tab>
          <template #title>
            <feather-icon
              icon="KeyIcon"
              size="16"
              class="mr-0 mr-sm-50"
            />
            <span class="d-none d-sm-inline">Benutzer freischalten</span>
          </template>
          <user-reset-password
            :user-data="userData"
            class="mt-2 pt-75"
          />
        </b-tab>
        -->
      </b-tabs>
    </b-card>
  </component>
</template>

<script>
import {
  BTab, BTabs, BCard, BAlert, BLink,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import router from '@/router'
import store from '@/store'
import userStoreModule from '../userStoreModule'
import UserEditTabAccount from './UserEditTabAccount.vue'
import UserBillAndSepa from './UserBillAndSepa.vue'
import UserEditTabInformation from './UserEditTabInformation.vue'
import UserPowerPlant from './UserPowerPlant.vue'
import UserMessages from './UserMessages.vue'
import UserResetPassword from './UserResetPassword.vue'
import UserFiles from './UserFiles.vue'

export default {
  components: {
    BTab,
    BTabs,
    BCard,
    BAlert,
    BLink,

    UserEditTabAccount,
    UserBillAndSepa,
    UserEditTabInformation,
    //  UserFiles,
    UserMessages,
    UserPowerPlant,
    UserResetPassword,
    UserFiles,
  },
  setup() {
    const userData = ref({})
    const addressData = ref({})
    const sepaData = ref({})
    const fileContainersData = ref([])

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
      })
      .catch(error => {
        if (error.response.status === 404) {
          userData.value = undefined
        }
      })

    store.dispatch('app-user/fetchUserFileContainers', { id: router.currentRoute.params.id })
      .then(response => { fileContainersData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          fileContainersData.value = undefined
        }
      })

    store.dispatch('app-user/fetchUserAddress', { id: router.currentRoute.params.id })
      .then(response => { addressData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          addressData.value = undefined
        }
      })

    store.dispatch('app-user/fetchUserSepa', { id: router.currentRoute.params.id })
      .then(response => { sepaData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          sepaData.value = undefined
        }
      })

    return {
      userData,
      addressData,
      sepaData,
      fileContainersData,
    }
  },
}
</script>

<style>

</style>
