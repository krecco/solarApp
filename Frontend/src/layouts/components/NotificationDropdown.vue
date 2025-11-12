<template>
  <b-nav-item-dropdown
    class="dropdown-notification mr-25"
    menu-class="dropdown-menu-media"
    right
  >
    <template #button-content>
      <feather-icon
        :badge="notificationCount"
        badge-classes="bg-warning"
        class="text-body"
        icon="BellIcon"
        size="21"
      />
    </template>

    <!-- Header -->
    <li class="dropdown-menu-header">
      <div class="dropdown-header d-flex">
        <h4 class="notification-title mb-0 mr-auto">
          Benachrichtigungen
        </h4>
        <b-badge
          pill
          variant="light-primary"
        >
          {{ notificationCount }} Neu
        </b-badge>
      </div>
    </li>

    <!-- Notifications -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="scrollable-container media-list scroll-area"
      tagname="li"
    >
      <div
        v-if="activity.length > 0"
      >
        <div class="media d-flex align-items-center">
          <h6 class="font-weight-bolder mr-auto mb-0">
            Aktivitäten
          </h6>
        </div>
        <b-link
          v-for="notification in activity"
          :key="notification.id"
          @click="redirectActivity(notification.id)"
        >
          <b-media>
            <template #aside>
              <b-avatar
                size="32"
                variant="light-primary"
              >
                <feather-icon
                  v-if="notification"
                  :icon="getIcon(notification)"
                />
              </b-avatar>
            </template>
            <p class="media-heading">
              <span class="font-weight-bolder">
                {{ notification.content }}
              </span>
            </p>
          </b-media>
        </b-link>
        <li class="dropdown-menu-footer"><b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          variant="outline-secondary"
          block
          :to="{ name: 'activity' }"
        >zum Aktivitäten</b-button>
        </li>
      </div>

      <div
        v-if="webinfo.length > 0"
      >
        <div class="media d-flex align-items-center">
          <h6 class="font-weight-bolder mr-auto mb-0">
            WEB Nachrichten
          </h6>
        </div>

        <b-link
          v-for="notification in webinfo"
          :key="notification.id"
          @click="redirectWebInfo(notification.id)"
        >
          <b-media>
            <template #aside>
              <b-avatar
                size="32"
                variant="light-primary"
              >
                <feather-icon icon="MailIcon" />
              </b-avatar>
            </template>
            <p class="media-heading">
              <span class="font-weight-bolder">
                {{ notification.firstName }} {{ notification.lastName }}
              </span>
            </p>
            <small class="notification-text">WEB Nachricht.</small>
          </b-media>
        </b-link>
        <li class="dropdown-menu-footer"><b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          variant="outline-secondary"
          block
          :to="{ name: 'web-info' }"
        >zum WEB Nachrichten</b-button>
        </li>
      </div>
    </vue-perfect-scrollbar>
  </b-nav-item-dropdown>
</template>

<script>
import {
  BNavItemDropdown, BBadge, BMedia, BLink, BAvatar, BButton,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import Ripple from 'vue-ripple-directive'
import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

import storeModule from '@/store/app/navbar'

export default {
  components: {
    BNavItemDropdown,
    BBadge,
    BMedia,
    BLink,
    BAvatar,
    VuePerfectScrollbar,
    BButton,
  },
  directives: {
    Ripple,
  },
  /*
  mounted() {
    console.log('mounted')
    this.$on('fetch-notifications', () => { console.log('evtotigana') })
    console.log(this.$on)
    console.log(this.$on('fetch-notifications'))
  },
  */
  methods: {
    getIcon(item) {
      let icon = ''
      if (item.contentType === 'user') {
        icon = 'UserIcon'
      } else if (item.contentType === 'file-user') {
        icon = 'UserIcon'
      } else if (item.contentType === 'file-plant') {
        icon = 'ZapIcon'
      }

      return icon
    },

    redirectActivity(id) {
      this.$router.push({
        name: 'activity',
        params: { id },
        query: { _randomKey: new Date().getTime() },
      })
    },
    redirectWebInfo(id) {
      this.$router.push({
        name: 'web-info',
        params: { id },
        query: { _randomKey: new Date().getTime() },
      })
      // this.$router.push({ path: `/web-info-detail/${id}` })
    },
  },
  setup() {
    if (!store.hasModule('app-navbar')) store.registerModule('app-navbar', storeModule)
    const updatedAt = computed(() => store.getters['app-navbar/getUpdatedAt'])

    const activity = ref([])
    const webinfo = ref([])
    const notificationCount = ref(0)

    notificationCount.value = 0
    const fetchNotifications = () => {
      store
        .dispatch('app-navbar/webInfoLatest')
        .then(response => {
          webinfo.value = response.data
        })

      store
        .dispatch('app-navbar/activityLatest')
        .then(response => {
          activity.value = response.data
        })

      store
        .dispatch('app-navbar/countNotifications')
        .then(response => {
          notificationCount.value = response.data
        })
    }

    setInterval(() => { fetchNotifications() }, 60000)
    fetchNotifications()

    watch(updatedAt, (val, oldVal) => {
      if (val > oldVal) {
        fetchNotifications()
      }
    })

    const perfectScrollbarSettings = {
      maxScrollbarLength: 60,
      wheelPropagation: false,
    }

    return {
      perfectScrollbarSettings,
      webinfo,
      activity,
      notificationCount,
    }
  },
}
</script>

<style>

</style>
