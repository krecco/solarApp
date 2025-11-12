import Vue from 'vue'
import { ToastPlugin, ModalPlugin } from 'bootstrap-vue'

//  chat dependency
import moment from 'moment'
import 'moment/locale/de'

//  VueAuthHref - https://www.npmjs.com/package/vue-auth-href
import VueAuthHref from 'vue-auth-href'

//  VueAuthImage - https://gitlab.com/jlalande/vue-auth-image
import VueAuthImage from 'vue-auth-image'

//  addition non vue package removed after test - not neaded using cleave.js
//  https://github.com/freearhey/vue2-filters#number
//  import Vue2Filters from 'vue2-filters'

import VueCompositionAPI from '@vue/composition-api'
// https://www.npmjs.com/package/vue-cookies
//  import VueCookies from 'vue-cookies'

import VueObserveVisibility from 'vue-observe-visibility'

//  number formatting
import numeral from 'numeral'
import numFormat from 'vue-filter-number-format'
import useJwt from '@/auth/jwt/useJwt'

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
moment.locale('de')

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

Vue.use(VueObserveVisibility)

//  Vue2Filters
//  Vue.use(Vue2Filters)

// Composition API
Vue.use(VueCompositionAPI)

//  moment lib
//  Vue.use(require('vue-moment'))
Vue.use(require('vue-moment'), {
  moment,
})

//  numberformatting
Vue.filter('numFormat', numFormat(numeral))

//  use VueCookies
//  Vue.use(VueCookies)

// import core styles
require('@core/scss/core.scss')

// import assets styles
require('@/assets/scss/style.scss')

Vue.filter('formatDate', value => moment(String(value)).format('DD/MM/YYYY'))
Vue.filter('formatTime', value => moment(String(value)).format('hh:mm'))

Vue.config.productionTip = false

new Vue({
  router,
  store,
  i18n,
  render: h => h(App),
}).$mount('#app')
