import Vue from 'vue'
import { ToastPlugin, ModalPlugin } from 'bootstrap-vue'

// do move -- this is from chat app
import moment from 'moment'

//  VueAuthHref - https://www.npmjs.com/package/vue-auth-href
import VueAuthHref from 'vue-auth-href'

//  VueAuthImage - https://gitlab.com/jlalande/vue-auth-image
import VueAuthImage from 'vue-auth-image'

//  addition non vue package removed after test - not neaded using cleave.js
//  https://github.com/freearhey/vue2-filters#number
//  import Vue2Filters from 'vue2-filters'

import device from 'vue-device-detector'

import VueCompositionAPI from '@vue/composition-api'

//  number formatting
import numeral from 'numeral'
import numFormat from 'vue-filter-number-format'

import useJwt from '@/auth/jwt/useJwt'

// https://www.npmjs.com/package/vue-cookies
//  import VueCookies from 'vue-cookies'

import i18n from '@/libs/i18n'
import router from './router'
import store from './store'
import App from './App.vue'

// Global Components
import './global-components'

// 3rd party plugins
import '@axios'
import '@/libs/acl'
import '@/libs/portal-vue'
import '@/libs/toastification'

numeral.register('locale', 'de', {
  delimiters: {
    thousands: '.',
    decimal: ',',
  },
  currency: {
    symbol: '€',
  },
})

numeral.locale('de')

// BSV Plugin Registration
Vue.use(ToastPlugin)
Vue.use(ModalPlugin)

//  VueAuthHref
const vueAuthHrefOptions = {
  token: () => useJwt.getToken(),
  //  downloadingText: 'downloading', //works
}
Vue.use(VueAuthHref, vueAuthHrefOptions)

//  VueAuthImage
Vue.use(VueAuthImage)

//  Vue2Filters
//  Vue.use(Vue2Filters)

//  numberformatting
Vue.filter('numFormat', numFormat(numeral))

//  device
Vue.use(device)

// Composition API
Vue.use(VueCompositionAPI)

//  moment lib
Vue.use(require('vue-moment'))

//  use VueCookies
//  Vue.use(VueCookies)

// import core styles
require('@core/scss/core.scss')

// import assets styles
require('@/assets/scss/style.scss')

// do move -- this is from chat app
Vue.filter('formatDate', value => moment(String(value)).format('DD/MM/YYYY'))
Vue.filter('formatTime', value => moment(String(value)).format('hh:mm'))

Vue.config.productionTip = false

new Vue({
  router,
  store,
  i18n,
  render: h => h(App),
}).$mount('#app')
