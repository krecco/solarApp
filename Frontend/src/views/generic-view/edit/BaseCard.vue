<template>
  <div>
    <b-card>
      <b-row
          class="match-height"
        >
          <b-col
            cols="12"
            md="12"
          >
            Basic info edit:
            <br/><br/>
            <b-form
              @submit.prevent="onSubmitBaseData"
            >
              <b-row>

                <b-col
                  cols="12"
                  md="6"
                >
                  <b-form-group
                    label="Title"
                    label-for="title"
                  >
                    <b-form-input
                      id="title"
                      v-model="baseData.title"
                      autofocus
                      trim
                      placeholder="Enter title"
                    />
                  </b-form-group>
                </b-col>
              </b-row>
              <b-button
                variant="primary"
                class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                :block="$store.getters['app/currentBreakPoint'] === 'xs'"
                type="submit"
              >
                Speichern
              </b-button>
            </b-form>
          </b-col>
        </b-row>
    </b-card>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BCard,
  BButton,
  BFormGroup,
  BFormInput,
  BForm,
} from 'bootstrap-vue'

import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { onUnmounted } from '@vue/composition-api'
import store from '@/store'
import storeModule from '../storeModule'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BButton,
    BFormGroup,
    BFormInput,
    BForm,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
    paramId: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const toast = useToast()

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-generic'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const onSubmitBaseData = () => {
      store.dispatch(`${STORE_MODULE_NAME}/editBaseData`, { baseData: props.baseData })
        .then(response => {
          console.log(response)
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

    return {
      onSubmitBaseData,
    }
  },
}
</script>
