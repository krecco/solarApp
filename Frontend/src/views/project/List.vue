<template>
  <div>
    <b-row
      class="match-height"
      style="padding-top:10px;"
    >
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/1.jpg')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Traidersberg
          </b-card-title>
          <b-card-text>
            Das erhöht den volkswirtschaftlichen Nutzen volatiler Stromerzeugung und sorgt für noch mehr grünen Ökostrom aus der Region.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData1"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                v-if="media.subtitle === ''"
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ media.title }}
              </h3>
              <h6
                v-if="media.subtitle !== ''"
                class="mb-0"
              >
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/5.jpg')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Linz
          </b-card-title>
          <b-card-text>
            In unmittelbarer Nähe zum Windpark Kittsee/Pama wird in sogenannten Hybridparks Strom aus Wind und Sonne kombiniert.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData2"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                v-if="media.subtitle === ''"
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ media.title }}
              </h3>
              <h6
                v-if="media.subtitle !== ''"
                class="mb-0"
              >
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/17.jpg')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Graz
          </b-card-title>
          <b-card-text>
            Die Kombination ermöglicht zum Teil die gemeinsame Nutzung von bestehender Netzinfrastruktur.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData3"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h3
                v-if="media.subtitle === ''"
                class="mb-0"
                style="padding-top:5px;"
              >
                {{ media.title }}
              </h3>
              <h6
                v-if="media.subtitle !== ''"
                class="mb-0"
              >
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/solar.png')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Klagenfurt am Wörthersee
          </b-card-title>
          <b-card-text>
            Die Kombination ermöglicht zum Teil die gemeinsame Nutzung von bestehender Netzinfrastruktur.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData1"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h6 class="mb-0">
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/23.jpg')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Klagenfurt am Wörthersee
          </b-card-title>
          <b-card-text>
            In unmittelbarer Nähe zum Windpark Kittsee/Pama wird in sogenannten Hybridparks Strom aus Wind und Sonne kombiniert.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData1"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h6 class="mb-0">
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
        </b-card>
      </b-col>
      <b-col
        lg="4"
        md="12"
      >
        <b-card
          :img-src="require('@/assets/images/logo/24.jpg')"
          img-top
          img-alt="card img"
          class="mb-3"
        >
          <b-card-title>
            Klagenfurt am Wörthersee
          </b-card-title>
          <b-card-text>
            Das erhöht den volkswirtschaftlichen Nutzen volatiler Stromerzeugung und sorgt für noch mehr grünen Ökostrom aus der Region.
          </b-card-text>
          <!-- media -->
          <b-media
            v-for="media in mediaData1"
            :key="media.avatar"
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
                  :icon="media.avatar"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h6 class="mb-0">
                {{ media.title }}
              </h6>
              <small>{{ media.subtitle }}</small>
            </b-media-body>
          </b-media>
          <br>
          <b-card-text>
            <small class="text-muted">Last updated 3 mins ago</small>
          </b-card-text>
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
  BCardText,
  BCardTitle,
  BMedia,
  BMediaAside,
  BAvatar,
  //  BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardText,
    BCardTitle,
    BMedia,
    BMediaAside,
    BAvatar,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      mediaData1: [
        { avatar: 'ZapIcon', title: '4 MWp', subtitle: '' },
        { avatar: 'MapPinIcon', title: 'Center', subtitle: 'GSt. 584/1' },
        { avatar: 'CalendarIcon', title: 'Projekt Start', subtitle: 'September 2021' },
      ],
      mediaData2: [
        { avatar: 'ZapIcon', title: '1,7 MWp', subtitle: '' },
        { avatar: 'MapPinIcon', title: 'Industriepark', subtitle: 'Lange Gasse' },
        { avatar: 'CalendarIcon', title: 'Projekt Start', subtitle: 'November 2021' },
      ],
      mediaData3: [
        { avatar: 'ZapIcon', title: '5,8 MWp', subtitle: '' },
        { avatar: 'MapPinIcon', title: 'West', subtitle: 'Einkaufsstraße' },
        { avatar: 'CalendarIcon', title: 'Projekt Start', subtitle: 'Februar 2022' },
      ],
    }
  },
}
</script>

<style>

</style>
