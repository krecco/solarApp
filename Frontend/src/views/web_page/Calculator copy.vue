<template>
  <div>
     <b-card title="">
      <b-img
        src="@/assets/images/logo/logo-solar-1.png"
        alt="Solar.Family Logo"
        style="height:25px;"
      />
      <br>
      <br>
      <h3>Berechnen Sie Ihre Photovoltaik - Anlage !</h3>
      <br>
      <br>
      <b-card-text>
        <b-row>
          <b-col
            cols="12"
            md="8"
            lg="10"
          >
            <b>Stromverbrauch pro Jahr im kW</b>
          </b-col>
          <b-col
            cols="12"
            md="4"
            lg="2"
            class="text-right"
          >
            <b-form-input v-model="value" />
          </b-col>
        </b-row>
        <div
          style="padding-right:10px; padding-top:10px;"
        >
          <vue-slider
            v-model="value"
            :min="0"
            :max="40000"
            :marks="marksValue"
            :interval="50"
            :adsorb="false"
            :direction="direction"
            class="mb-3 vue-slider-primary"
          />
        </div>
      </b-card-text>
      <b-card-text
        style="padding-top:80px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="12"
          >
            <b>Verbrauchsprofil</b>
          </b-col>
        </b-row>
        <div
          style="padding-right:10px; padding-top:10px;"
        >
          <vue-slider
            v-model="profil"
            :min="0"
            :max="100"
            :interval="1"
            :adsorb="false"
            :tooltip="'none'"
            :direction="direction"
            class="mb-3 vue-slider-primary"
          />
        </div>
        <b-row
          style="margin-top:-40px;"
        >
          <b-col
            cols="12"
            md="4"
            lg="4"
          >
            Nie zu<br>Hause
          </b-col>
          <b-col
            cols="12"
            md="4"
            lg="4"
            class="text-center"
          >
            Wochentags teilweise<br>zu Hause
          </b-col>
          <b-col
            cols="12"
            md="4"
            lg="4"
            class="text-right"
          >
            Immer<br>zu Hause
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:40px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="12"
          >
            <b-form-checkbox
              checked="true"
              name="check-button"
              switch
              inline
            >
              <b style="font-size:15px;">Warmwasserbereitung mit Strom"</b>
            </b-form-checkbox>
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:60px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="12"
          >
            <b>Vorgesehene Anlagegrosse</b>
          </b-col>
        </b-row>
        <div
          style="padding-right:10px; padding-top:10px;"
        >
          <vue-slider
            v-model="plantProduction"
            :min="0"
            :max="50"
            :marks="marksProduction"
            :interval="1"
            :adsorb="false"
            :direction="direction"
            class="mb-3 vue-slider-primary"
          />
        </div>
        <b-row
          style="margin-top:0px;"
        >
          <b-col
            cols="12"
            md="4"
            lg="4"
            class="text-center"
          >
            &nbsp;
          </b-col>
          <b-col
            cols="12"
            md="4"
            lg="4"
            class="text-center"
            style="background-color:rgb(95,122,185); color:white;"
          >
            empfohlene Bereich
          </b-col>
          <b-col
            cols="12"
            md="4"
            lg="4"
            class="text-center"
          >
           &nbsp;
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:80px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="6"
            style="font-size:20px;"
          >
            <b>Ergebnis...kann nicht lesen:</b><b> X %</b>
          </b-col>
          <b-col
            cols="12"
            md="12"
            lg="6"
            style="font-size:20px;"
          >
            &nbsp;
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:20px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="12"
            class="text-right"
            style="font-size:20px;"
          >
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="outline-primary"
            >
              Zu den Details
            </b-button>
          </b-col>
        </b-row>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import {
  BCard,
  BCardText,
  BFormInput,
  BCol,
  BRow,
  BFormCheckbox,
  BButton,
  BImg,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'
import VueSlider from 'vue-slider-component'
import '@core/scss/vue/libs/vue-slider.scss'
import store from '@/store/index'

export default {
  components: {
    BImg,
    BButton,
    BCard,
    BCardText,
    VueSlider,
    BFormInput,
    BCol,
    BRow,
    BFormCheckbox,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      value: 10000,
      profil: 40,
      plantProduction: 20,
      marksProduction: [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50],
      marksValue: [0, 5000, 10000, 20000, 30000, 40000],
      dir: 'ltr',
    }
  },
  computed: {
    direction() {
      if (store.state.appConfig.isRTL) {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.dir = 'rtl'
        return this.dir
      }
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      this.dir = 'ltr'
      return this.dir
    },
  },
}
</script>

<style>

</style>
