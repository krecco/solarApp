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
            Widget two: Optional placeholder for widget //just delete it if not needed -- no dependency
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
} from 'bootstrap-vue'

export default {
  components: {
    BRow,
    BCol,
    BCard,
  },
}
</script>
