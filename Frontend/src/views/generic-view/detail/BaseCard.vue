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
            Basic info content:
            //print et basic data here
            <br/>
            {{ baseData.title }}
            <br><br>
            <b-button
              :to="{ name: 'generic-edit', params: { id: baseData.id } }"
              variant="primary"
              style="width:100px"
            >
              Edit
            </b-button>
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
} from 'bootstrap-vue'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BButton,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
}
</script>
