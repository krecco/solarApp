<template>
  <div>
    <!-- Add new item component -->
    <add-new-solar-plant
      :is-add-new-solar-plant-active.sync="isAddNewSolarPlantActive"
      :user-data="userData"
      :plant-title="plantTitle"
    />
    <b-card
      no-body
      class="border-primary text-center"
    >
      <b-card-body>
        <h5 class="text-center">
          Neue PV-Anlage
        </h5>
        <b-button
          variant="gradient-primary"
          class="btn-icon rounded-circle"
          style="height:75px; width:75px;"
          @click="isAddNewSolarPlantActive = true"
        >
          <span style="font-size:40px;">+</span>
        </b-button>
      </b-card-body>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BCardBody, BButton,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import AddNewSolarPlant from './AddNewSolarPlant.vue'

export default {
  directives: {
    Ripple,
  },
  components: {
    AddNewSolarPlant,

    BCard,
    BCardBody,
    BButton,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
    plantTitle: {
      type: String,
      required: true,
    },
  },
  setup() {
    const isAddNewSolarPlantActive = ref(false)

    return {
      isAddNewSolarPlantActive,
    }
  },
}
</script>
