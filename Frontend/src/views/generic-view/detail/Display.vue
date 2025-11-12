<template>
  <div>
    <b-row
        class="match-height"
      >
        <b-col
          cols="12"
          md="12"
        >
          <base-card
            :base-data="baseData"
          />
        </b-col>
      </b-row>
      <b-row>
        <b-col
          cols="12"
          lg="6"
        >
          <vidget-one/>
        </b-col>
        <b-col
          cols="12"
          lg="6"
        >
          <vidget-two/>
        </b-col>
      </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
} from 'bootstrap-vue'

import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import router from '@/router'

import storeModule from '../storeModule'
import BaseCard from './BaseCard.vue'
import VidgetOne from './VidgetOne.vue'
import VidgetTwo from './VidgetTwo.vue'

export default {
  components: {
    BaseCard,
    VidgetOne,
    VidgetTwo,

    BRow,
    BCol,
  },

  setup() {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-generic'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const baseData = ref({})

    const fetchBaseData = () => {
      console.log(router.currentRoute.params.id)
      console.log(store)

      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.id })
        .then(response => {
          console.log(response)
          baseData.value = response.data
        })
        .catch(error => {
          console.log(error)
        })
    }

    //  load data
    fetchBaseData()

    return {
      baseData,
    }
  },
}
</script>
