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
      <user-edit-tab-account
        :user-data="userData"
        :address-data="addressData"
        class="mt-2 pt-75"
      />
      <!--
      <user-bill-and-sepa
        :user-data="userData"
        :sepa-data="sepaData"
        class="mt-2 pt-75"
      />
      -->
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BLink,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import userStoreModule from '../userStoreModule'
import UserEditTabAccount from './UserEditTabAccount.vue'
//  import UserBillAndSepa from './UserBillAndSepa.vue'

export default {
  components: {
    BCard,
    BLink,

    UserEditTabAccount,
    //  UserBillAndSepa,
  },
  setup() {
    const userData = ref({})
    const addressData = ref({})
    //  const sepaData = ref({})

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const authUser = JSON.parse(localStorage.getItem('userData'))

    store.dispatch('app-user/fetchUser', { id: authUser.uid })
      .then(response => {
        userData.value = response.data.payload
      })
      .catch(error => {
        if (error.response.status === 404) {
          userData.value = undefined
        }
      })

    store.dispatch('app-user/fetchUserAddress', { id: authUser.uid })
      .then(response => { addressData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          addressData.value = undefined
        }
      })

    /*
    store.dispatch('app-user/fetchUserSepa', { id: authUser.uid })
      .then(response => { sepaData.value = response.data.payload })
      .catch(error => {
        if (error.response.status === 404) {
          sepaData.value = undefined
        }
      })
      */

    return {
      userData,
      addressData,
      //  sepaData,
    }
  },
}
</script>

<style>

</style>
