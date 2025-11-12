<template>
  <b-card
    v-observe-visibility="visibilityChanged"
    border-variant="secondary"
  >
    <swiper
      ref="swiperGallery"
      :key="swiperGalleryKey"
      class="swiper-lazy-loading"
      :options="swiperOptions"
      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
      style="height:300px"
    >
      <swiper-slide
        v-for="(data,index) in galleryImages"
        :key="index"
      >
        <b-img
          v-auth-image="apiUrl+'/user/file/'+data.id"
          fluid
          class="swiper-lazy"
        />
        <div class="swiper-lazy-preloader swiper-lazy-preloader-white" />
      </swiper-slide>

      <!-- Add Arrows -->
      <div
        slot="button-next"
        class="swiper-button-next"
      />
      <div
        slot="button-prev"
        class="swiper-button-prev"
      />

      <div
        slot="pagination"
        class="swiper-pagination"
      />
    </swiper>
    <!--
    <div
      v-for="(data) in galleryImages"
      :key="data.id"
    >
      <b-img
        v-auth-image="apiUrl+'/user/file/'+data.id"
        fluid
        style="max-height: 300px;"
      />
    </div>
    -->
  </b-card>
</template>

<script>
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import { BImg, BCard } from 'bootstrap-vue'
import 'swiper/css/swiper.css'
import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'
import { $apiUrl } from '@serverConfig'
import router from '@/router'
import store from '@/store'
import storeModule from '../storeModule'

//  let self
export default {
  components: {
    Swiper,
    SwiperSlide,
    BImg,
    BCard,
  },
  data() {
    return {
      swiperOptions: {
        lazy: true,
        slidesPerView: 3,
        spaceBetween: 30,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      },
      apiUrl: $apiUrl,
    }
  },
  setup() {
    const galleryImages = ref([])
    const swiperGalleryKey = ref(0)

    /* test 1
    console.log('gallery setup function')
    console.log(ref)

    setTimeout(() => {
      // try to reinit
      console.log('reinit try')
      console.log(self)
    }, 3000)
    */

    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const solarPlantGalleryUpdatedAt = computed(() => store.getters[`${STORE_MODULE_NAME}/solarPlantGalleryUpdatedAt`])

    const visibilityChanged = () => {
      swiperGalleryKey.value = Math.random()
    }

    const getGalleryImages = () => {
      store.dispatch(`${STORE_MODULE_NAME}/fetchGalleryPlan`, { id: router.currentRoute.params.id })
        .then(response => {
          galleryImages.value = response.data.payload
          visibilityChanged()

          /* test 2
          setTimeout(() => {
            console.log('updatecachekey')
            swiperGalleryKey.value = 132
          }, 4000)
          */
        })
    }
    getGalleryImages()

    watch(solarPlantGalleryUpdatedAt, (val, oldVal) => {
      console.log('watching updateSolarPlantGalleryUpdatedAt')
      if (val > oldVal) {
        console.log('load galery images')
        getGalleryImages()
      }
    })

    /*
    setTimeout(() => {
    }, 1000)
    */
    return {
      galleryImages,
      swiperGalleryKey,
      visibilityChanged,
    }
  },
  /*
  mounted() {
    this.$nextTick(() => {
      self = this
    })
  },
  */
}
</script>
