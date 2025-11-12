<template>
  <div>
    <br>
    <b-row>
      <b-col
        cols="12"
        md="12"
      >
        <!--
        <div class="d-flex">
          <feather-icon
            icon="CreditCardIcon"
            size="19"
          />
          <h4 class="mb-0 ml-50">
            Benutzer freischalten
          </h4>
        </div>
        -->
        Passwort Zurücksetzen mail an den Benutzer Senden?
        <br>
        <br>
        <b-button
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          :block="$store.getters['app/currentBreakPoint'] === 'xs'"
          @click="passwordResetSend"
        >
           Passwort Zurücksetzen
        </b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BButton, BRow, BCol,
} from 'bootstrap-vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref } from '@vue/composition-api'
import store from '@/store'

export default {
  components: {
    BButton,
    BRow,
    BCol,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
  },
  methods: {
    passwordResetSend() {
      this.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie das Passwort Zurücksetzen mail an den Benutzer Senden möchten.', {
          title: 'Passwort Zurücksetzen?',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'Ja',
          cancelTitle: 'Nein',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            this.passwordReset()
          }
        })
    },
  },
  setup(props) {
    const toast = useToast()
    const sendingMail = ref(false)

    const passwordReset = () => {
      store.dispatch('app-user/passwordReset', { userId: props.userData.userId })
        .then(response => {
          console.log(response)

          if (response.data.payload.status === 200) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'OK!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler',
                icon: 'AlertTriangleIcon',
                variant: 'warning',
              },
            })
          }
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Fehler Server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    return {
      passwordReset,
      sendingMail,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
