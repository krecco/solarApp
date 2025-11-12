<template>
  <div>
    <!-- Add new item component -->
    <add-new-investment
      :is-add-new-investment-active.sync="isAddNewInvestmentActive"
      :user-data="userData"
    />
    <b-card
      no-body
      class="border-info text-center"
    >
      <b-card-body>
        <h5 class="text-center">
          Neue Investition
        </h5>
        <b-button
          variant="gradient-info"
          class="btn-icon rounded-circle"
          style="height:75px; width:75px;"
          @click="isAddNewInvestmentActive = true"
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
import AddNewInvestment from './AddNewInvestment.vue'

export default {
  directives: {
    Ripple,
  },
  components: {
    AddNewInvestment,

    BCard,
    BCardBody,
    BButton,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const isAddNewInvestmentActive = ref(false)

    return {
      isAddNewInvestmentActive,
    }
  },
}
</script>
