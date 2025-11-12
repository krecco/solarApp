<template>
  <div>
    <faq-question-answer :options="webInfoData" />
  </div>
</template>

<script>
import { } from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import {
  ref,
  onUnmounted,
} from '@vue/composition-api'

import router from '@/router'
import store from '@/store'

import storeModule from './storeModule'
import FaqQuestionAnswer from './FaqQuestionAnswer.vue'

export default {
  components: {
    FaqQuestionAnswer,
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
    const webInfoData = ref({
      title: '',
      data: [],
    })

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'web-info'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const fetchData = status => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchWebInfoList`, { status })
        .then(response => {
          webInfoData.value.data = response.data
        })
        .catch(() => {
          webInfoData.value.data = []
        })
    }

    fetchData(1)

    return {
      webInfoData,
    }
  },
}
</script>

<style>

</style>
