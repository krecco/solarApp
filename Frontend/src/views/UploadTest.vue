<template>
  <b-card title="File Upload">
    <b-card-text>New Component demo</b-card-text>
    <b-card-text style="width:500px;">
        <!--
        <file-pond :files="myFiles"/>
        -->
        <file-pond
          name="test"
          ref="pond"
          label-idle="Drop files here..."
          v-bind:allow-multiple="true"
          accepted-file-types="image/jpeg, application/pdf"
          server="http://localhost:8080/user/upload-multipart-file"
          v-bind:files="myFiles"
          v-on:init="handleFilePondInit"
          allow-file-encode="true"
          allowFileEncode="true"
        />
    </b-card-text>
  </b-card>
</template>

<script>
import { BCard, BCardText } from 'bootstrap-vue'
import vueFilePond from 'vue-filepond'

//  Import plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.esm'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm'
import FilePondPluginFileEncode from 'filepond-plugin-file-encode'

//  Import styles
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode)

export default {
  components: {
    BCard,
    BCardText,
    FilePond,
  },
  methods: {
    handleFilePondInit: () => {
      console.log('FilePond has initialized')

      // FilePond instance methods are available on `this.$refs.pond`
    },
  },
  data() {
    return {
      myFiles: [],
    }
  },
}
</script>

<style>

</style>
