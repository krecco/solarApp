<template>
  <b-sidebar
    id="add-new-user-sidebar"
    :visible="isAddNewActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @change="(val) => $emit('update:is-add-new-active', val)"
    @hidden="resetForm"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Neue Zusatzleistung
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
          <h6>Zusatzleistung</h6>
          <hr>
          <!-- Title -->
          <validation-provider
            #default="validationContext"
            name="Produktbezeichnung"
            rules="required"
          >
            <b-form-group
              label="Produktbezeichnung"
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
          <validation-provider
            #default="validationContext"
            name="Artikelnummer"
            rules="required"
          >
            <b-form-group
              label="Artikelnummer"
              label-for="ean"
            >
              <b-form-input
                id="ean"
                v-model="postData.ean"
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
          -->

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
                v-model="postData.price"
                class="form-control"
                autofocus
                trim
                placeholder=""
                :options="options.numberDeep"
              />
              <small class="text-warning">{{ errors[0] }}</small>
            </validation-provider>
          </b-form-group>

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

import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import Cleave from 'vue-cleave-component'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import store from '@/store'
import router from '@/router'

export default {
  components: {
    BSidebar,
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    Cleave,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewActive',
    event: 'update:is-add-new-active',
  },
  props: {
    isAddNewActive: {
      type: Boolean,
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

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-admin-settings'

    const blankPostData = {
      title: '',
    }

    const postData = ref(JSON.parse(JSON.stringify(blankPostData)))
    const resetPostData = () => {
      postData.value = JSON.parse(JSON.stringify(blankPostData))
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetPostData)

    const onSubmit = () => {
      console.log('onSubmit')
      console.log(postData)
      store.dispatch(`${STORE_MODULE_NAME}/addSettingExtras`, postData.value)
        .then(response => {
          console.log(response)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Daten erfolgreich hinzugefügt',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })

          //  emit('refetch-data')
          emit('update:is-add-new-active', false)
          router.push({ name: 'admin-settings-extras', params: { id: response.data.id } })
        })
        .catch(error => {
          console.log(error)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler beim Hinzufügen von Daten',
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
    }
  },
}
</script>
