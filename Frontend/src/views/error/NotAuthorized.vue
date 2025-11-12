<template>
  <div class="misc-wrapper">
    <b-link
      :to="loginRoute()"
      class="brand-logo"
    >
      <b-img
        src="@/assets/images/logo/logo-solar.svg"
        alt="Solar.Family Logo"
        style="height:30px;"
      />
    </b-link>

    <div class="misc-inner p-2 p-sm-3">
      <div class="w-100 text-center">
        <h2 class="mb-1">
          Sie sind nicht berechtigt! 🔐
        </h2>
        <p class="mb-2">
          Sie sind nicht berechtigt, auf diese Seite zuzugreifen.
        </p>
        <b-button
          variant="primary"
          class="mb-1 btn-sm-block"
          :to="loginRoute()"
        >Zurück</b-button>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { BLink, BImg, BButton } from 'bootstrap-vue'
import store from '@/store/index'

export default {
  components: {
    BLink, BImg, BButton,
  },
  data() {
    return {
      downImg: require('@/assets/images/pages/not-authorized.svg'),
    }
  },
  computed: {
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.downImg = require('@/assets/images/pages/not-authorized-dark.svg')
        return this.downImg
      }
      return this.downImg
    },
  },
  methods: {
    loginRoute() {
      return '/'
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-misc.scss';
</style>
