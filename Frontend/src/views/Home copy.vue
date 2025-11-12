<template>
  <div>
    <b-row class="match-height">
      <b-col
        lg="4"
        md="12"
      >
        <!-- bg-variant="secondary" -->
        <b-card
          text-variant="center"
          class="card card-congratulations"
        >
          <!--
            <b-avatar
              variant="primary"
              size="70"
              class="shadow mb-2"
            >
              <b-img
                src="@/assets/images/logo/logosmall.png"
                alt="Solar.Family Logo"
                style="height:70px;"
              />
            </b-avatar>
            <h1 class="mb-1 mt-50 text-white">
              solar.family
            </h1>
          -->
          <br>
          <br>
          <br>
          <b-img
            src="@/assets/images/logo/logo-solar-1.png"
            alt="Solar.Family Logo"
            style="height:30px;"
          />
          <br><br>
          <b-card-text class="m-auto w-75">
            Helping you build greener future!
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="BoxIcon"
          color="info"
          statistic="512"
          statistic-title="Solaranlagen"
          :chart-data="plants.series"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="ZapIcon"
          color="success"
          statistic="128 kW"
          statistic-title="Power Production"
          :chart-data="power.series"
        />
      </b-col>
    </b-row>
    <b-row class="match-height">
      <b-col
        lg="4"
        md="12"
      >
        <card-analytic-goal-overview />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="UsersIcon"
          color="danger"
          statistic="1792"
          statistic-title="Users"
          :chart-data="users.series"
        />
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <statistic-card-with-area-chart
          icon="MailIcon"
          color="warning"
          statistic="17"
          statistic-title="New Messages"
          :chart-data="messages.series"
        />
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BImg,
  BRow,
  BCol,
  BCard,
  BCardText,
  //  BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import StatisticCardWithAreaChart from '@core/components/statistics-cards/StatisticCardWithAreaChart.vue'
import CardAnalyticGoalOverview from './cards/CardAnalyticGoalOverview.vue'

export default {
  components: {
    StatisticCardWithAreaChart,
    CardAnalyticGoalOverview,
    BRow,
    BCol,
    BCard,
    //  BAvatar,
    BCardText,
    BImg,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      plants: {
        series: [
          {
            name: 'Units',
            data: [10, 15, 75, 89, 120, 234, 512],
          },
        ],
      },
      power: {
        series: [
          {
            name: 'Power',
            data: [10, 50, 220, 390, 890, 1276, 2111],
          },
        ],
      },
      users: {
        series: [
          {
            name: 'Users',
            data: [10, 15, 8, 15, 7, 12, 8],
          },
        ],
      },
      messages: {
        series: [
          {
            name: 'Messages',
            data: [22, 15, 4, 15, 33, 12, 21],
          },
        ],
      },
    }
  },
}
</script>

<style>

</style>
