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

      <!-- Forgot password-->
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
            Passwort zurücksetzen 🔒
          </b-card-title>
          <b-card-text class="mb-2">
            Geben Sie Ihre registrierte E-Mailadresse ein und wir schicken Ihnen einen sicheren Link mit dem Sie Ihr Passwort ändern können.
          </b-card-text>

          <!-- form -->
          <validation-observer ref="simpleRules">
            <b-form
              class="auth-forgot-password-form mt-2"
              @submit.prevent="validationForm"
            >
              <b-form-group
                label="Email"
                label-for="forgot-password-email"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Email"
                  rules="required|email"
                >
                  <b-form-input
                    id="forgot-password-email"
                    v-model="userEmail"
                    :state="errors.length > 0 ? false:null"
                    name="forgot-password-email"
                    placeholder="benutzer@solar.family"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-alert
                v-if="showAlert"
                variant="danger"
                show
              >
                <h4 class="alert-heading">
                  Fehler
                </h4>
                <div class="alert-body">
                  <span>{{ alertText }}</span>
                </div>
              </b-alert>

              <b-alert
                v-if="showSuccess"
                variant="primary"
                show
              >
                <h4 class="alert-heading">
                  Erfolg
                </h4>
                <div class="alert-body">
                  <span>Wir haben ihnen eine E-Mail mit einem Link und einer Erklärung geschickt, damit Sie ihren Passwort zurücksetzen können. </span>
                </div>
              </b-alert>
              <b-button
                v-if="showSuccess == false"
                type="submit"
                variant="primary"
                block
              >
                <b-spinner
                  v-if="showLoading"
                  small
                />
                Passwort zurücksetzen
              </b-button>
            </b-form>
          </validation-observer>

          <p class="text-center mt-2">
            <b-link :to="{name:'login'}">
              <feather-icon icon="ChevronLeftIcon" /> zurück zur Anmeldung
            </b-link>
          </p>
        </b-col>
      </b-col>
      <!-- /Forgot password-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
//  import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BRow, BCol, BLink, BCardTitle, BCardText, BImg, BForm, BFormGroup, BFormInput, BButton, BAlert, BSpinner,
} from 'bootstrap-vue'
import { required, email } from '@validations'
import axios from '@axios'
import store from '@/store/index'
import errorMsg from '../../../globalErrorMessages'

localize('de')
export default {
  components: {
    // VuexyLogo,
    BRow,
    BCol,
    BLink,
    BImg,
    BForm,
    BButton,
    BFormGroup,
    BFormInput,
    BCardTitle,
    BCardText,
    ValidationProvider,
    ValidationObserver,
    BAlert,
    BSpinner,
  },
  data() {
    return {
      userEmail: '',
      sideImg: require('@/assets/images/pages/forgot-password-v2.svg'),
      // validation
      required,
      email,
      showAlert: false,
      showSuccess: false,
      showLoading: false,
      alertText: '',
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
  methods: {
    validationForm() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          this.showSuccess = false
          this.showAlert = false
          this.showLoading = true
          axios
            .post('/auth/password-change-request', { email: this.userEmail })
            .then(response => {
              if (response.data.payload.status === 200) {
                this.showSuccess = true
                this.showLoading = false
              } else {
                this.showAlert = true
                this.showLoading = false
                this.alertText = 'Die E-Mail-Adresse, die Sie eingegeben haben, ist keinem aktiven solar.family Konto zugeordnet. Prüfen Sie Ihre Angaben auf Tippfehler und versuchen Sie es mit einer anderen E-Mail-Adresse.'
              }
            })
            .catch(() => {
              this.showAlert = true
              this.showLoading = false
              this.alertText = errorMsg.errorMsg.serverOffline
            })
        }
      })
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>
