<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link class="brand-logo">
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
            alt="solar.family login"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Login-->
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
            solar.family
          </b-card-title>
          <b-card-text class="mb-2">
            Bitte melden Sie sich in Ihrem Konto an
          </b-card-text>

          <!-- form -->
          <validation-observer ref="loginValidation">
            <b-form
              class="auth-login-form mt-2"
              @submit.prevent
            >
              <!-- email -->
              <b-form-group
                label="Email"
                label-for="login-email"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Email"
                  rules="required|email"
                >
                  <b-form-input
                    id="login-email"
                    v-model="userEmail"
                    :state="errors.length > 0 ? false:null"
                    name="login-email"
                    placeholder="benutzer@solar.family"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- forgot password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="login-password">Passwort</label>
                  <b-link :to="{name:'forgot-password'}">
                    <small>Passwort vergessen?</small>
                  </b-link>
                </div>
                <validation-provider
                  #default="{ errors }"
                  name="Passwort"
                  rules="required"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="login-password"
                      v-model="password"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      :type="passwordFieldType"
                      name="login-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
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

              <!-- submit buttons -->
              <b-button
                type="submit"
                variant="primary"
                block
                @click="validationForm"
              >
                Anmelden
              </b-button>
            </b-form>
          </validation-observer>
          <!--
          <b-card-text class="text-center mt-2">
            <span>New on our platform? </span>
            <b-link :to="{name:'register'}">
              <span>&nbsp;Create an account</span>
            </b-link>
          </b-card-text>
          -->
        </b-col>
      </b-col>
    <!-- /Login-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import {
  BRow, BCol, BLink, BFormGroup, BFormInput, BInputGroupAppend, BInputGroup, BCardText, BCardTitle, BImg, BForm, BButton, BAlert,
} from 'bootstrap-vue'
import { required, email } from '@validations'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import jwtDecode from 'jwt-decode'
import store from '@/store/index'
//  import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import useJwt from '@/auth/jwt/useJwt'
import errorMsg from '../../../globalErrorMessages'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BLink,
    BFormGroup,
    BFormInput,
    BInputGroupAppend,
    BInputGroup,
    BCardText,
    BCardTitle,
    BImg,
    BForm,
    BButton,
    ValidationProvider,
    ValidationObserver,
    BAlert,
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      status: '',
      password: '',
      userEmail: '',
      sideImg: require('@/assets/images/pages/login-v2.svg'),
      // validation rulesimport store from '@/store/index'
      required,
      email,
      showAlert: false,
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
        this.sideImg = require('@/assets/images/pages/login-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
  },
  methods: {
    getUserAbility() {
      const managerAbility = [
        {
          action: 'manage',
          subject: 'Users',
        },
        {
          action: 'manage',
          subject: 'SolarPlant',
        },
        {
          action: 'manage',
          subject: 'Auth',
        },
      ]

      return managerAbility
    },
    getMangerAbility() {
      const managerAbility = [
        {
          action: 'manage',
          subject: 'Dashboard',
        },
        {
          action: 'manage',
          subject: 'Users',
        },
        {
          action: 'manage',
          subject: 'SolarPlant',
        },
        {
          action: 'manage',
          subject: 'Auth',
        },
      ]

      return managerAbility
    },
    getAdminAbility() {
      const adminAbility = [
        {
          action: 'manage',
          subject: 'all',
        },
      ]

      return adminAbility
    },
    validationForm() {
      //  This is temp only, do logic first!
      //  this.$router.push('/')
      this.$refs.loginValidation.validate().then(success => {
        if (success) {
          this.showAlert = false
          useJwt.login({
            username: this.userEmail,
            password: this.password,
          })
            .then(response => {
              if (typeof (response.data.payload.accessToken) !== 'undefined') {
                useJwt.setToken(response.data.payload.accessToken)
                useJwt.setRefreshToken(response.data.payload.refreshToken)

                const token = response.data.payload.accessToken
                const decodedToken = jwtDecode(token)

                let userAbility = []
                if (decodedToken.realm_access.roles.includes('user')) {
                  userAbility = this.getUserAbility()
                }

                if (decodedToken.realm_access.roles.includes('manager')) {
                  userAbility = this.getMangerAbility()
                }

                if (decodedToken.realm_access.roles.includes('admin')) {
                  userAbility = this.getAdminAbility()
                }

                const userData = {
                  id: decodedToken.sub,
                  //  fullName: decodedToken.name,
                  fullName: `${response.data.payload.firstName} ${response.data.payload.lastName}`,
                  username: decodedToken.preferred_username,
                  avatar: '',
                  email: decodedToken.email,
                  ability: userAbility,
                  uid: response.data.payload.uid,
                  firstName: response.data.payload.firstName,
                  lastName: response.data.payload.lastName,
                }

                // Setting user data in localStorage
                localStorage.setItem('userData', JSON.stringify(userData))

                // Updating user ability in CASL plugin instance
                this.$ability.update(userData.ability)

                /*
                this.$toast({
                  component: ToastificationContent,
                  position: 'top-right',
                  props: {
                    title: `Willkommen ${userData.fullName || userData.username}`,
                    icon: 'CoffeeIcon',
                    variant: 'success',
                    text: 'Weiterleitung zum Dashboard',
                  },
                })
                */

                this.$router.go('/')

                /*
                this.$router.replace('/')
                  .then(() => {
                    this.$toast({
                      component: ToastificationContent,
                      position: 'top-right',
                      props: {
                        title: `Welcome ${userData.fullName || userData.username}`,
                        icon: 'CoffeeIcon',
                        variant: 'success',
                        text: 'You have successfully logged in.',
                      },
                    })
                  })
                */
              } else {
                this.showAlert = true
                this.alertText = 'Die angegebene Kombination aus E-Mail-Adresse und Passwort ist ungültig.'
              }
            })
            .catch(() => {
              this.showAlert = true
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
