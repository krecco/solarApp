<template>
  <div>
    <h1
      class="font-weight-bolder"
    >
      Letzte Energieabrechnung
    </h1>
    <b-link
      :to="{ name: 'user-detail' }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zum PV-Anlage
    </b-link>
    <br>
    <br>
    Damit wir eine Prognoserechnung für Ihre geplante PV-Anlage erstellen können, benötigen wir einige Daten aus Ihrer letzten Energieabrechnung, wie z.B. Jahresverbrauch in Kw und die derezitigen Kosten.
    <br>
    <br>
    <br>
    <b-card no-body>
      <b-card-body
        style="padding: 0;"
      >
        <div
          style="padding:20px 20px 10px 20px; background-color:rgba(124, 157, 43, 0.25);"
        >
          <h4
            class="font-weight-bolder"
          >Was ist zu tun</h4>
        </div>
        <b-card-text
          style="padding:20px 20px 10px 20px;"
        >
          <feather-icon
            icon="CameraIcon"
            size="16"
            class="mr-0 mr-sm-50"
          /> Machen Sie ein <b>Foto oder scannen</b> die letzte Energieabrechnung.
          <br>
          <feather-icon
            icon="CheckIcon"
            size="16"
            class="mr-0 mr-sm-50"
          /> Prüfen Sie, ob der <b>Kw-Verbrauch und der Rechnungsbetrag</b> gut sichtbar sind.
          <br>
          <feather-icon
            icon="UploadIcon"
            size="16"
            class="mr-0 mr-sm-50"
          /> Laden Sie entweder <b>alle Fotos einzeln hoch</b>, oder machen Sie aus den Fotos <b>EINEN pdf</b> zum hochladen.
        </b-card-text>
      </b-card-body>
    </b-card>
    <br>
    <b-card
      border-variant="primary"
    >
      <b-table
        hover
        :items="items"
        :fields="userDocumentFields"
      >
        <template #cell(actions)="row">
          <div
            class="text-right"
          >
            <a
              v-auth-href
              :href="`${row.item.id}`"
            >
              <feather-icon
                class="mr-1"
                icon="ArrowDownCircleIcon"
              />
            </a>
            <a
              nohref
            >
              <feather-icon
                class="mr-1"
                icon="XCircleIcon"
              />
            </a>
          </div>
        </template>
      </b-table>
      <br>
      <b-button
        v-b-modal.modal-uploadFileNew
        size="sm"
        variant="outline-secondary"
        class="mb-1 mb-sm-0 mr-0 mr-sm-1"
        type="button"
        block
      >
        <feather-icon
          icon="PlusCircleIcon"
          size="15"
        />
        Neue Datei hochladen
      </b-button>
    </b-card>
  </div>
</template>
<script>
import {
  BCard, BCardText, BButton, BLink, BTable, BCardBody,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BCard, BCardText, BButton, BLink, BTable, BCardBody,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      items: [
        {
          fileName: 'user_energieabrechnung_78086757-bba5-414f-af80-24bb9f9cc84c.jpg', t0: '19.11.2021',
        },
        {
          fileName: 'user_energieabrechnung_122086757-erds-414f-af80-dsdsdsawdwc.jpg', t0: '19.11.2021',
        },
      ],
    }
  },
  setup() {
    const userDocumentFields = [
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '80px !important' } },
    ]

    return {
      userDocumentFields,
    }
  },
}
</script>
