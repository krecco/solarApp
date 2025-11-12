<template>
  <b-sidebar
    id="add-new-user-sidebar"
    :visible="isAddNewUserSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-user-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Neukunde
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
          <h6>Persönliche Daten</h6>
          <hr>
          <!--
          <b-form-group
            label="Anrede"
            label-for="gender"
          >
            <b-form-select
              v-model="userData.gender"
              :options="gender_options"
            />
          </b-form-group>
          -->

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

          <b-form-group
            label="Titel vorangestellt"
            label-for="titlePrefix"
          >
            <b-form-input
              id="titlePrefix"
              v-model="userData.titlePrefix"
              autofocus
              trim
              placeholder=""
            />
          </b-form-group>
          <!-- First Name -->
          <validation-provider
            #default="validationContext"
            name="Vorname"
            rules="required"
          >
            <b-form-group
              label="Vorname"
              label-for="firstName"
            >
              <b-form-input
                id="firstName"
                v-model="userData.firstName"
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

          <!-- Last Name -->
          <validation-provider
            #default="validationContext"
            name="Nachname"
            rules="required"
          >
            <b-form-group
              label="Nachname"
              label-for="lastName"
            >
              <b-form-input
                id="lastName"
                v-model="userData.lastName"
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

          <b-form-group
            label="Title nachgestellt"
            label-for="titleSuffix"
          >
            <b-form-input
              id="titleSuffix"
              v-model="userData.titleSuffix"
              autofocus
              trim
              placeholder=""
            />
          </b-form-group>

          <br>
          <h6>Kontakt</h6>
          <hr>
          <!-- Email -->
          <validation-provider
            #default="validationContext"
            name="Email"
            rules="required|email"
          >
            <b-form-group
              label="Email"
              label-for="email"
            >
              <b-form-input
                id="email"
                v-model="userData.email"
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
          <b-form-group
            label="Telefonnummer"
            label-for="phoneNr"
          >
            <b-form-input
              id="phoneNr"
              v-model="userData.phoneNr"
              autofocus
              trim
              placeholder=""
            />
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BButton, BFormSelect,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref } from '@vue/composition-api'
import { required, alphaNum, email } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import store from '@/store'

export default {
  components: {
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BButton,
    BFormSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewUserSidebarActive',
    event: 'update:is-add-new-user-sidebar-active',
  },
  props: {
    isAddNewUserSidebarActive: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      required,
      alphaNum,
      email,
      gender_options: [
        { value: null, text: 'Bitte auswählen' },
        { value: '1', text: 'Herr' },
        { value: '2', text: 'Frau' },
        { value: '3', text: 'Divers' },
        { value: '11', text: 'Familie' },
        { value: '12', text: 'Verein' },
        { value: '13', text: 'Gemeinde' },
      ],
    }
  },
  setup(props, { emit }) {
    const toast = useToast()

    const blankUserData = {
      firstName: '',
      lastName: '',
      email: '',
      phoneNr: '',
      gender: '',
      titlePrefix: '',
      titleSuffix: '',
    }

    const userData = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      userData.value = JSON.parse(JSON.stringify(blankUserData))
    }

    const onSubmit = () => {
      store.dispatch('app-user/addUser', userData.value)
        .then(response => {
          if (response.data.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'New user added successfully',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            emit('refetch-data')
            emit('update:is-add-new-user-sidebar-active', false)
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Error adding new user -- user exists',
                text: response.data.payload.message,
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }
        })
        .catch(() => {
          this.$toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Reuest Failed',
              icon: 'CoffeeIcon',
              variant: 'warning',
              text: 'Error connecting to server',
            },
          })
        })
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      userData,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';

#add-new-user-sidebar {
  .vs__dropdown-menu {
    max-height: 200px !important;
  }
}
</style>
