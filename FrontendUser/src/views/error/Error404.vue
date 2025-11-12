<template>
  <!-- Error page-->
  <div class="misc-wrapper">
    <!-- Brand logo-->
    <b-link
      :to="{path:'/'}"
      class="brand-logo"
    >
      <b-img
        src="@/assets/images/logo/logo-solar.svg"
        alt="Solar.Family Logo"
        style="height:30px;"
      />
    </b-link>
    <!-- /Brand logo-->

    <div class="misc-inner p-2 p-sm-3">
      <div class="w-100 text-center">
        <h2 class="mb-1">
          Seite nicht gefunden 🕵🏻‍♀️
        </h2>
        <p class="mb-2">
          Die angeforderte URL wurde auf diesem Server nicht gefunden.
        </p>

        <b-button
          variant="primary"
          class="mb-2 btn-sm-block"
          :to="{path:'/'}"
        >
          zurück zum Portal
        </b-button>
      </div>
    </div>
  </div>
<!-- / Error page-->
</template>

<script>
/* eslint-disable global-require */
import { BLink, BButton, BImg } from 'bootstrap-vue'
import store from '@/store/index'

export default {
  components: {
    BLink,
    BButton,
    BImg,
  },
  data() {
    return {
      downImg: require('@/assets/images/pages/error.svg'),
    }
  },
  computed: {
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.downImg = require('@/assets/images/pages/error-dark.svg')
        return this.downImg
      }
      return this.downImg
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-misc.scss';
</style>
