<template>
  <div
    style="padding-right:10px"
  >
    <feather-icon
      :badge="newMsgCount"
      badge-classes="bg-warning"
      class="text-body"
      style="cursor:pointer;"
      icon="MessageSquareIcon"
      size="21"
      @click="redirectToChat"
    />
    <!--
    <feather-icon
      :badge="hasUnread"
      badge-classes="bg-warning"
      class="text-body"
      style="cursor:pointer;"
      icon="MessageSquareIcon"
      size="21"
      @click="redirectToChat"
    />
    -->
  </div>
</template>

<script>
import {
//  BButton,
//  BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

import storeModule from '@/store/app/navbar'

export default {
  components: {
    //  BButton,
    // BAvatar,
  },
  directives: {
    Ripple,
  },
  methods: {
    redirectToChat() {
      this.$router.push({
        name: 'chat-lite',
        query: { _randomKey: new Date().getTime() },
      })
    },
  },
  setup() {
    if (!store.hasModule('app-navbar')) store.registerModule('app-navbar', storeModule)
    const updatedAt = computed(() => store.getters['app-navbar/getUpdatedMsgCountAt'])

    const newMsgCount = ref(0)

    const fetchNewMsgStatus = () => {
      store
        .dispatch('app-navbar/countNewMessages')
        .then(response => {
          if (response.status === 200) {
            newMsgCount.value = response.data
          }
        })
    }

    setInterval(() => { fetchNewMsgStatus() }, 60000)
    fetchNewMsgStatus()

    watch(updatedAt, (val, oldVal) => {
      if (val > oldVal) {
        fetchNewMsgStatus()
      }
    })

    return {
      newMsgCount,
    }
  },
}
</script>

<style>

</style>
