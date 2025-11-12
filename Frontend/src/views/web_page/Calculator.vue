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
        style="padding-top:40px;"
      >
        <b-row>
          <b-col
            cols="12"
            md="12"
            lg="12"
          >
            <b>Verbrauchsprofil</b><br>
            <b-form-radio-group
              id="btn-radios-1"
              v-model="selectedRadio"
              button-variant="outline-primary"
              :options="optionsRadio"
              buttons
              name="radios-btn-default"
            />
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:30px;"
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
              <b style="font-size:14px;">Warmwasserbereitung mit Strom</b>
            </b-form-checkbox>
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
            <hr>
            <h3>Vorgesehene Anlagegrosse</h3>
            <br>
            <b-progress
              :max="max"
              style="height:40px;"
            >
              <b-progress-bar
                :value="values[0]"
                class="progress-bar-success"
                variant="warning"
                label-html="0-15kWp"
              />
              <b-progress-bar
                variant="primary"
                :value="values[1]"
                label-html="15-25kWp"
              />
              <b-progress-bar
                variant="danger"
                :value="values[2]"
                label-html="25-50kWp"
              />
            </b-progress>
          </b-col>
        </b-row>
      </b-card-text>
      <b-card-text
        style="padding-top:50px; padding-bottom:20px;"
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
    <b-card>
      <app-collapse accordion>
        <app-collapse-item title="Übersicht">
          Ihr prognostizierter Eigentumsübergang<br>
          <b-table
            responsive="sm"
            :items="items"
            :fields="tableColumns"
          />
          <br><br>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            block
          >
            Auftragsabsicht bekanntgeben
          </b-button>
          <br><br>
        </app-collapse-item>
        <app-collapse-item title="Ich will andere Anzahlung">
          Cheesecake cotton candy bonbon muffin cupcake tiramisu croissant. Tootsie roll sweet candy bear claw chupa chups lollipop toffee. Macaroon donut liquorice powder candy carrot cake macaroon fruitcake. Cookie toffee lollipop cotton candy ice cream dragée soufflé. Cake tiramisu lollipop wafer pie soufflé dessert tart. Biscuit ice cream pie apple pie topping oat cake dessert. Soufflé icing caramels. Chocolate cake icing ice cream macaroon pie cheesecake liquorice apple pie.
        </app-collapse-item>
        <app-collapse-item title="Ich will Direkt kaufen">
          ihr Kommentar:<br>
          <b-form-textarea
            id="textarea-default"
            placeholder=""
            rows="4"
          />
          <br>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="outline-primary"
            block
          >
            Kommentar senden
          </b-button>
          <br><br>
        </app-collapse-item>
        <app-collapse-item title="Änderung an PV Projektierung">
          Cheesecake cotton candy bonbon muffin cupcake tiramisu croissant. Tootsie roll sweet candy bear claw chupa chups lollipop toffee. Macaroon donut liquorice powder candy carrot cake macaroon fruitcake. Cookie toffee lollipop cotton candy ice cream dragée soufflé. Cake tiramisu lollipop wafer pie soufflé dessert tart. Biscuit ice cream pie apple pie topping oat cake dessert. Soufflé icing caramels. Chocolate cake icing ice cream macaroon pie cheesecake liquorice apple pie.
        </app-collapse-item>
      </app-collapse>
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
  BFormRadioGroup,
  BProgressBar,
  BProgress,
  BTable,
  BFormTextarea,
} from 'bootstrap-vue'

import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItem from '@core/components/app-collapse/AppCollapseItem.vue'

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
    BFormRadioGroup,
    BProgressBar,
    BProgress,

    AppCollapse,
    AppCollapseItem,
    BTable,
    BFormTextarea,
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
      marksValue: [0, 10000, 20000, 30000, 40000],
      dir: 'ltr',
      selectedRadio: 2,
      optionsRadio: [
        { text: 'Nie zu Hause', value: 1 },
        { text: 'Wochentags teilweise zu Hause', value: 2 },
        { text: 'Immer zu Hause', value: 3 },
      ],
      values: [15, 10, 25],
      max: 50,
      items: [
        {
          title: 'Gesamtkosten PV-Anlage inkl. Ust. *** ', value: '11.885,00 EUR',
        },
        {
          title: 'Anzahlung', value: '3.000,00 EUR',
        },
        {
          title: 'Förderzuschuss', value: '1.850,00 EUR',
        },
        {
          title: 'Eigentumsübergang nach Jahren****', value: '12,5',
        },
      ],
      tableColumns: [
        { key: 'title', label: 'Titel', thStyle: { width: '80% !important' } },
        { key: 'value', label: '', thStyle: { width: '50px !important' } },
      ],
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
.collapse-title {
  font-weight: 800 !important;
  font-size: 18px !important;
}

</style>
