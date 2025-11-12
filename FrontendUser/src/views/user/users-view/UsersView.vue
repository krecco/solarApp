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
                lg="1"
                md="12"
                style="min-width:100px;"
              >
                <div
                  :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
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
                </div>
              </b-col>
              <b-col
                :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
              >
                <h2>Kundendaten</h2>
                Hier finden Sie Ihre persönlichen Daten.
              </b-col>
              <b-col
                lg="2"
                md="12"
              >
                <b-button
                  v-if="userStatus.status == 200"
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="outline-secondary"
                  pill
                  block
                  :to="{ name: 'user-detail-edit', params: { id: authUser.id } }"
                  :style="[isMobile === true ? {'margin-top':'20px'} : {'margin-top':'0px'}]"
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
                  :style="[isMobile === true ? {'margin-top':'20px'} : {'margin-top':'0px'}]"
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
        <!-- solar plant -->
        <b-card
          v-for="item in solarPlantData"
          :key="item.id"
        >
          <b-card-body>
            <b-row
              align-v="center"
            >
              <b-col
                lg="1"
                md="12"
                style="min-width:100px;"
              >
                <div
                  :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
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
                </div>
              </b-col>
              <b-col
                :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
              >
                <h2>{{ item.title }}</h2>
                <b>Engpassleistung: {{ item.nominalPower | numFormat('0.00') }} kW</b>
              </b-col>
              <b-col
                lg="2"
                md="12"
              >
                <b-button
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="primary"
                  pill
                  block
                  :to="{ name: 'power-plant-detail', params: { id: item.id } }"
                  :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
                >
                  Weiter
                </b-button>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
        <!-- investment -->
        <b-card
          v-for="item in investmentData"
          :key="item.id"
        >
          <b-card-body>
            <b-row
              align-v="center"
            >
              <b-col
                lg="1"
                md="12"
                style="min-width:100px;"
              >
                <div
                  :style="[isMobile === true ? {'padding-bottom':'20px', 'text-align':'center'} : {'padding-bottom':'0px', 'text-align':'left'}]"
                >
                  <b-avatar
                    variant="light-info"
                    size="70px"
                  >
                    <feather-icon
                      icon="TrendingUpIcon"
                      size="40px;"
                    />
                  </b-avatar>
                </div>
              </b-col>
              <b-col
                :style="[isMobile === true ? {'text-align':'center'} : {'text-align':'left'}]"
              >
                <h2>Investition: {{ item.amount | numFormat('0,00.00') }} EUR</h2>
                <b>Dauer: {{ item.duration }} Jahren</b>
              </b-col>
              <b-col
                lg="2"
                md="12"
              >
                <b-button
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="info"
                  pill
                  block
                  :to="{ name: 'investment-detail', params: { id: item.id } }"
                  :style="[isMobile === true ? {'margin-top':'40px'} : {'margin-top':'0px'}]"
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
import {
  ref,
  onUnmounted,
  watch,
  computed,
} from '@vue/composition-api'
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
    const investmentData = ref([])
    const userStatus = ref({})
    const filesStatus = ref({})
    const userFirstName = ref('')
    const userLastName = ref('')
    const powerBillStatus = ref(false)

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    const isMobile = ref(true)
    if (store.getters['app/currentBreakPoint'] === 'xl' || store.getters['app/currentBreakPoint'] === 'lg') {
      isMobile.value = false
    }

    const currentBreakPoint = computed(() => store.getters['app/currentBreakPoint'])
    watch(currentBreakPoint, val => {
      if (val === 'xl' || val === 'lg') {
        isMobile.value = false
      } else {
        isMobile.value = true
      }
    })

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
          const res = []
          response.data.payload.forEach(el => {
            if (el.cloneSource === null) {
              res.push(el)
            } else if (el.cloneSource !== null && el.calculationSentToCustomer === true) {
              res.push(el)
            }
          })

          solarPlantData.value = res
        }
      })
      .catch(error => {
        if (error.response.status === 404) {
          solarPlantData.value = undefined
        }
      })

    store.dispatch('app-user/getInvestmentList', { userId: authUser.uid })
      .then(response => {
        if (response.data.status === 200) {
          investmentData.value = response.data.payload
        }
      })
      .catch(error => {
        if (error.response.status === 404) {
          investmentData.value = undefined
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
      investmentData,
      userStatus,
      filesStatus,
      authUser,
      userFirstName,
      userLastName,
      powerBillStatus,
      isMobile,
    }
  },
}
</script>
