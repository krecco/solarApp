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
          Neue Solaranlage
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
          <h6>Solar Plant</h6>
          <hr>
          <!-- Title -->
          <validation-provider
            #default="validationContext"
            name="Title"
            rules="required"
          >
            <b-form-group
              label="Title"
              label-for="title"
            >
              <b-form-input
                id="title"
                v-model="postData.title"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Enter Title"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <br>
          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Add
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="button"
              variant="outline-secondary"
              @click="hide"
            >
              Cancel
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
import store from '@/store'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

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
    }
  },
  setup(props, { emit }) {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-generic'

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
      store.dispatch(`${STORE_MODULE_NAME}/addDemoItem`, postData.value)
        .then(response => {
          console.log(response)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Data added successfully',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })

          emit('refetch-data')
          emit('update:is-add-new-active', false)
        })
        .catch(error => {
          console.log(error)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Error adding data',
              text: 'error',
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
