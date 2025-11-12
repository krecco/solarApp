<template>
  <div>
    <h1
      class="font-weight-bolder"
    >
      Ihre PV-Projektierung und Prognoserechnung
    </h1>
    <b-link
      :to="{ name: 'power-plant-detail', params: { id: this.$router.currentRoute.params.plantId } }"
      variant="success"
      class="font-weight-bold d-block text-nowrap"
    >
      <feather-icon
        icon="ArrowLeftCircleIcon"
        size="16"
        class="mr-0 mr-sm-50"
      />
      Zurück zur PV-Anlage
    </b-link>
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
          >Was ist zu tun:</h4>
        </div>
        <b-card-text
          style="padding:20px 20px 10px 20px;"
        >
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="DownloadIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              <b>Laden</b> Sie bitte die <b>Projektierungsunterlagen und die Prognoserechnung</b> für Ihre geplanten Photovoltaik-Anlage <b>herunter</b>.
            </b-col>
          </b-row>
          <b-row>
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="InfoIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Für <b>Rückfragen</b> stehen wir Ihnen unter <b>+43 (0)3326 52496-100</b> jederzeit gerne zur Verfügung.
            </b-col>
          </b-row>
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="CheckIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Wenn die Unterlagen und das Angebot (Prognoserechnung) Ihren Vorstellungen entsprechen klicken Sie bitte auf <b>„Auftragsabsicht bekanntgeben“</b>.
              Sie erhalten im nächsten Schritt alle relevanten Vertragsunterlagen und Vollmachten.
            </b-col>
          </b-row>
        </b-card-text>
      </b-card-body>
    </b-card>
    <br>
    <h4>Übersicht</h4>
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          Ihr prognostizierter Eigentumsübergang<br>
          <b-table
            responsive="sm"
            :items="detailsData"
            :fields="detailsTableColumns"
            style="margin-bottom:0px !important;"
          />
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Angebot </h4>
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          <b-table
            ref="powerbilltable"
            striped
            responsive
            :stacked="isMobile"
            :hover="true"
            :items="containerFilesOffer"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(fileName)="data"
            >
              <div
                v-if="isMobile !== true && isMobileDevice() !== true"
                v-b-tooltip.hover.top="'Vorschau'"
                class="text-left"
                style="font-size:1em"
              >
                <a
                  v-if="data.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
              >
                {{ data.item.fileName }}
              </div>
            </template>
            <template
              #cell(icon)="data"
            >
              <feather-icon
                v-if="data.value === 'application/pdf'"
                class="mr-1"
                icon="FileTextIcon"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div class="text-left">
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  target="_blank"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
              </div>
            </template>
          </b-table>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Planungsdokument</h4>
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          <b-table
            ref="powerbilltable"
            striped
            responsive
            :stacked="isMobile"
            :hover="true"
            :items="containerFilesPlan"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(icon)="data"
            >
              <feather-icon
                v-if="data.value === 'application/pdf'"
                class="mr-1"
                icon="FileTextIcon"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
              />
            </template>
            <template
              #cell(fileName)="data"
            >
              <div
                v-if="isMobile !== true && isMobileDevice() !== true"
                v-b-tooltip.hover.top="'Vorschau'"
                class="text-left"
                style="font-size:1em"
              >
                <a
                  v-if="data.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
              >
                {{ data.item.fileName }}
              </div>
            </template>
            <template v-slot:cell(t0)="data">
              <div class="text-left">
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  target="_blank"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
              </div>
            </template>
          </b-table>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Prognoserechnung</h4>
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          border-variant="primary"
        >
          <b-table
            ref="powerbilltable"
            striped
            responsive
            :stacked="isMobile"
            :hover="true"
            :items="containerFilesCalculation"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(fileName)="data"
            >
              <div
                v-if="isMobile !== true && isMobileDevice() !== true"
                v-b-tooltip.hover.top="'Vorschau'"
                class="text-left"
                style="font-size:1em"
              >
                <a
                  v-if="data.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageCalculation
                  nohref
                  @click="loadPreview(data.item.id)"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
              >
                {{ data.item.fileName }}
              </div>
            </template>
            <template
              #cell(icon)="data"
            >
              <feather-icon
                v-if="data.value === 'application/pdf'"
                class="mr-1"
                icon="FileTextIcon"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div class="text-left">
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  style="margin-left:10px;"
                  target="_blank"
                  @click="getOfferDownloadStatus();setBtnColor(row.item);"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                    style="min-width:170px;"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
              </div>
            </template>
          </b-table>
        </b-card>
      </b-col>
    </b-row>
    <br><br>
    <b-row
      v-if="offersDownloaded === true"
    >
      <b-col
        cols="12"
      >
        <!-- v-if="contractSignedAndUploaded !== true && contractsAccepted !== true && contractsDownloaded === true" -->
        <b-card
          border-variant="primary"
        >
          <b-form-checkbox
            v-model="offersRead"
            switch
            inline
            :disabled="baseData.orderInterestAccepted"
          >
            <span style="font-size:1.5em;">Alle Dokumente gelesen und einverstanden</span>
          </b-form-checkbox>
          <div
            v-if="offersRead===true"
          >
            <div>
              <br>
              <br>
              <br>
              <b-row>
                <b-col
                  cols="12"
                  md="12"
                >
                  <div class="d-flex">
                    <feather-icon
                      icon="CreditCardIcon"
                      size="19"
                    />
                    <h4 class="mb-0 ml-50">
                      SEPA Information
                    </h4>
                  </div>
                  <br>
                  <!-- Sepa Info: Input Fields -->
                  <validation-observer ref="validateSepaFormOffer">
                    <b-form
                      @submit.prevent="onSubmitSepa"
                    >
                      <b-row>
                        <!-- Field: fullName -->
                        <b-col
                          cols="12"
                          md="12"
                        >
                          <b-form-group
                            label="Kontoinhaber"
                            label-for="fullName"
                          >
                            <validation-provider
                              #default="{ errors }"
                              name="Kontoinhaber"
                              rules="required"
                            >
                              <b-form-input
                                id="fullName"
                                v-model="sepaData.fullName"
                              />
                              <small class="text-warning">{{ errors[0] }}</small>
                            </validation-provider>
                          </b-form-group>
                        </b-col>
                        <b-col
                          cols="12"
                          md="8"
                        >
                          <b-form-group
                            label="IBAN"
                            label-for="account"
                          >
                            <validation-provider
                              #default="{ errors }"
                              name="IBAN"
                              rules="required|iban"
                            >
                              <cleave
                                id="account"
                                v-model="sepaData.account"
                                class="form-control"
                                :options="options.iban"
                              />
                              <small class="text-warning">{{ errors[0] }}</small>
                            </validation-provider>
                          </b-form-group>
                        </b-col>
                        <b-col
                          cols="12"
                          md="4"
                        >
                          <b-form-group
                            label="BIC"
                            label-for="bic"
                          >
                            <validation-provider
                              #default="{ errors }"
                              name="BIC"
                              rules="required"
                            >
                              <b-form-input
                                id="bic"
                                v-model="sepaData.bic"
                              />
                              <small class="text-warning">{{ errors[0] }}</small>
                            </validation-provider>
                          </b-form-group>
                        </b-col>
                      </b-row>
                      <b-button
                        v-if="baseData.orderInterestAccepted !== true && orderInterestAccepted === false"
                        variant="primary"
                        class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                        :block="$store.getters['app/currentBreakPoint'] === 'xs'"
                        @click="validationFormSepa"
                      >
                        Speichern
                      </b-button>
                    </b-form>
                  </validation-observer>
                </b-col>
              </b-row>
              <br>
              <br>
            </div>
          </div>
          <b-row
            v-if="sepaExists === true && offersRead === true"
          >
            <b-col
              cols="12"
            >
              <br>
              <br>
              <b-alert
                v-if="baseData.orderInterestAccepted !== true && orderInterestAccepted === false"
                variant="primary"
                show
              >
                <div class="alert-body">
                  <span>Wenn die Unterlagen und das Angebot (Prognoserechnung) Ihren Vorstellungen entsprechen <b>klicken Sie bitte auf „Auftragsabsicht bekanntgeben“</b>. Sie erhalten im nächsten Schritt alle relevanten Vertragsunterlagen und Vollmachten.</span>
                </div>
              </b-alert>
              <br>
              <b-button
                v-if="baseData.orderInterestAccepted !== true && orderInterestAccepted === false"
                size="lg"
                variant="primary"
                class="mb-1 mb-sm-0 mr-0 mr-sm-1"
                type="button"
                block
                :disabled="showLoading"
                @click="acceptOffer"
              >
                <b-spinner
                  v-if="showLoading"
                />
                <div v-if="showLoading === false">
                  Auftragsabsicht bekanntgeben
                </div>
              </b-button>

              <app-collapse
                v-if="offersRead===true && baseData.orderInterestAccepted !== true && orderInterestAccepted === false"
                accordion
                style="padding-top:50px;"
              >
                <app-collapse-item title="Ich will andere Anzahlung">
                  <b-row>
                    <!-- v-if="tariff.directBuy === false"-->
                    <b-col
                      cols="12"
                      md="9"
                    >
                      <b-form-group
                        label="Anzahlung"
                        label-for="prePayment"
                      >
                        <vue-slider
                          v-model="plantCalculationInfo.prePayment"
                          :adsorb="true"
                          :marks="false"
                          :tooltip="'always'"
                          :min="plantCalculationInfo.prePaymentMin"
                          :max="plantCalculationInfo.prePaymentMax"
                          :interval="50"
                          class="mb-3"
                          direction="ltr"
                          style="padding-top:30px;"
                        />
                      </b-form-group>
                    </b-col>
                    <b-col
                      cols="12"
                      md="3"
                    >
                      <!-- v-if="tariff.directBuy === false"-->
                      <b-form-group
                        label="Anzahlung"
                        label-for="prePayment"
                      >
                        <validation-provider
                          #default="{ errors }"
                          name="Anzahlung"
                          rules="required"
                        >
                          <!-- :change="recalculateFinalPrice()" -->
                          <cleave
                            id="prePayment"
                            v-model="plantCalculationInfo.prePayment"
                            class="form-control"
                            trim
                            placeholder=""
                            :options="options.number"
                            :state="errors.length > 0 ? false:null"
                          />
                          <small class="text-warning">{{ errors[0] }}</small>
                        </validation-provider>
                      </b-form-group>
                    </b-col>
                    <b-col>
                      <b-button
                        variant="outline-primary"
                        block
                        @click="sendMessageToBackoffice(1)"
                      >
                        <b-spinner
                          v-if="sendingMessage"
                          small
                        />
                        Neue Anzahlung anfordern
                      </b-button>
                    </b-col>
                  </b-row>
                </app-collapse-item>
                <app-collapse-item title="Ich will Direkt kaufen">
                  ihr Kommentar:<br>
                  <b-form-textarea
                    v-model="messagesToBackend.directBuy"
                    placeholder=""
                    rows="4"
                  />
                  <br>
                  <b-button
                    variant="outline-primary"
                    block
                    @click="sendMessageToBackoffice(2)"
                  >
                    <b-spinner
                      v-if="sendingMessage"
                      small
                    />
                    Kommentar senden
                  </b-button>
                  <br><br>
                </app-collapse-item>
                <app-collapse-item title="Änderung an PV Projektierung">
                  ihr Kommentar:<br>
                  <b-form-textarea
                    v-model="messagesToBackend.requestProjectChange"
                    placeholder=""
                    rows="4"
                  />
                  <br>
                  <b-button
                    variant="outline-primary"
                    block
                    @click="sendMessageToBackoffice(3)"
                  >
                    <b-spinner
                      v-if="sendingMessage"
                      small
                    />
                    Kommentar senden
                  </b-button>
                  <br><br>
                </app-collapse-item>
              </app-collapse>

              <b-card
                v-if="orderInterestAccepted === true"
                border-variant="primary"
                style="text-align: center;"
              >
                <b-row
                  class="match-height"
                >
                  <b-col
                    cols="12"
                    md="12"
                    style="padding-top:5px;"
                  >
                    <h3>
                      <feather-icon
                        icon="CheckCircleIcon"
                        size="30"
                        class="mr-0 mr-sm-50"
                      />
                      Vielen Dank für die Bekanntgabe Ihrer Auftragsabsicht. Sie erhalten in Kürze die zugehörigen Vertragsunterlagen zur Einsichtnahme.
                    </h3>
                  </b-col>
                </b-row>
              </b-card>
              <b-card
                v-if="baseData.orderInterestAccepted === true"
                border-variant="primary"
                style="text-align: center;"
              >
                <b-row
                  class="match-height"
                >
                  <b-col
                    cols="12"
                    md="12"
                    style="padding-top:5px;"
                  >
                    <h3>
                      <feather-icon
                        icon="CheckCircleIcon"
                        size="30"
                        class="mr-0 mr-sm-50"
                      />
                      Auftragsabsicht bekundet
                    </h3>
                  </b-col>
                </b-row>
              </b-card>
              <app-collapse
                v-if="baseData.orderInterestAccepted === true || orderInterestAccepted === true"
                accordion
              >
                <app-collapse-item title="Möchten Sie etwas ändern? Kommentar an Kundenservice senden.">
                  ihr Kommentar:<br>
                  <b-form-textarea
                    v-model="messagesToBackend.requestContractChange"
                    placeholder=""
                    rows="4"
                  />
                  <br>
                  <b-button
                    variant="outline-primary"
                    block
                    @click="sendMessageToBackoffice(4)"
                  >
                    <b-spinner
                      v-if="sendingMessage"
                      small
                    />
                    Kommentar senden
                  </b-button>
                  <br><br>
                </app-collapse-item>
              </app-collapse>

            </b-col>
          </b-row>
        </b-card>
      </b-col>
    </b-row>
    <b-row
      v-if="offersDownloaded !== true"
    >
      <b-col
        style="margin-bottom:200px"
      >
        <b-alert
          variant="danger"
          show
        >
          <div
            class="alert-body"
            style="text-align:center;"
          >
            <span><strong>Um Auftragsabsicht bekanntgeben bitte alle Unterlagen herunterladen und überprüfen.</strong></span>
          </div>
        </b-alert>
      </b-col>
    </b-row>
    <b-modal
      id="modal-previewImageCalculation"
      title="Bildvorschau"
      ok-title="Schließen"
      ok-only
      size="xl"
    >
      <div
        v-if="loadingPreviewFile === true"
        style="text-align: center;"
      >
        <b-spinner /><br>
        Wird geladen ...
      </div>
      <div
        style="text-align: center; background-color:black;"
      >
        <b-img
          :src="previewFile"
          style="max-height: 85vh;"
          fluid
        />
      </div>
    </b-modal>

    <b-modal
      id="modal-previewPDFCalculation"
      title="PDF vorschau"
      ok-title="Schließen"
      ok-only
      size="xl"
    >
      <div
        v-if="loadingPreviewFile === true"
        style="text-align: center;"
      >
        <b-spinner /><br>
        Wird geladen ...
      </div>
      <object
        :data="previewFile"
        style="width: 100%; height:85vh;"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BRow,
  BCol,
  BTable,
  BCard,
  BLink,
  BCardText,
  BCardBody,
  BButton,
  BSpinner,
  BAlert,
  VBTooltip,
  BModal,
  BImg,
  BFormCheckbox,
  BFormGroup,
  BFormInput,
  BForm,
  BFormTextarea,
} from 'bootstrap-vue'
import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'
import Cleave from 'vue-cleave-component'

import VueSlider from 'vue-slider-component'
import '@core/scss/vue/libs/vue-slider.scss'

import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItem from '@core/components/app-collapse/AppCollapseItem.vue'

import { ValidationProvider, ValidationObserver, localize } from 'vee-validate'
import { required } from '@validations'
import { base64StringToBlob } from 'blob-util'
import { $apiUrl } from '@serverConfig'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
import numeral from 'numeral'
import store from '@/store'
import storeModule from '../storeModule'
import router from '@/router'

localize('de')
export default {
  components: {
    BRow,
    BCol,
    BTable,
    BCard,
    BCardBody,
    BLink,
    BCardText,
    BButton,
    BSpinner,
    BAlert,
    BModal,
    BImg,
    BFormCheckbox,
    ValidationProvider,
    ValidationObserver,
    BFormGroup,
    BFormInput,
    BForm,
    Cleave,
    AppCollapse,
    AppCollapseItem,
    BFormTextarea,
    VueSlider,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    containerFilesCalculation: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerFilesOffer: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerFilesPlan: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  data() {
    return {
      previewFile: '',
      loadingPreviewFile: false,
      showLoading: false,
      orderInterestAccepted: false,
      apiUrl: $apiUrl,
      accessToken: localStorage.getItem('accessToken'),
      gender_options: [
        { value: null, text: 'Please select an option' },
        { value: '1', text: 'Mr.' },
        { value: '2', text: 'Mrs.' },
      ],
      options: {
        number: numberFormat,
        numberDeep: numberFormatDeep,
        iban: {
          blocks: [4, 4, 4, 4, 4],
          uppercase: true,
        },
      },
      required,
    }
  },
  methods: {
    loadPreview(id) {
      this.previewFile = ''
      this.loadingPreviewFile = true
      store.dispatch('app-power-plant/getFileBase', { id })
        .then(response => {
          const blob = base64StringToBlob(response.data, response.headers['content-type'])
          const fileURL = URL.createObjectURL(blob)

          this.loadingPreviewFile = false
          this.previewFile = fileURL
        })
        .catch(error => {
          console.log(error)
        })
    },
    isDisabled() {
      return false
      //  not defined yet
      //  return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
    acceptOffer() {
      this.$bvModal
        .msgBoxConfirm('Sie erhalten in Kürze die Verträge und Vollmachten zur Einsichtnahme.', {
          title: 'Angebot annehmen?',
          size: 'sm',
          okVariant: 'primary',
          okTitle: 'OK',
          cancelTitle: 'abbrechen',
          cancelVariant: 'outline-secondary',
          hideHeaderClose: false,
          centered: true,
        })
        .then(value => {
          if (value === true) {
            this.showLoading = true
            store.dispatch('app-power-plant/simpleStatusUpdateFrontend', { id: router.currentRoute.params.plantId, status: 'orderInterestAccepted', value: true })
              .then(() => {
                this.showLoading = false
                //  this.baseData.orderInterestAccepted = true
                this.orderInterestAccepted = true
              })
          }
        })
    },
    isMobileDevice() {
      let isMobileDevice = false
      if ((this.$device.mobile === true) || (this.$device.ios === true) || (this.$device.android === true)) {
        isMobileDevice = true
      }
      return isMobileDevice
    },
    validationFormSepa() {
      this.$refs.validateSepaFormOffer.validate().then(success => {
        if (success) {
          this.onSubmitSepa()
        }
      })
    },
  },
  setup(props) {
    //  change this line according to app(view) name
    const STORE_MODULE_NAME = 'app-power-plant'
    // Register module
    if (!store.hasModule(STORE_MODULE_NAME)) store.registerModule(STORE_MODULE_NAME, storeModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(STORE_MODULE_NAME)) store.unregisterModule(STORE_MODULE_NAME)
    })

    const toast = useToast()
    const fileContainersData = ref([])
    const baseData = ref({})
    const sepaData = ref({})
    const offersDownloaded = ref(false)
    const offersRead = ref(false)
    const sepaExists = ref(false)
    const detailsData = ref([])
    const plantCalculationInfo = ref({
      prePayment: 0,
      prePaymentMin: 0,
      prepaymentMax: 1,
    })
    const messagesToBackend = ref({
      requestProjectChange: '',
      directBuy: '',
      requestContractChange: '',
    })
    const sendingMessage = ref(false)

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

    const loadDocumentFiles = () => {
      //  fileContainersData.value.push(response.data.payload.filter(c => (c.type === 21))[0])
      //  fileContainersData.value.push(response.data.payload.filter(c => (c.type === 23))[0])

      store.dispatch('app-power-plant/getDocumentFiles', { id: fileContainersData.value.filter(c => (c.type === 21))[0].id })
        .then(response => {
          //  inventing contitions on the fly is the best way!
          const file = response.data.payload[0]
          /* eslint-disable no-param-reassign */
          props.containerFilesCalculation = [file]
          /* eslint-ebable no-param-reassign */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('error connecting to server')
            /* eslint-enable no-console */
          }
        })

      store.dispatch('app-power-plant/getDocumentFiles', { id: fileContainersData.value.filter(c => (c.type === 23))[0].id })
        .then(response => {
          const file = response.data.payload[0]
          /* eslint-disable no-param-reassign */
          props.containerFilesOffer = [file]
          /* eslint-ebable no-param-reassign */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('error connecting to server')
            /* eslint-enable no-console */
          }
        })

      store.dispatch('app-power-plant/getDocumentFiles', { id: fileContainersData.value.filter(c => (c.type === 22))[0].id })
        .then(response => {
          const file = response.data.payload[0]
          /* eslint-disable no-param-reassign */
          props.containerFilesPlan = [file]
          /* eslint-ebable no-param-reassign */
        })
        .catch(error => {
          if (error.status === 404) {
            /* eslint-disable no-console */
            console.log('error connecting to server')
            /* eslint-enable no-console */
          }
        })
    }

    const fetchUserSepa = () => {
      store.dispatch('app-power-plant/fetchUserSepa', { id: JSON.parse(localStorage.getItem('userData')).uid })
        .then(response2 => {
          sepaData.value = response2.data.payload

          if (sepaData.value.account === null) {
            sepaData.value.account = ''
          } else if (sepaData.value.account.length > 5) {
            sepaExists.value = true
          }

          if (sepaData.value.bic === null) {
            sepaData.value.bic = ''
          }

          if (sepaData.value.fullName === null) {
            sepaData.value.fullName = ''
          }
        })
    }

    const getBtnColor = item => {
      if (item.downloadedByUser === true) {
        return 'outline-primary'
      }

      return 'primary'
    }

    const setBtnColor = item => {
      item.downloadedByUser = true
    }

    const fetchBaseData = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchBaseData`, { id: router.currentRoute.params.plantId })
        .then(response => {
          baseData.value = response.data.payload

          if (baseData.value.orderInterestAccepted === true) {
            offersRead.value = true
          }

          store.dispatch('app-power-plant/fetchFileContainers', { id: router.currentRoute.params.plantId })
            .then(response1 => {
              fileContainersData.value = response1.data.payload
              loadDocumentFiles()
            })

          fetchUserSepa()
        })
        .catch(error => {
          console.log(error)
        })
    }

    const fetchPlantPreviewInfo = () => {
      store
        .dispatch(`${STORE_MODULE_NAME}/fetchPlantPreviewInfo`, { id: router.currentRoute.params.plantId })
        .then(response => {
          detailsData.value = [
            { title: 'Gesamtkosten PV-Anlage inkl. Ust. ***', value: `${numeral(response.data.unitPrice).format('0,0.00')} EUR` },
            { title: 'Anzahlung', value: `${numeral(response.data.prePayment).format('0,0.00')} EUR` },
            { title: 'Förderzuschuss', value: `${numeral(response.data.subvention).format('0,0.00')} EUR` },
            { title: 'Eigentumsübergang nach Jahren ****', value: `${numeral(response.data.duration).format('0.0')} Jahren` },
          ]

          if (response.data.openBalance > 0) {
            detailsData.value.push({ title: 'Restbetrag nach 12,5 Jahren in Euro, zu zahlen an solar.family', value: `${numeral(response.data.openBalance).format('0,0.00')} EUR` })
          }

          plantCalculationInfo.value = response.data
        })
        .catch(error => {
          console.log(error)
        })
    }

    //  load data
    fetchBaseData()
    fetchPlantPreviewInfo()

    const getOfferDownloadStatus = () => {
      setTimeout(() => {
        store.dispatch('app-power-plant/getOfferDownloadStatus', { id: router.currentRoute.params.plantId })
          .then(response => {
            if (response.data.status === 200) {
              offersDownloaded.value = true
            }
          })
      }, 1000)
    }

    getOfferDownloadStatus()

    const sendMessageToBackoffice = type => {
      let msg = ''
      let value = 0

      let error = false
      if (type === 1) {
        value = plantCalculationInfo.value.prePayment
      } else if (type === 2) {
        msg = messagesToBackend.value.directBuy
        if (msg === '') { error = true }
      } else if (type === 3) {
        msg = messagesToBackend.value.requestProjectChange
        if (msg === '') { error = true }
      } else if (type === 4) {
        msg = messagesToBackend.value.requestContractChange
        if (msg === '') { error = true }
      }

      if (error === true) {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Fehler',
            text: 'Bitte Kommentar eingeben!',
            icon: 'AlertTriangleIcon',
            variant: 'warning',
          },
        })
      } else {
        const message = {
          msg,
          value,
          type,
        }
        sendingMessage.value = true
        store.dispatch('app-power-plant/sendMessageToBackoffice', { plantId: router.currentRoute.params.plantId, message })
          .then(response => {
            messagesToBackend.value.directBuy = ''
            messagesToBackend.value.requestProjectChange = ''
            messagesToBackend.value.requestContractChange = ''
            if (response.data.status === 200) {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Nachricht an Kundenservice übermittelt!',
                  icon: 'CheckIcon',
                  variant: 'success',
                },
              })
              sendingMessage.value = false
            } else {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Nachricht an Kundenservice nicht übermittelt!',
                  icon: 'AlertTriangleIcon',
                  variant: 'warning',
                },
              })
              sendingMessage.value = false
            }
          })
      }
    }

    const onSubmitSepa = () => {
      store.dispatch('app-power-plant/editSepa', { userId: JSON.parse(localStorage.getItem('userData')).id, sepaData })
        .then(response => {
          if (response.data.payload.status === 200) {
            fetchUserSepa()
            toast({
              component: ToastificationContent,
              props: {
                title: 'SEPA erfolgreich aktualisiert',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Fehler beim SEPA Aktualisierung',
                text: response.data.payload.message,
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
              title: 'Fehler beim SEPA Aktualisierung: Server',
              icon: 'AlertTriangleIcon',
              variant: 'warning',
            },
          })
        })
    }

    const userDocumentFields = [
      {
        key: 'icon',
        label: 'Info',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '40px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '350px !important', textAlign: 'right', paddingRight: '70px' } },
    ]

    const detailsTableColumns = [
      { key: 'title', label: 'Titel', thStyle: { width: '80% !important' } },
      { key: 'value', label: '', thStyle: { width: '50px !important' } },
    ]

    return {
      userDocumentFields,
      baseData,
      isMobile,
      getOfferDownloadStatus,
      offersDownloaded,
      sepaData,
      onSubmitSepa,
      offersRead,
      sepaExists,
      getBtnColor,
      setBtnColor,
      detailsData,
      detailsTableColumns,
      sendMessageToBackoffice,
      plantCalculationInfo,
      messagesToBackend,
      sendingMessage,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
