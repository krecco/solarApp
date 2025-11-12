<template>
  <div>
    <b-row
      class="match-height"
      style="padding-top:0px;"
    >
      <b-col
        v-for="item in solarPlantData"
        :key="item.id"
        lg="6"
        md="12"
      >
        <b-card
          class="mb-3"
        >
          <b-card-title>
            {{ item.title }}
            <br>
            <small class="text-muted">{{ item.t0 | moment("DD.MM. YYYY") }}</small>
          </b-card-title>
          <b-media
            no-body
            style="padding-top:10px;"
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-primary"
                size="34"
              >
                <feather-icon
                  icon="ZapIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ item.nominalPower | numFormat('0.00') }} kW
              </h3>
            </b-media-body>
          </b-media>
          <b-media
            no-body
            style="padding-top:10px;"
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-primary"
                size="34"
              >
                <feather-icon
                  icon="DollarSignIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h6
                class="mb-0"
              >
                {{ item.unitPrice | numFormat('0,0.00') }}
              </h6>
              <small>EUR</small>
            </b-media-body>
          </b-media>
          <br>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            block
            variant="outline-primary"
            :to="{ name: 'power-plant-detail', params: { id: item.id, preventAdminUpdate: true }}"
          >
            Mehr
          </b-button>
        </b-card>
      </b-col>

      <b-col
        v-for="item in investmentData"
        :key="item.id"
        lg="6"
        md="12"
      >
        <b-card
          class="mb-3"
        >
          <b-card-title>
            Investition Title
            <br>
            <small class="text-muted">{{ item.t0 | moment("DD.MM. YYYY") }}</small>
            <small class="text-muted"> / {{ item.duration }} months</small>
          </b-card-title>
          <b-media
            no-body
            style="padding-top:10px;"
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-info"
                size="34"
              >
                <feather-icon
                  icon="DollarSignIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ item.amount | numFormat('0,0') }} EUR
              </h3>
            </b-media-body>
          </b-media>
          <b-media
            no-body
            style="padding-top:10px;"
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-info"
                size="34"
              >
                <feather-icon
                  icon="PercentIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ item.interestRate | numFormat('0,0.00') }}
              </h3>
            </b-media-body>
          </b-media>
          <br>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            block
            variant="outline-info"
            :to="{ name: 'investment-detail', params: { id: item.id }}"
          >
            Mehr
          </b-button>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>
<script>
import {
  BRow,
  BCol,
  BCard,
  BCardTitle,
  BMedia,
  BMediaAside,
  BMediaBody,
  BAvatar,
  BButton,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardTitle,
    BMedia,
    BMediaAside,
    BMediaBody,
    BAvatar,
    BButton,
  },
  directives: {
    Ripple,
  },
  props: {
    solarPlantData: {
      type: Array,
      required: true,
    },
    investmentData: {
      type: Array,
      required: true,
    },
  },
}
</script>
