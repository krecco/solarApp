<template>
  <p class="clearfix mb-0">
    <span class="float-md-left d-block d-md-inline-block mt-25">
      COPYRIGHT  © {{ new Date().getFullYear() }}
      <b-link
        class="ml-25"
        href="https://solar.family"
        target="_blank"
      >solar.family</b-link>
      <span class="d-none d-sm-inline-block" />
    </span>

    <span class="float-md-right d-none d-md-block" />
  </p>
</template>

<script>
import { BLink } from 'bootstrap-vue'

export default {
  components: {
    BLink,
  },
}
</script>
