<template>
  <div v-if="options">
    <div class="d-flex align-items-center">
      <div>
        <h4 class="mb-0">
          {{ options.title }}
        </h4>
        <span>{{ options.subtitle }}</span>
      </div>
    </div>

    <!-- collapse -->
    <app-collapse
      accordion
      type="margin"
      class="mt-2"
    >

      <app-collapse-item-with-icon
        v-for="( item,index) in options"
        :id="item.id"
        :key="index"
        :title="item.title"
        :date="getDate(item)"
        :icon="getIcon(item)"
        :style="getStyle(item)"
      >
        {{ item.content }}
        <br><br>
        <b-button
          variant="outline-primary"
          @click="redirect(item)"
        >
          Mehr
        </b-button>
        <!--
        &nbsp;&nbsp;&nbsp;
        <b-button
          variant="outline-danger"
        >
          Action 2
        </b-button>
        &nbsp;&nbsp;&nbsp;
        <b-button
          variant="outline-warning"
        >
          Action 3
        </b-button>
        -->
      </app-collapse-item-with-icon>

    </app-collapse>

    <!--/ collapse -->
  </div>
</template>

<script>
import { BButton } from 'bootstrap-vue'
import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItemWithIcon from '@core/components/app-collapse/AppCollapseItemWithIcon.vue'

import store from '@/store'

export default {
  components: {
    BButton,
    AppCollapseItemWithIcon,
    AppCollapse,
  },
  props: {
    options: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    getDate(item) {
      const date = this.$moment(item.t0).format('DD.MM.YYYY HH:mm:ss')

      return date
    },

    getIcon(item) {
      let icon = ''
      if (item.contentType === 'user') {
        icon = 'UserIcon'
      } else if ((item.contentType === 'file-user') || (item.contentType === 'file-user-status')) {
        icon = 'UserIcon'
      } else if ((item.contentType === 'file-plant') || (item.contentType === 'file-plant-status')) {
        icon = 'ZapIcon'
      } else if (item.contentType === 'event-plant') {
        icon = 'ClockIcon'
      }

      return icon
    },

    getStyle(item) {
      let style = ''
      if (item.rs < 1) {
        style = 'border: 1px solid rgb(95,122,185) !important;'
      }
      return style
    },

    redirect(item) {
      if (item.rs < 1) {
        store.dispatch('app-user/updateActivity', { id: item.id })
          .then(() => {
            store.commit('app-navbar/updateUpdatedAt')
          })
      }
      if (item.contentType === 'user') {
        this.$router.push({ name: 'user-detail', params: { id: item.contentId } })
      } else if ((item.contentType === 'file-user') || (item.contentType === 'file-user-status')) {
        this.$router.push({ name: 'user-detail', params: { id: item.userId, fileContainerId: item.contentId } })
      } else if ((item.contentType === 'file-plant') || (item.contentType === 'file-plant-status')) {
        this.$router.push({ name: 'power-plant-detail', params: { id: item.parentContentId, fileContainerId: item.contentId } })
      } else if (item.contentType === 'event-plant') {
        this.$router.push({ name: 'power-plant-detail', params: { id: item.contentId } })
      } else if (item.contentType === 'frontend-msg') {
        this.$router.push({ name: 'power-plant-detail', params: { id: item.contentId } })
      }
    },
  },
}
</script>
