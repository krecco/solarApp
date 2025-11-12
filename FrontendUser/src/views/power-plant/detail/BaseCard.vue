<template>
  <div>
    <b-row
      class="match-height"
    >
      <b-col
        cols="12"
        lg="4"
        md="12"
      >
        <b-card>
          <h3>{{ baseData.title }}</h3>
          <br>
          <b-row
            class="match-height"
          >
            <b-col
              cols="12"
              md="12"
            >
              <b-row>
                <b-col>
                  Photovoltaik-Anlagen-Engpassleistung
                  <h2>{{ baseData.nominalPower | numFormat('0.00') }}  kWp</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Preis Photovoltaik-Anlage inkl. Ust.
                  <h2>{{ baseData.unitPrice | numFormat('0,0.00') }} EUR</h2>
                </b-col>
              </b-row>
              <br>
            </b-col>
            <b-col
              cols="12"
              md="12"
            >
              <b-row>
                <b-col>
                  Prognose Stromertrag
                  <h2>{{ baseData.powerProductionForecast | numFormat('0,0') }} kWh/a</h2>
                </b-col>
              </b-row>
              <br>
              <b-row>
                <b-col>
                  Prognose Eigenverbrauch
                  <h2>{{ baseData.powerConsumptionForecast | numFormat('0,0') }} kWh/a</h2>
                </b-col>
              </b-row>
              <!--
              <br>
              <br>
              <b-row>
                <b-col>
                  Tarif
                  <h2>{{ plantTariff }}</h2>
                </b-col>
              </b-row>
              -->
            </b-col>
          </b-row>
        </b-card>
      </b-col>
      <b-col
        cols="12"
        lg="8"
        md="12"
      >
        <b-card>
          <b-row>
            <b-col
              cols="12"
              md="12"
            >
              <l-map
                v-if="baseData.lat != null"
                :zoom="zoom"
                :center="mapCenter"
              >
                <l-tile-layer :url="url" />
                <l-marker :lat-lng="mapMarker">
                  <l-popup>{{ baseData.location }}</l-popup>
                </l-marker>
              </l-map>
              <div
                v-if="baseData.lat == null"
              >
                Map not showing --- Coordinates not set
              </div>
            </b-col>
          </b-row>
          <!--
          <b-row>
            <b-col
              cols="12"
              md="12"
            >
              <br>
              <br>
              <b-button
                :to="{ name: 'power-plant-edit', params: { id: baseData.id } }"
                variant="primary"
                style="width:100px"
              >                    <h2>{{ plantTariff }}</h2>
                  </b-col>
                </b-row>
            </b-col>
          </b-row>
          -->
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
} from 'bootstrap-vue'

import { ref } from '@vue/composition-api'

import {
  LMap, LTileLayer, LMarker, LPopup,
} from 'vue2-leaflet'
import 'leaflet/dist/leaflet.css'
import { Icon } from 'leaflet'
import store from '@/store'

/* eslint-disable global-require */
// eslint-disable-next-line no-underscore-dangle
delete Icon.Default.prototype._getIconUrl
Icon.Default.mergeOptions({
  iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
  iconUrl: require('leaflet/dist/images/marker-icon.png'),
  shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
})
/* eslint-enable global-require */

export default {
  components: {
    BRow,
    BCol,
    BCard,

    LMap,
    LTileLayer,
    LMarker,
    LPopup,
  },
  props: {
    baseData: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  data() {
    return {
      url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      zoom: 15,
    }
  },
  setup(props) {
    const STORE_MODULE_NAME = 'app-power-plant'
    const mapCenter = ref([0, 0])
    const mapMarker = ref([0, 0, { draggable: 'false' }])

    const plantTariff = ref({})

    if (props.baseData.lat !== null) {
      mapCenter.value = [props.baseData.lat, props.baseData.lon]
      mapMarker.value = [props.baseData.lat, props.baseData.lon, { draggable: 'false' }]
    }

    store.dispatch(`${STORE_MODULE_NAME}/fetchTarriff`, { id: props.baseData.tariff })
      .then(response => { plantTariff.value = response.data })

    return {
      mapCenter,
      mapMarker,
      plantTariff,
    }
  },
}
</script>

<style lang="scss">
.vue2leaflet-map{
  &.leaflet-container{
    height: 330px;
  }
}
</style>
