<template>
  <div>
    <h1
      style="font-weight: bold;"
    >
      Willkommen {{ userFirstName }} {{ userLastName }}
    </h1>
    <br>
    <br>
    <!--<h3>nächster Schritte</h3>-->
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <b-card
          style="background-color:#f8f8f8"
        >
          <b-card-body>
            <b-row
              align-v="center"
            >
              <b-col
                cols="12"
                xl="1"
                lg="1"
                md="1"
                sm="1"
              >
                <b-avatar
                  variant="light-primary"
                  size="70px"
                >
                  <feather-icon
                    icon="UserIcon"
                    size="40px;"
                  />
                </b-avatar>
              </b-col>
              <b-col
                cols="12"
                xl="7"
                lg="6"
                md="6"
                sm="4"
              >
                <h2>Kundendaten</h2>
                Hier finden Sie Ihre persönlichen Daten.
              </b-col>
              <b-col
                cols="12"
                xl="2"
                lg="3"
                md="3"
                sm="4"
                class="text-center"
              >
                &nbsp;
              </b-col>
              <b-col
                cols="12"
                lx="2"
                lg="2"
                md="2"
                sm="2"
              >
                <b-button
                  v-if="userStatus.status == 200"
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="outline-secondary"
                  pill
                  block
                  :to="{ name: 'user-detail-edit', params: { id: authUser.id } }"
                >
                  Erledigt
                </b-button>
                <b-button
                  v-if="userStatus.status != 200"
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="primary"
                  pill
                  block
                  :to="{ name: 'user-detail-edit', params: { id: authUser.id } }"
                >
                  Weiter
                </b-button>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>

      <b-col
        cols="12"
        md="12"
      >
        <b-card
          v-for="item in solarPlantData"
          :key="item.id"
        >
          <b-card-body>
            <b-row
              align-v="center"
            >
              <b-col
                cols="12"
                xl="1"
                lg="1"
                md="1"
                sm="1"
              >
                <b-avatar
                  variant="light-primary"
                  size="70px"
                >
                  <feather-icon
                    icon="SunIcon"
                    size="40px;"
                  />
                </b-avatar>
              </b-col>
              <b-col
                cols="12"
                xl="9"
                lg="8"
              >
                <h2>{{ item.title }}</h2>
                <!--
                <b>Begehungstermin: {{ item.inspectionCheckDate | moment("DD.MM.YYYY hh:mm") }}</b>
                -->
              </b-col>
              <b-col
                cols="12"
                lx="2"
                lg="2"
              >
                <b-button
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="primary"
                  pill
                  block
                  :to="{ name: 'power-plant-detail', params: { id: item.id } }"
                >
                  Weiter
                </b-button>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import { ref, onUnmounted } from '@vue/composition-api'
import {
  BRow,
  BCol,
  BCard,
  BCardBody,
  BButton,
  //  BOverlay,
  BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import userStoreModule from '../userStoreModule'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardBody,
    BButton,
    //  BOverlay,
    BAvatar,
  },
  directives: {
    Ripple,
  },
  setup() {
    const solarPlantData = ref([])
    const userStatus = ref({})
    const filesStatus = ref({})
    const userFirstName = ref('')
    const userLastName = ref('')
    const powerBillStatus = ref(false)

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const authUser = JSON.parse(localStorage.getItem('userData'))
    userFirstName.value = authUser.firstName
    userLastName.value = authUser.lastName

    /* !!!! TODO  REFLECTION WHEN KNOW WHAT IS NEEDED !!!! */
    store.dispatch('app-user/getPowerPlantList', { userId: authUser.uid })
      .then(response => {
        if (response.data.status === 200) {
          solarPlantData.value = response.data.payload
        }
      })
      .catch(error => {
        if (error.response.status === 404) {
          solarPlantData.value = undefined
        }
      })

    store.dispatch('app-user/getUserStatus', { userId: authUser.uid })
      .then(response => { userStatus.value = response.data })
      .catch(error => {
        if (error.response.status === 404) {
          userStatus.value = undefined
        }
      })

    store.dispatch('app-user/getUserFilesStatus', { userId: authUser.uid })
      .then(response => { filesStatus.value = response.data.payload.message })
      .catch(error => {
        if (error.response.status === 404) {
          filesStatus.value = undefined
        }
      })

    //  powerBillStatus.value = false

    return {
      solarPlantData,
      userStatus,
      filesStatus,
      authUser,
      userFirstName,
      userLastName,
      powerBillStatus,
    }
  },
}
</script>
