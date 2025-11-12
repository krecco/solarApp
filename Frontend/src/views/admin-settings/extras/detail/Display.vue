<template>
  <div>
    <b-link
      :to="{ name: 'admin-settings-list-extras' }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zur Liste
    </b-link>
    <br>
    <b-card>
      <validation-observer ref="validateSettingsForm">
        <b-form
          @submit.prevent="onSubmitSettingsData"
        >
          <b-row>
            <b-col
              cols="12"
              lg="8"
            >
              <b-form-group
                label="Produktbezeichnung"
                label-for="title"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Produktbezeichnung"
                  rules="required"
                >
                  <b-form-input
                    id="title"
                    v-model="settingsData.title"
                    autofocus
                    trim
                    placeholder=""
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Aktiv"
                label-for="active"
              >
                <b-form-checkbox
                  v-model="settingsData.active"
                  name="check-button"
                  switch
                  inline
                >
                  wählbar
                </b-form-checkbox>
              </b-form-group>
            </b-col>
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Preis Netto"
                label-for="price"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Preis"
                  rules="required"
                >
                  <cleave
                    id="price"
                    v-model="settingsData.price"
                    class="form-control"
                    autofocus
                    trim
                    placeholder=""
                    :options="options.numberDeep"
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <!--
            <b-col
              cols="12"
              lg="4"
            >
              <b-form-group
                label="Artikelnummer"
                label-for="ean"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Artikelnummer"
                  rules="required"
                >
                  <b-form-input
                    id="ean"
                    v-model="settingsData.ean"
                    autofocus
                    trim
                    placeholder=""
                  />
                  <small class="text-warning">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            -->
          </b-row>
          <br><br>
          <b-button
            variant="primary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            :block="$store.getters['app/currentBreakPoint'] === 'xs'"
            type="submit"
            @click.prevent="validationForm"
          >
            Speichern
          </b-button>
        </b-form>
      </validation-observer>
    </b-card>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BForm,
  BFormGroup,
  BFormInput,
  BButton,
  BCard,
  BFormCheckbox,
  BLink,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Cleave from 'vue-cleave-component'
import { ref, onUnmounted } from '@vue/composition-api'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import router from '@/router'
import store from '@/store'
import storeModule from '../../storeModule'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BForm,
    BFormGroup,
    BFormInput,
    BButton,
    BCard,
    BFormCheckbox,
    ValidationProvider,
    ValidationObserver,
    BLink,
    Cleave,
  },
  data() {
    return {
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
      required,
    }
  },
  methods: {
    validationForm() {
      this.$refs.validateSettingsForm.validate().then(success => {
        if (success) {
          this.onSubmitSettingsData()
        }
      })
    },
  },
  setup() {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-admin-settings'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const settingsData = ref({})

    const fetchSettings = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchSettingsExtras`, { settingsId: router.currentRoute.params.id })
        .then(response => {
          settingsData.value = response.data
        })
    }

    const onSubmitSettingsData = () => {
      store.dispatch(`${STORE_MODULE_NAME}/editSettingsExtras`, { baseData: settingsData.value })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Updated successfully',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Error updating',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    //  load data
    fetchSettings()

    return {
      settingsData,
      onSubmitSettingsData,
    }
  },
}
</script>
