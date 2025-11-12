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
            Passwort festlegen 🔒
          </b-card-title>

          <!-- form -->
          <validation-observer ref="simpleRules">
            <b-form
              class="auth-reset-password-form mt-2"
              method="POST"
              @submit.prevent="validationForm"
            >

              <!-- password -->
              <b-form-group
                label="neues Kennwort"
                label-for="reset-password-new"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Kennwort"
                  vid="Password"
                  rules="required|password"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="reset-password-new"
                      v-model="password"
                      :type="password1FieldType"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      name="reset-password-new"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="password1ToggleIcon"
                        @click="togglePassword1Visibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- confirm password -->
              <b-form-group
                label-for="reset-password-confirm"
                label="neues Kennwort wiederholen"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Kennwort wiederholen"
                  rules="required|confirmed:Password"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="reset-password-confirm"
                      v-model="cPassword"
                      :type="password2FieldType"
                      class="form-control-merge"
                      :state="errors.length > 0 ? false:null"
                      name="reset-password-confirm"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="password2ToggleIcon"
                        @click="togglePassword2Visibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
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
                  <span>Ihre Passwort wurde erfolgreich geändert. </span>
                </div>
              </b-alert>

              <!-- submit button -->
              <b-button
                v-if="showSuccess == false"
                block
                type="submit"
                variant="primary"
              >
                <b-spinner
                  v-if="showLoading"
                  small
                />
                Passwort festlegen
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
      <!-- /Reset password-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
//  import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BRow, BCol, BCardTitle, BForm, BFormGroup, BInputGroup, BInputGroupAppend, BLink, BFormInput, BButton, BImg, BAlert, BSpinner,
} from 'bootstrap-vue'
import { required } from '@validations'
import axios from '@axios'
import router from '@/router'
import store from '@/store/index'
import errorMsg from '../../../globalErrorMessages'

localize('de')
export default {
  components: {
    // VuexyLogo,
    BRow,
    BCol,
    BButton,
    BCardTitle,
    // BCardText,
    BForm,
    BFormGroup,
    BImg,
    BInputGroup,
    BLink,
    BFormInput,
    BInputGroupAppend,
    ValidationProvider,
    ValidationObserver,
    BAlert,
    BSpinner,
  },
  data() {
    return {
      userEmail: '',
      cPassword: '',
      password: '',
      sideImg: require('@/assets/images/pages/reset-password-v2.svg'),
      // validation
      required,

      // Toggle Password
      password1FieldType: 'password',
      password2FieldType: 'password',
      showAlert: false,
      showSuccess: false,
      showLoading: false,
      alertText: '',
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/reset-password-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
    password1ToggleIcon() {
      return this.password1FieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    password2ToggleIcon() {
      return this.password2FieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  methods: {
    togglePassword1Visibility() {
      this.password1FieldType = this.password1FieldType === 'password' ? 'text' : 'password'
    },
    togglePassword2Visibility() {
      this.password2FieldType = this.password2FieldType === 'password' ? 'text' : 'password'
    },
    validationForm() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          const verifyData = {
            userId: router.currentRoute.params.userId,
            token: router.currentRoute.params.token,
            data: this.password,
          }
          this.showSuccess = false
          this.showAlert = false
          this.showLoading = true
          axios
            .post('/auth/password-reset', verifyData)
            .then(response => {
              this.showLoading = false
              if (response.data.payload.status === 200) {
                this.showSuccess = true
                this.showAlert = false
                this.showLoading = false
              } else {
                this.showSuccess = false
                this.showAlert = true
                this.showLoading = false
                this.alertText = 'Es ist ein Fehler aufgetreten. Versuchen Sie es erneut zu ändern.'
              }
            })
            .catch(() => {
              this.showSuccess = false
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
