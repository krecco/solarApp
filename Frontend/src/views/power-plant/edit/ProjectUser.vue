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
            Kunde zuordnen
          </h4>
        </div>
        <br>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        ausgewählter Kunde: <b>{{ pUser.lastName }} {{ pUser.firstName }} ({{ pUser.email }})</b>
        <br><br>
        Kundenauswahl:
        <vue-autosuggest
          ref="autocomplete"
          v-model="query"
          :suggestions="suggestions"
          :input-props="inputProps"
          :section-configs="sectionConfigs"
          :render-suggestion="renderSuggestion"
          :get-suggestion-value="getSuggestionValue"
          @input="fetchResults"
          :disabled="isDisabled()"
        />
        <br><br>
        <b-button
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          type="button"
          @click="mapUserToProject"
          :disabled="isDisabled()"
        >
          Speichern
        </b-button>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BButton,
  BRow,
  BCol,
} from 'bootstrap-vue'
import { VueAutosuggest } from 'vue-autosuggest'
import { onUnmounted } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store'
import storeModule from '../storeModule'

export default {
  components: {
    VueAutosuggest,
    BButton,
    BRow,
    BCol,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    pUser: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  data() {
    return {
      query: '',
      results: [],
      timeout: null,
      selected: null,
      debounceMilliseconds: 250,
      inputProps: {
        id: 'autosuggest__input_ajax',
        placeholder: 'Search User Lastname',
        class: 'form-control',
        name: 'ajax',
      },
      suggestions: [],
      sectionConfigs: {
        users: {
          limit: 10,
          label: '',
          onSelected: selected => {
            this.selected = selected.item
          },
        },
      },
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
    mapUserToProject() {
      store
        .dispatch('app-power-plant/mapUserToProject', {
          userId: this.selected.id,
          solarPlantId: this.$props.baseData.id,
        })
        .then(response => {
          console.log(response)
          if (response.data.payload.status === 200) {
            this.showOkNotification()
            this.$props.pUser = this.selected
          }
        })
        .catch(() => {
          console.log('error')
        })
    },
    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
  },
  setup() {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const showOkNotification = () => {
      toast({
        component: ToastificationContent,
        props: {
          title: 'Updated successfully',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    }

    return {
      showOkNotification,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-autosuggest.scss';
</style>
