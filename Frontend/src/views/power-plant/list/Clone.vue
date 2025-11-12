<template>
  <b-sidebar
    id="clone-solar-power-sidebar"
    :visible="isCloneSolarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @change="(val) => $emit('update:is-clone-solar-active', val)"
    @hidden="resetForm"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          PV-Anlage Kopieren
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
          <h6>PV-Anlage:&nbsp;&nbsp;{{ solarPlant.title }} {{ solarPlant.nominalPower | numFormat('0.00') }}  kWp</h6>
          <hr>
          <br>
          <h6>Kundenauswahl:</h6>
          <vue-autosuggest
            ref="autocomplete"
            v-model="query"
            :suggestions="suggestions"
            :input-props="inputProps"
            :section-configs="sectionConfigs"
            :render-suggestion="renderSuggestion"
            :get-suggestion-value="getSuggestionValue"
            @input="fetchResults"
          />
          <!-- Form Actions -->
          <br>
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Kopieren
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
} from 'bootstrap-vue'
import { VueAutosuggest } from 'vue-autosuggest'

import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import { ValidationObserver } from 'vee-validate'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store'

export default {
  components: {
    BSidebar,
    BButton,
    BForm,

    ValidationObserver,
    VueAutosuggest,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isCloneSolarActive',
    event: 'update:is-clone-solar-active',
  },
  props: {
    isCloneSolarActive: {
      type: Boolean,
      required: true,
    },
    solarPlant: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      required,
      alphaNum,
      query: '',
      results: [],
      timeout: null,
      debounceMilliseconds: 250,
      inputProps: {
        id: 'autosuggest__input_ajax',
        placeholder: 'Nachnamen des Benutzers suchen',
        class: 'form-control',
        name: 'ajax',
      },
      suggestions: [],
    }
  },
  methods: {
    fetchResults() {
      const { query } = this

      clearTimeout(this.timeout)
      this.timeout = setTimeout(() => {
        store
          .dispatch('app-power-plant/fetchUsers', {
            q: query,
            perPage: 50,
            page: 1,
            sortBy: 'lastName',
            sortDesc: false,
            customerType: -1,
          })
          .then(response => {
            console.log(response)
            //  renderSuggestion(response.data.payload)

            this.suggestions = []
            this.selected = null

            this.suggestions.push({ name: 'users', data: response.data.payload })
          })
          .catch(() => {
            console.log('error')
          })
      }, this.debounceMilliseconds)
    },
    renderSuggestion(suggestion) {
      return (
        <div class="d-flex">
          <div>
            {suggestion.item.lastName} {suggestion.item.firstName}
          </div>
          <div>
            <span>({suggestion.item.email})</span>
          </div>
        </div>
      )
    },
    getSuggestionValue(suggestion) {
      const { item } = suggestion
      return `${item.lastName} ${item.firstName} (${item.email})`
    },
  },
  setup(props, { emit }) {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'

    const blankPostData = {
      title: '',
    }

    const selected = ref(null)

    const sectionConfigs = {
      users: {
        limit: 50,
        label: '',
        onSelected: s => {
          selected.value = s.item
        },
      },
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
      console.log(props)
      console.log(selected)

      store.dispatch(`${STORE_MODULE_NAME}/cloneItem`, { plantId: props.solarPlant.id, userId: selected.value.id })
        .then(response => {
          console.log(response)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Data cloned successfully',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })

          emit('refetch-data')
          emit('update:is-clone-solar-active', false)
        })
        .catch(error => {
          console.log(error)

          toast({
            component: ToastificationContent,
            props: {
              title: 'Error cloning data',
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
      selected,
      sectionConfigs,

      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-autosuggest.scss';
</style>
