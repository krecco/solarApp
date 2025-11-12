<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link
        class="brand-logo"
        href="/login"
      >
        <b-img
          src="@/assets/images/logo/logo-solar.svg"
          alt="Solar.Family Logo"
          style="height:30px;"
        />
      </b-link>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <b-col
        lg="8"
        class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
            src="@/assets/images/solar/back1.jpg"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Reset password-->
      <b-col
        lg="4"
        class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
          sm="8"
          md="6"
          lg="12"
          class="px-xl-2 mx-auto"
        >
          <b-card-title
            title-tag="h2"
            class="font-weight-bold mb-1"
          >
            Verify Account 🔒
          </b-card-title>

          {{ status }}

          <p class="text-left mt-2">
            <b-link :to="{name:'login'}">
              <feather-icon icon="ChevronLeftIcon" /> Back to login
            </b-link>
          </p>
        </b-col>
      </b-col>
      <!-- /Reset password-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
//  import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BRow, BCol, BCardTitle, BImg, BLink,
} from 'bootstrap-vue'
import axios from '@axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { useToast } from 'vue-toastification/composition'
import router from '@/router'
import store from '@/store/index'

export default {
  components: {
    // VuexyLogo,
    BRow,
    BCol,
    BCardTitle,
    BImg,
    BLink,
    // BCardText,
  },
  props: {
    status: {
      type: String,
      default: 'Verifying your account ...',
    },
  },
  data() {
    return {
      sideImg: require('@/assets/images/pages/reset-password-v2.svg'),
    }
  },
  computed: {
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/forgot-password-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
  },
  setup(props) {
    const toast = useToast()

    const verifyData = {
      userId: router.currentRoute.params.userId,
      token: router.currentRoute.params.token,
      data: '',
    }

    axios
      .post('/auth/verify-account', verifyData)
      .then(response => {
        if (response.data.payload.status === 200) {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Password updated',
              icon: 'EditIcon',
              variant: 'success',
            },
          })
          router.push({ name: 'login' })
        } else {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Password update failed!',
              icon: 'EditIcon',
              variant: 'warning',
            },
          })
          props.status = 'Validation token fail' // eslint-disable-line no-param-reassign
        }
      })
      .catch(() => {
        props.status = 'Error Validating Account' // eslint-disable-line no-param-reassign
      })
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>
