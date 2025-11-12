<template>
  <div>
    <div style="float:right;">
      <b-button
        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
        size="sm"
        block
        variant="outline-primary"
        style="max-width:250px;"
        @click="markAllAsRead()"
      >
        <feather-icon
          icon="CheckCircleIcon"
          class="mr-50"
        />
        Alles als gelesen markieren
      </b-button>
    </div>
    <div style="clear:both" />
    <activity-item :options="activityData" />
    <br><br>
    <b-button
      v-if="showMore"
      v-ripple.400="'rgba(255, 255, 255, 0.15)'"
      size="lg"
      block
      variant="outline-primary"
      @click="increaseActivityDataPage()"
    >
      Mehr ...
    </b-button>
  </div>
</template>

<script>
import { BButton } from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ref, onUnmounted } from '@vue/composition-api'
import router from '@/router'
import store from '@/store'

import userStoreModule from '../user/userStoreModule'

import ActivityItem from './ActivityItem.vue'

export default {
  components: {
    ActivityItem,
    BButton,
  },
  directives: {
    Ripple,
  },
  watch: {
    '$route.query': function $routeQuery() {
      this.openTab()
    },
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        if (typeof (router.currentRoute.params.id) !== 'undefined') {
          this.openTab()
        }
      }, 250)
    })
  },
  methods: {
    openTab() {
      if (typeof (router.currentRoute.params.id) !== 'undefined') {
        document.getElementById(router.currentRoute.params.id).children[0].click()
      }
    },
  },
  setup() {
    const activityData = ref([])
    const activityDataPage = ref(0)
    const showMore = ref(false)
    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const getActivityData = () => {
      if (typeof (router.currentRoute.params.id) === 'undefined') {
        showMore.value = true
        store.dispatch('app-user/getActivity', { page: activityDataPage.value })
          .then(response => {
            /* eslint-disable no-param-reassign */
            activityData.value = [...activityData.value, ...response.data]
            /* eslint-ebable no-param-reassign */
          })
          .catch(error => {
            console.log(error)
          })
      } else {
        showMore.value = false
        store.dispatch('app-user/getActivityNew')
          .then(response => {
            /* eslint-disable no-param-reassign */
            activityData.value = [...activityData.value, ...response.data]
            /* eslint-ebable no-param-reassign */
          })
          .catch(error => {
            console.log(error)
          })
      }
    }

    const markAllAsRead = () => {
      store.dispatch('app-user/setActivityAsRead')
        .then(() => {
          store.commit('app-navbar/updateUpdatedAt')

          activityData.value = []
          activityDataPage.value = 0
          getActivityData()
        })
        .catch(error => {
          console.log(error)
        })
    }

    const increaseActivityDataPage = () => {
      activityDataPage.value += 1
      getActivityData()
    }

    getActivityData()

    return {
      activityData,
      activityDataPage,
      increaseActivityDataPage,
      markAllAsRead,
      showMore,
    }
  },
}
</script>

<style>

</style>
