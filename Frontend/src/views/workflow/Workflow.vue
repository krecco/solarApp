<template>
  <div>
    <form-wizard-vertical />
    Und so weiter...
  </div>
</template>

<script>
import FormWizardVertical from './FormWizardVertical.vue'

export default {
  components: {
    FormWizardVertical,
  },
}
</script>

<style lang="scss">
  @import '@core/scss/vue/libs/vue-wizard.scss';
  @import '@core/scss/vue/libs/vue-select.scss';
</style>
