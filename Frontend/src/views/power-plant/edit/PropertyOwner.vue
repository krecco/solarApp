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
            icon="ColumnsIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            Eigentümer der Liegenschaft
          </h4>
        </div>
        <br>

        <b-list-group>
          <b-list-group-item
            v-for="item in propertyOwnerList"
            :key="item.id"
            class="d-flex justify-content-between align-items-center"
          >
            <span>{{ item.person }}</span>
            <b-badge
              variant="secondary"
              pill
              class="badge-round"
              style="cursor:pointer"
              @click="remove(item.id)"
            >
              x
            </b-badge>
          </b-list-group-item>
        </b-list-group>
        <br><br>
        <b-form
          @submit.prevent="add"
        >
          <b-form-group
            label="Eingabe Liegenschaftseigentümer"
            label-for="propertyOwner"
          >
            <b-form-input
              id="propertyOwner"
              v-model="propertyOwner.person"
              trim
              placeholder=""
              :disabled="isDisabled()"
            />
          </b-form-group>
          <b-button
            variant="primary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            :block="$store.getters['app/currentBreakPoint'] === 'xs'"
            type="submit"
            :disabled="isDisabled()"
          >
            Eigentümer hinzufügen
          </b-button>
        </b-form>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BListGroup,
  BListGroupItem,
  BBadge,
  BForm,
  BFormGroup,
  BFormInput,
  BButton,
} from 'bootstrap-vue'
import store from '@/store'

const STORE_MODULE_NAME = 'app-power-plant'

export default {
  components: {
    BRow,
    BCol,
    BListGroup,
    BListGroupItem,
    BBadge,
    BForm,
    BFormGroup,
    BFormInput,
    BButton,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    propertyOwnerList: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  data() {
    return {
      propertyOwner: {
        person: '',
      },
    }
  },
  methods: {
    remove(id) {
      store.dispatch(`${STORE_MODULE_NAME}/removeOwnerFromProperty`, { ownerId: id })
        .then(() => {
          this.refetch()
        })
        .catch(error => {
          console.log(error)
        })
    },
    add() {
      store.dispatch(`${STORE_MODULE_NAME}/addOwnerToProperty`, { solarPlantId: this.baseData.id, postData: this.propertyOwner })
        .then(() => {
          this.refetch()
          this.propertyOwner.person = ''
        })
        .catch(error => {
          console.log(error)
        })
    },
    refetch() {
      store.dispatch(`${STORE_MODULE_NAME}/getPropertyOwnerList`, { solarPlantId: this.baseData.id })
        .then(response => { this.propertyOwnerList = response.data.payload })
        .catch(error => {
          if (error.response.status === 404) {
            this.propertyOwnerList.value = undefined
          }
        })
    },
    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
  },
}
</script>
