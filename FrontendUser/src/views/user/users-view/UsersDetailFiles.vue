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
      <user-files
        :file-containers-data="fileContainersData"
        class="mt-2 pt-75"
      />
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
import UserFiles from './UserFiles.vue'

export default {
  components: {
    BCard,
    BLink,

    UserFiles,
  },
  setup() {
    const fileContainersData = ref({})

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const authUser = JSON.parse(localStorage.getItem('userData'))

    store.dispatch('app-user/fetchUserFileContainers', { id: authUser.uid })
      .then(response => { fileContainersData.value = response.data.payload })

    return {
      fileContainersData,
    }
  },
}
</script>
