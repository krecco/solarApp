<template>
  <b-card
    title="Aktivitäten"
    style="height:600px;"
  >
    <b-card-body
      style="padding:0px;"
    >
      <app-timeline
        style="overflow-y: auto; max-height: 500px; padding: 10px;"
      >

        <app-timeline-item
          v-for="activity in userActivityData"
          :key="activity.id"
          :variant="activity.notificationType"
          :title="activity.title"
          :subtitle="activity.content"
          :time="isToday(activity.t0)"
        />

      </app-timeline>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard,
  BCardBody,
} from 'bootstrap-vue'
import AppTimeline from '@core/components/app-timeline/AppTimeline.vue'
import AppTimelineItem from '@core/components/app-timeline/AppTimelineItem.vue'

export default {
  components: {
    BCard,
    AppTimeline,
    AppTimelineItem,
    BCardBody,
  },
  props: {
    userActivityData: {
      type: Array,
      required: true,
    },
  },
  methods: {
    isToday(date) {
      //  return this.$moment(date).this.$moment(this.$moment().clone().startOf('day'), 'd')
      //  return this.$moment(date).format('DD.MM. YYYY')
      return this.$moment(date).format('LLL')
    },
  },
}
</script>

<style>

</style>
