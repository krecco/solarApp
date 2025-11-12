<template>
  <div>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <div class="d-flex">
          <feather-icon
            icon="UserIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            Persönliche Daten
          </h4>
        </div>
        <br>
        <!-- User Info: Input Fields -->
        <validation-observer ref="validateUserForm">
          <b-form
            @submit.prevent="onSubmitUser"
          >
            <b-row>
              <!-- Field: Email -->
              <b-col
                cols="12"
                md="12"
              >
                <b-form-group
                  label="Email"
                  label-for="email"
                >
                  <b-form-input
                    id="email"
                    v-model="userData.email"
                    type="email"
                    disabled
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Anrede"
                  label-for="gender"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Anrede"
                    rules="required"
                  >
                    <b-form-select
                      v-model="userData.gender"
                      :options="gender_options"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Titel vorangestellt"
                  label-for="titlePrefix"
                >
                  <b-form-input
                    id="titlePrefix"
                    v-model="userData.titlePrefix"
                    autofocus
                    trim
                    placeholder="z.B. Mag. oder Dr."
                  />
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Title nachgestellt"
                  label-for="titleSuffix"
                >
                  <b-form-input
                    id="titleSuffix"
                    v-model="userData.titleSuffix"
                    autofocus
                    trim
                    placeholder="z.B. MBA, MsC, etc."
                  />
                </b-form-group>
              </b-col>

              <!-- Field: Username -->
              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Vorname"
                  label-for="firstname"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Vorname"
                    rules="required"
                  >
                    <b-form-input
                      id="firstname"
                      v-model="userData.firstName"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <!-- Field: Full Name -->
              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Nachname"
                  label-for="lastName"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Nachname"
                    rules="required"
                  >
                    <b-form-input
                      id="lastName"
                      v-model="userData.lastName"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Telefonnummer"
                  label-for="phoneNr"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Telefonnummer"
                    rules="required"
                  >
                    <b-form-input
                      id="phoneNr"
                      v-model="userData.phoneNr"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

            </b-row>
            <!--
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              type="submit"
              @click.prevent="validationFormUser"
            >
              Speichern
            </b-button>
            -->
          </b-form>
        </validation-observer>
      </b-col>
    </b-row>
    <br>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <div class="d-flex">
          <feather-icon
            icon="HomeIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            Adresse
          </h4>
        </div>
        <br>
        <!-- User Info: Input Fields -->
        <validation-observer ref="validateAddressForm">
          <b-form
            @submit.prevent="onSubmitAddress"
          >
            <b-row>
              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Strasse"
                  label-for="street"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Strasse"
                    rules="required"
                  >
                    <b-form-input
                      id="street"
                      v-model="addressData.street"
                      autofocus
                      trim
                      placeholder="Eingabe Strasse"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Postleitzahl"
                  label-for="postNr"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Postleitzahl"
                    rules="required"
                  >
                    <b-form-input
                      id="postNr"
                      v-model="addressData.postNr"
                      autofocus
                      trim
                      placeholder="Eingabe Plz"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="4"
              >
                <b-form-group
                  label="Ort"
                  label-for="city"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Ort"
                    rules="required"
                  >
                    <b-form-input
                      id="city"
                      v-model="addressData.city"
                      autofocus
                      trim
                      placeholder="Eingabe Ort"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>

              <b-col
                cols="12"
                md="12"
              >
                <b-form-group
                  label="Adress-Zusatz"
                  label-for="addressExtra"
                >
                  <b-form-input
                    id="addressExtra"
                    v-model="addressData.addressExtra"
                    autofocus
                    trim
                    placeholder="Eingabe Adress-Zusatz"
                  />
                </b-form-group>
              </b-col>

            </b-row>
            <!--
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              type="submit"
              @click.prevent="validationFormAddress"
            >
              Speichern
            </b-button>
            -->
          </b-form>
        </validation-observer>
      </b-col>
    </b-row>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <b-button
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          type="submit"
          @click.prevent="validateUserAndAddress"
        >
          Speichern
        </b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BButton, BRow, BCol, BFormGroup, BFormInput, BForm, BFormSelect,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
//  import Cleave from 'vue-cleave-component'
import { avatarText } from '@core/utils/filter'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref } from '@vue/composition-api'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'
import errorMsg from '../../../../globalErrorMessages'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    BFormSelect,
    ValidationProvider,
    ValidationObserver,
    //  Cleave,
  },
  directives: {
    Ripple,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
    addressData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      gender_options: [
        { value: null, text: 'Bitte auswählen' },
        { value: '1', text: 'Herr' },
        { value: '2', text: 'Frau' },
        { value: '3', text: 'Divers' },
      ],
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
      required,
    }
  },
  methods: {
    /*
    validationFormUser() {
      this.$refs.validateUserForm.validate().then(success => {
        if (success) {
          this.onSubmitUser()
        }
      })
    },
    validationFormAddress() {
      this.$refs.validateAddressForm.validate().then(success => {
        if (success) {
          this.onSubmitAddress()
        }
      })
    },
    */

    validateUserAndAddress() {
      console.log('validate')
      this.$refs.validateUserForm.validate().then(s1 => {
        if (s1) {
          this.$refs.validateAddressForm.validate().then(s2 => {
            if (s2) {
              this.onSubmitUser()
              this.onSubmitAddress()
            }
          })
        }
      })
    },
  },
  setup(props, { emit }) {
    const toast = useToast()

    const onSubmitUser = () => {
      store.dispatch('app-user/editUser', props.userData)
        .then(response => {
          if (response.data.payload.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erfolg',
                text: 'Benutzerdaten erfolgreich aktualisiert',
                icon: 'CheckIcon',
                variant: 'primary',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler',
                text: 'Fehler beim Aktualisieren des Benutzers, Benutzerdaten ungültig',
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }

          emit('refetch-data')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              text: errorMsg.errorMsg.serverOffline,
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    const onSubmitAddress = () => {
      console.log('On Submit addressData')
      console.log(props)
      console.log(props.addressData)
      store.dispatch('app-user/editAddress', { userId: props.userData.userId, addressData: props.addressData })
        .then(response => {
          if (response.data.payload.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erfolg',
                text: 'Adresse erfolgreich aktualisiert',
                icon: 'CheckIcon',
                variant: 'primary',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler',
                text: 'Fehler beim Aktualisieren der Adresse, Adressdaten nicht gültig',
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }

          emit('refetch-data')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              text: errorMsg.errorMsg.serverOffline,
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, base64 => {
      // eslint-disable-next-line no-param-reassign
      props.userData.avatar = base64
    })

    return {
      onSubmitUser,
      onSubmitAddress,
      avatarText,

      //  ? Demo - Update Image on click of update button
      refInputEl,
      previewEl,
      inputImageRenderer,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
