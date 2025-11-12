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
            icon="CreditCardIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            SEPA Information
          </h4>
        </div>
        <br>
        <!-- Sepa Info: Input Fields -->
        <validation-observer ref="validateSepaForm">
          <b-form
            @submit.prevent="onSubmitSepa"
          >
            <b-row>
              <!-- Field: fullName -->
              <b-col
                cols="12"
                md="12"
              >
                <b-form-group
                  label="Kontoinhaber"
                  label-for="fullName"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="Kontoinhaber"
                    rules="required"
                  >
                    <b-form-input
                      id="fullName"
                      v-model="sepaData.fullName"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>
              <b-col
                cols="12"
                md="8"
              >
                <b-form-group
                  label="IBAN"
                  label-for="account"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="IBAN"
                    rules="required|iban"
                  >
                    <cleave
                      id="account"
                      v-model="sepaData.account"
                      class="form-control"
                      :options="options.iban"
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
                  label="BIC"
                  label-for="bic"
                >
                  <validation-provider
                    #default="{ errors }"
                    name="BIC"
                    rules="required"
                  >
                    <b-form-input
                      id="bic"
                      v-model="sepaData.bic"
                    />
                    <small class="text-warning">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </b-col>
            </b-row>
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              type="submit"
              @click.prevent="validationFormSepa"
            >
              Speichern
            </b-button>
          </b-form>
        </validation-observer>
        <!--
        <b-button
          variant="outline-secondary"
          type="reset"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
        >
          Reset
        </b-button>
        -->
      </b-col>
    </b-row>
    <br>
    <br>
  </div>
</template>

<script>
import {
  BButton, BRow, BCol, BFormGroup, BFormInput, BForm,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
import Cleave from 'vue-cleave-component'
import { avatarText } from '@core/utils/filter'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref } from '@vue/composition-api'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'

localize('de')
export default {
  components: {
    BButton,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    ValidationProvider,
    ValidationObserver,
    Cleave,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
    sepaData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      gender_options: [
        { value: null, text: 'Please select an option' },
        { value: '1', text: 'Mr.' },
        { value: '2', text: 'Mrs.' },
      ],
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
        iban: {
          blocks: [4, 4, 4, 4, 4],
          uppercase: true,
        },
      },
      required,
    }
  },
  methods: {
    validationFormSepa() {
      this.$refs.validateSepaForm.validate().then(success => {
        if (success) {
          this.onSubmitSepa()
        }
      })
    },
  },
  setup(props) {
    const toast = useToast()

    const onSubmitSepa = () => {
      console.log(props.sepaData)
      store.dispatch('app-user/editSepa', { userId: props.userData.userId, sepaData: props.sepaData })
        .then(response => {
          if (response.data.payload.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Sepa updated successfully',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Error updating sepa',
                text: response.data.payload.message,
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Error updating sepa, Error connecting to server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    // ? Demo Purpose => Update image on click of update
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, base64 => {
      // eslint-disable-next-line no-param-reassign
      props.userData.avatar = base64
    })

    return {
      onSubmitSepa,
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
