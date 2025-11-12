<template>
  <b-sidebar
    id="add-new-solar-plant-sidebar"
    :visible="isAddNewSolarPlantActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @change="(val) => $emit('update:is-add-new-solar-plant-active', val)"
    @hidden="resetForm"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Neue PV-Anlage
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />
      </div>

      <!-- BODY -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Form -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >
          <h6>PV-Anlage</h6>
          <hr>
          <!-- Title -->
          <validation-provider
            #default="validationContext"
            name="Titel"
            rules="required"
          >
            <b-form-group
              label="Titel"
              label-for="title"
            >
              <b-form-input
                id="title"
                v-model="postData.title"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!--
          <b-form-group
            label="Kampagnenname"
            label-for="campaign"
          >
            <b-form-input
              id="campaign"
              v-model="postData.campaign"
              autofocus
              trim
              placeholder=""
            />
          </b-form-group>
          -->
          <b-form-group
            label="Kampagnenname"
            label-for="campaign"
          >
            <v-select
              v-model="postData.campaign"
              :options="campaignOptions"
              :clearable="false"
              label="text"
            />
          </b-form-group>

          <validation-provider
            #default="validationContext"
            name="Photovoltaik-Anlagen-Engpassleistung"
            rules="required"
          >
            <b-form-group
              label="Photovoltaik-Anlagen-Engpassleistung in kWp"
              label-for="nominalPower"
            >
              <cleave
                id="nominalPower"
                v-model="postData.nominalPower"
                class="form-control"
                autofocus
                trim
                :state="getValidationState(validationContext)"
                placeholder=""
                :options="options.number"
              />
            </b-form-group>
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>
          <!--
          <b-form-group
            label="Tarifgruppe"
            label-for="tariff"
          >
            <v-select
              v-model="postData.tariff"
              :options="tarrifOptions"
              :clearable="false"
              label="text"
            />
          </b-form-group>
          -->
          <br>
          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Hinzufügen
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="button"
              variant="outline-secondary"
              @click="hide"
            >
              Abbrechen
            </b-button>
          </div>

        </b-form>
      </validation-observer>
    </template>
  </b-sidebar>
</template>

<script>
import {
  BSidebar,
  BButton,
  BForm,
  BFormGroup,
  BFormInput,
  BFormInvalidFeedback,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Cleave from 'vue-cleave-component'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store'
import router from '@/router'

localize('de')
export default {
  components: {
    BSidebar,
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    Cleave,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewSolarPlantActive',
    event: 'update:is-add-new-solar-plant-active',
  },
  props: {
    isAddNewSolarPlantActive: {
      type: Boolean,
      required: true,
    },
    userData: {
      type: Object,
      required: true,
    },
    plantTitle: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      required,
      alphaNum,
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
      },
    }
  },
  setup(props, { emit }) {
    const toast = useToast()
    const tarrifOptions = ref([])
    const campaignOptions = ref([{
      text: 'Keine Kampagne',
      value: null,
    }])

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-user'

    /*
    const blankPostData = {
      title: `${props.userData.lastName} ${props.userData.firstName}`,
    }
    */

    //  tariff: {},
    const blankPostData = {
      title: '',
      campaign: {},
      nominalPower: '',
    }

    const postData = ref(JSON.parse(JSON.stringify(blankPostData)))
    const resetPostData = () => {
      //  postData.value = JSON.parse(JSON.stringify(blankPostData))
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetPostData)

    setTimeout(() => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchTarrifeOptions`)
        .then(response => {
          response.data.forEach(element => {
            const options = {
              text: element.title,
              value: element.id,
            }
            tarrifOptions.value.push(options)

            /* eslint prefer-destructuring: ["error", {VariableDeclarator: {object: true}}] */
            postData.value.tariff = tarrifOptions.value[0]
            postData.value.title = props.plantTitle
          })
        })

      store.dispatch(`${STORE_MODULE_NAME}/fetchCampaignOptions`)
        .then(response => {
          response.data.forEach(element => {
            const options = {
              text: element.title,
              value: element.id,
            }
            campaignOptions.value.push(options)

            /* eslint prefer-destructuring: ["error", {VariableDeclarator: {object: true}}] */
            postData.value.campaign = campaignOptions.value[0]

            store.dispatch(`${STORE_MODULE_NAME}/fetchWebInfoPlantSize`, { userId: props.userData.id })
              .then(res => {
                if (typeof res.data.systemsize !== 'undefined') {
                  /* eslint prefer-destructuring: ["error", {VariableDeclarator: {object: true}}] */
                  postData.value.nominalPower = res.data.systemsize
                }
              })
          })
        })
    }, 500)

    const onSubmit = () => {
      store.dispatch(`${STORE_MODULE_NAME}/addPowerPlant`, { userId: props.userData.id, postData: postData.value })
        .then(response => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'OK',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          emit('update:is-add-new-solar-plant-active', false)
          router.push({ name: 'power-plant-detail', params: { id: response.data.payload.id } })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    return {
      onSubmit,
      postData,

      refFormObserver,
      getValidationState,
      resetForm,
      tarrifOptions,
      campaignOptions,
    }
  },
}
</script>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
