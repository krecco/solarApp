<template>
  <div>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
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
        <br>
        <validation-observer ref="validatePropertyOwner">
          <b-form
            @submit.prevent="add"
          >
            <b-form-group
              label="Eingabe Liegenschaftseigentümer"
              label-for="propertyOwner"
            >
              <validation-provider
                #default="{ errors }"
                name="Liegenschaftseigentümer"
                rules="required"
              >
                <b-form-input
                  id="propertyOwner"
                  v-model="propertyOwner.person"
                  trim
                  placeholder="Liegenschaftseigentümer"
                  :disabled="isDisabled()"
                />
                <small class="text-warning">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
            <b-button
              variant="primary"
              class="mb-1 mb-sm-0 mr-0 mr-sm-1"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              @click="add"
              :disabled="isDisabled()"
            >
              Eigentümer hinzufügen
            </b-button>
            <b-button
              variant="flat-dark"
              class="mb-1 mb-sm-0 mr-0 mr-sm-0"
              :block="$store.getters['app/currentBreakPoint'] === 'xs'"
              style="float:right"
            >
              <a
                href="https://www.lexunited.com/services/grundbuchdatenbank/"
                target="blank"
                style="color:#5e5873"
              >
                lexunited/Grundbuchabfrage
              </a>
            </b-button>
          </b-form>
        </validation-observer>
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

import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import store from '@/store'

const STORE_MODULE_NAME = 'app-power-plant'

localize('de')
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
    ValidationProvider,
    ValidationObserver,
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
          store.commit('app-power-plant/updatePropertyOwnerFetchedAd')
          //  this.refetch()
        })
        .catch(error => {
          console.log(error)
        })
    },
    add() {
      this.$refs.validatePropertyOwner.validate().then(success => {
        if (success) {
          store.dispatch(`${STORE_MODULE_NAME}/addOwnerToProperty`, { solarPlantId: this.baseData.id, postData: this.propertyOwner })
            .then(() => {
              store.commit('app-power-plant/updatePropertyOwnerFetchedAd')
              //  this.refetch()
              this.propertyOwner.person = ''
              this.$refs.validatePropertyOwner.reset()

              console.log(this.$refs.validatePropertyOwner)
            })
            .catch(error => {
              console.log(error)
            })
        }
      })
    },
    /*
    refetch() {
      store.dispatch(`${STORE_MODULE_NAME}/getPropertyOwnerList`, { solarPlantId: this.baseData.id })
        .then(response => { this.propertyOwnerList = response.data.payload })
        .catch(error => {
          if (error.response.status === 404) {
            this.propertyOwnerList.value = undefined
          }
        })
    },
    */
    isDisabled() {
      return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
  },
}
</script>
