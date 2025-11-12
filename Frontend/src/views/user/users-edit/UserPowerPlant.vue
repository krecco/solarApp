<template>
  <div>
    <br>
    <div class="d-flex">
      <feather-icon
        icon="ZapIcon"
        size="19"
      />
      <h4 class="mb-0 ml-50">
        Meine Solaranlage ({{ plantData.title }})
      </h4>
    </div>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <!-- causes reload
        <b-button
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          style="float:right;"
          type="button"
          @click.prevent="generateFile()"
        >
          <feather-icon
            icon="CheckCircleIcon"
            size="15"
          />
          Tmp Generate
        </b-button>
        -->
      </b-col>
    </b-row>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <br><br>
        <b-table
          striped
          responsive
          :hover="true"
          :items="userDocuments"
          class="mb-0"
        >
          <template
            #cell(action)="data"
          >
            <div
              class="text-right"
            >
              <a
                :href="data.value"
                target="_blank"
              >
                <feather-icon
                  class="mr-1"
                  icon="EyeIcon"
                />
              </a>
              <a
                href="#"
                target="_blank"
              >
                <feather-icon
                  class="mr-1"
                  icon="UploadIcon"
                />
              </a>
            </div>
          </template>
        </b-table>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BRow, BCol, BTable,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import store from '@/store'

export default {
  components: {
    BRow, BCol, BTable,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      file1: [],
      selected: '',
      option: ['Vollmacht', 'Grundbuchauszug', 'Verträg', 'Angebot', 'Other'],
    }
  },
  setup() {
    const plantData = ref(null)
    const fileData = ref(null)

    //  define fieldnames in production
    const userDocuments = [
      {
        Title: 'Prognoserechnung',
        Type: 'prognose',
        DateAdded: '28.04.2021',
        action: 'http://localhost:8080/generated_files/0bd33d71-b7ee-4d8a-9694-220c2343148e-881593d5-e6c8-44bf-aa23-f329a7cc0b02.pdf',
        //  uploadable: false,
      },
      {
        Title: 'Vollmacht User',
        Type: 'Vollmacht',
        DateAdded: '13.02.2021',
        action: 'A_B-solar.family Projektierung Teil2 V1.0_20210403.pdf',
      },
    ]

    const fakeFileUpload = () => {
      console.log('Fake File Upload')
    }

    const generateFile = () => {
      store.dispatch('app-user/generateDocument1', { ida: '881593d5-e6c8-44bf-aa23-f329a7cc0b02', idb: '0bd33d71-b7ee-4d8a-9694-220c2343148e' })
        .then(response => { fileData.value = response.data })
        .catch(error => {
          if (error.response.status === 404) {
            console.log(error)
          }
        })
      /*
      store.dispatch('app-user/getPlantInfo', { id: '881593d5-e6c8-44bf-aa23-f329a7cc0b02' })
        .then(response => { fileData.value = response.data })
        .catch(error => {
          if (error.response.status === 404) {
            plantData.value = undefined
          }
        })
        */
    }

    store.dispatch('app-user/getPlantInfo', { id: '881593d5-e6c8-44bf-aa23-f329a7cc0b02' })
      .then(response => { plantData.value = response.data })
      .catch(error => {
        if (error.response.status === 404) {
          plantData.value = undefined
        }
      })

    return {
      plantData,
      userDocuments,
      fakeFileUpload,
      generateFile,
    }
  },
}
</script>

<style>

</style>
