<template>
  <div>
    <h1
      class="font-weight-bolder"
    >
      Ihre Vertragsunterlagen
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
    Die Vertragsunterlagen für Ihre solar.family Photovoltaik-Anlage stehen Ihnen zum Download zur Verfügung.
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
              <b>Laden</b> Sie bitte die <b>Vertragsunterlagen</b> zu Ihrer geplanten Photovoltaik-Anlage <b>herunter</b>.
            </b-col>
          </b-row>
          <b-row
            style="margin-bottom:5px;"
          >
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
                icon="Edit2Icon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Wenn die Unterlagen Ihren Vorstellungen entsprechend <b>unterschreiben Sie die Dokumente</b>.
            </b-col>
          </b-row>
          <b-row
            style="margin-bottom:5px;"
          >
            <b-col
              style="max-width:30px;"
            >
              <feather-icon
                icon="UploadIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
            </b-col>
            <b-col>
              Damit wir Ihren Vertrag aktivieren können, <b>scannen oder fotografieren Sie die unterfertigten Unterlagen und laden Sie die Dokumente wieder hoch</b> (Sie müssen alle Dokumente vollständig hochladen).
            </b-col>
          </b-row>
        </b-card-text>
      </b-card-body>
    </b-card>
    <br>
    <h4>Vertrag Energieeinsparung</h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerSavingContract"
            :fields="userDocumentFields"
            class="mb-0"
          >
            <template
              #cell(icon)="data"
            >
              <feather-icon
                v-if="data.value === 'application/pdf'"
                class="mr-1"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                icon="FileTextIcon"
              />
              <feather-icon
                v-else
                class="mr-1"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                icon="ImageIcon"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                icon="UploadIcon"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.item.fileName }}
              </div>
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->

                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(24)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Vertrag Verrechnungsblatt </h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerContractBillingt"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->

                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>

                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(25)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Vollmacht Abwicklung</h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerMandateCompletion"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->
                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(27)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Vollmacht Energieabrechnung</h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerMandateBilling"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->

                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(29)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>Vollmacht Netzbetreiber</h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerMandateBillingNet"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->

                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(291)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <h4>SEPA</h4>
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
            :hover="true"
            :stacked="isMobile"
            :items="containerSepa"
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
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
                <a
                  v-if="data.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(data.item.id)"
                  :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
                >
                  {{ data.value }}
                </a>
              </div>
              <div
                v-else
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
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
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
              <feather-icon
                v-else
                class="mr-1"
                icon="ImageIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === false"
                v-b-tooltip.hover.top="'Hochgeladen: ich'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />

              <feather-icon
                v-if="data.item.backendUserUpload === true || data.item.generated === true"
                v-b-tooltip.hover.top="'Hochgeladen: solar.family'"
                class="mr-1"
                icon="UploadIcon"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              />
            </template>
            <template v-slot:cell(t0)="data">
              <div
                class="text-left"
                :style="[(data.item.backendUserUpload !== true && data.item.generated !== true) ? {'color': '#7c9d2b'} : {}]"
              >
                {{ data.value | moment("DD.MM. YYYY") }}
              </div>
            </template>
            <!-- Column: Actions -->
            <template #cell(actions)="row">
              <div
                :class="[isMobile === true ? 'text-left' : 'text-right']"
              >
                <!--
                <a
                  v-if="isDisabled() !== true && row.item.fileContentType === 'application/pdf'"
                  v-b-modal.modal-previewPDFContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>

                <a
                  v-if="isDisabled() !== true && row.item.fileContentType !== 'application/pdf'"
                  v-b-modal.modal-previewImageContract
                  nohref
                  @click="loadPreview(row.item.id)"
                >
                  <b-button
                    variant="outline-primary"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="EyeIcon"
                    />
                    Vorschau
                  </b-button>
                </a>
                -->

                <a
                  v-if="isMobileDevice() === true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  target="_blank"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
                  >
                    <feather-icon
                      class="mr-1"
                      icon="DownloadIcon"
                    />
                    Herunterladen
                  </b-button>
                </a>
                <a
                  v-if="isMobileDevice() !== true"
                  v-auth-href
                  style="margin-left:10px;"
                  :href="`${apiUrl}/user/file/${row.item.id}/${row.item.fileName}`"
                  @click="setBtnColor(row.item)"
                >
                  <b-button
                    :variant="getBtnColor(row.item)"
                    size="sm"
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
          <br>
          <b-button
            v-b-modal.modal-uploadContract
            size="sm"
            variant="outline-secondary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            type="button"
            block
            :disabled="isDisabled()"
            @click="setContainerByTypeId(201)"
          >
            <feather-icon
              icon="UploadIcon"
              size="15"
            />
            Unterschriebene Dokumente hochladen
          </b-button>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <br>
    <!--<h4>Dokumente gelesen und überprüft</h4>-->
    <b-row>
      <b-col
        cols="12"
      >
        <b-card
          v-if="contractSignedAndUploaded !== true && contractsAccepted !== true && contractsUserUploaded === true"
          border-variant="primary"
        >
          <b-form-checkbox
            v-model="contractsUploaded"
            switch
            inline
            :disabled="isDisabled()"
          >
            <span style="font-size:1.5em;">Alle Dokumente gelesen und einverstanden</span>
          </b-form-checkbox>
          <!-- v-if="baseData.contractSignedAndUploaded !== true && contractsUploaded === false" -->
          <b-button
            v-if="contractsUploaded === true && contractsAccepted !== true"
            size="lg"
            variant="primary"
            class="mb-1 mb-sm-0 mr-0 mr-sm-1"
            style="margin-top: 30px;"
            type="button"
            block
            :disabled="showLoading"
            @click="contractsUploadedStatus"
          >
            <b-spinner
              v-if="showLoading"
            />
            <div v-if="showLoading === false">
              unterfertigte Vertragsunterlagen an solar.family übermitteln
            </div>
          </b-button>
        </b-card>
        <b-card
          v-if="contractsAccepted === true"
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
                Vielen Dank für die Übermittlung der Vertragsunterlagen. Nach der erfolgreichen Prüfung der Unterlagen werden Sie von uns über die weitere Vorgehensweise zur Montage Ihrer Photovoltaik-Anlage informiert.
              </h3>
            </b-col>
          </b-row>
        </b-card>
        <b-card
          v-if="contractSignedAndUploaded === true"
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
                Vertragsunterlagen übermittelt
              </h3>
            </b-col>
          </b-row>
        </b-card>
      </b-col>
    </b-row>
    <br>
    <br>
    <br>
    <b-modal
      id="modal-uploadContract"
      title="Datei hochladen"
      ok-title="Schließen"
      ok-only
      no-close-on-backdrop
      no-close-on-esc
      size="lg"
      @ok="hadleFileUploadClose()"
      @close="hadleFileUploadClose()"
    >
      <!--@ok="hadleFileUploadClose"-->
      <file-pond
        ref="pondOne"
        name="fileUpload"
        label-idle="Dateien hier ablegen..."
        allow-multiple="true"
        accepted-file-types="image/jpeg, image/png, application/pdf"
        :server="{
          url: apiUrl,
          process: {
            url: `/user/upload-multipart-file/${fileContainerSelectedId}`,
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${accessToken}`
            },
          }
        }"
        allow-file-encode="true"
        allow-file-rename="true"
        allow-image-resize="true"
        image-resize-target-width="1280"
        image-resize-target-height="720"
        image-resize-mode="cover"
        :file-rename-function="fileRenameFunction"
      />
      <!--@processfile="handleProcessFile"-->
    </b-modal>
    <b-modal
      id="modal-previewImageContract"
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
      id="modal-previewPDFContract"
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
  BFormCheckbox,
  BSpinner,
  BModal,
  BImg,
  VBTooltip,
} from 'bootstrap-vue'
import { v4 as uuidv4 } from 'uuid'
import { base64StringToBlob } from 'blob-util'
import { localize } from 'vee-validate'

import vueFilePond from 'vue-filepond'

//  Import plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.esm'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.esm'
import FilePondPluginFileEncode from 'filepond-plugin-file-encode'
import FilePondPluginFileRename from 'filepond-plugin-file-rename'
import FilePondPluginImageResize from 'filepond-plugin-image-resize'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'

//  Import styles
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

import {
  ref,
  onUnmounted,
  computed,
  watch,
} from '@vue/composition-api'
import { $apiUrl } from '@serverConfig'

import { numberFormat, numberFormatDeep } from '@core/utils/localSettings'
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
    BFormCheckbox,
    BSpinner,
    BModal,
    BImg,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    containerSavingContract: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerContractBillingt: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerMandateCompletion: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerMandateBilling: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerMandateBillingNet: {
      type: Array,
      required: false,
      default: () => [],
    },
    containerSepa: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  data() {
    return {
      previewFile: '',
      loadingPreviewFile: false,
      apiUrl: $apiUrl,
      showLoading: false,
      contractsUploaded: false,
      contractsAccepted: false,
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

    contractsUploadedStatus() {
      this.$bvModal
        .msgBoxConfirm('unterfertigte Vertragsunterlagen an solar.family übermitteln', {
          title: '',
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
            store.dispatch('app-power-plant/simpleStatusUpdateFrontend', { id: router.currentRoute.params.plantId, status: 'contractSignedAndUploaded', value: true })
              .then(() => {
                this.showLoading = false
                //  this.baseData.orderInterestAccepted = true
                this.contractsAccepted = true
              })
          }
        })
    },
    fileRenameFunction(file) {
      const authUser = JSON.parse(localStorage.getItem('userData'))

      let fileName = `${authUser.firstName.toLowerCase()}_${authUser.lastName.toLowerCase()}_`
      const documentType = this.fileContainersData.find(x => x.id === this.fileContainerSelectedId).type

      if (documentType === 24) {
        fileName = `${fileName}vertrag_energieeinsparung`
      } else if (documentType === 25) {
        fileName = `${fileName}vertrag_verrechnungsblatt`
      } else if (documentType === 27) {
        fileName = `${fileName}vollmacht_abwicklung`
      } else if (documentType === 29) {
        fileName = `${fileName}vollmacht_energieabrechnung`
      } else if (documentType === 291) {
        fileName = `${fileName}vollmacht_netzbetreiber`
      }

      fileName = `${fileName}_${uuidv4()}`

      return `${fileName}${file.extension}`
    },
    isDisabled() {
      return false
      //  not defined yet
      //  return this.$props.baseData.solarPlantFilesVerifiedByBackendUser
    },
    deleteFile(id, containerId) {
      this.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie die Datei löschen möchten.', {
          title: 'Datei löschen?',
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
            store.dispatch('app-power-plant/deleteFile', { id })
              .then(response => {
                console.log(response)
                /* eslint-disable no-param-reassign */
                //  this.$props.fileContainersData.find(x => x.id === response.data.payload.message).rs = response.data.payload.status
                /* eslint-ebable no-param-reassign */

                store.dispatch('app-power-plant/getDocumentFiles', { id: containerId })
                  .then(response1 => {
                    /* eslint-disable no-param-reassign */
                    //  this.$props.fileContainersData.find(x => x.id === containerId).files = response1.data.payload
                    this.$props.containerFiles = response1.data.payload
                    /* eslint-ebable no-param-reassign */
                    //  containerFiles.value = response1.data.payload

                    //  this should be optimized! -- being reasonable with tasks and available time would be much appreciated in development!!!
                  })
                  .catch(error => {
                    if (error.status === 404) {
                      /* eslint-disable no-console */
                      console.log('loadfiles error response')
                      /* eslint-enable no-console */
                    }
                  })
              })
              .catch(error => {
                if (error.status === 404) {
                  /* eslint-disable no-console */
                  console.log('updateDocument error response')
                  /* eslint-enable no-console */
                }
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

    const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginFileEncode, FilePondPluginFileRename, FilePondPluginImageResize, FilePondPluginImageTransform)

    const fileContainersData = ref([])
    const fileContainerSelectedId = ref('')
    const contractSignedAndUploaded = ref(router.currentRoute.params.status)
    //  const contractsDownloaded = ref(false)
    const contractsUserUploaded = ref(false)

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

    const filterFiles = data => {
      let skip = false
      const files = []

      data.forEach(file => {
        if (file.backendUserUpload === false) {
          files.push(file)
        }

        if (file.backendUserUpload === true || file.generated === true) {
          if (skip === false) {
            files.push(file)
            skip = true
          }
        }
      })

      return files
    }

    //  temp prototype -- in a hurry as always
    const loadDocumentFiles = type => {
      store.dispatch('app-power-plant/getDocumentFiles', { id: fileContainersData.value.filter(c => (c.type === type))[0].id })
        .then(response => {
          /* eslint-disable no-param-reassign */
          if (type === 24) {
            props.containerSavingContract = filterFiles(response.data.payload)
          } else if (type === 25) {
            props.containerContractBillingt = filterFiles(response.data.payload)
          } else if (type === 27) {
            props.containerMandateCompletion = filterFiles(response.data.payload)
          } else if (type === 29) {
            props.containerMandateBilling = filterFiles(response.data.payload)
          } else if (type === 291) {
            props.containerMandateBillingNet = filterFiles(response.data.payload)
          } else if (type === 201) {
            props.containerSepa = filterFiles(response.data.payload)
          }

          /* eslint-ebable no-param-reassign */
        })
    }

    const setContainerByTypeId = type => {
      const container = fileContainersData.value.filter(c => (c.type === type))[0]
      fileContainerSelectedId.value = container.id
    }

    store.dispatch('app-power-plant/fetchFileContainers', { id: router.currentRoute.params.plantId })
      .then(response => {
        fileContainersData.value = response.data.payload
        loadDocumentFiles(24)
        loadDocumentFiles(25)
        loadDocumentFiles(27)
        loadDocumentFiles(29)
        loadDocumentFiles(291)
        loadDocumentFiles(201)
      })

    /*
    store.dispatch('app-power-plant/plantContractStatus', { id: router.currentRoute.params.plantId })
      .then(response => {
        console.log(response.data)
      })
    */

    /*
    const getContractDownloadStatus = () => {
      setTimeout(() => {
        store.dispatch('app-power-plant/getContractDownloadStatus', { id: router.currentRoute.params.plantId })
          .then(response => {
            if (response.data.status === 200) {
              contractsDownloaded.value = true
            }
          })
      }, 1000)
    }
    */

    //  getContractDownloadStatus()

    const getContractUploadStatus = () => {
      setTimeout(() => {
        store.dispatch('app-power-plant/getContractUploadStatus', { id: router.currentRoute.params.plantId })
          .then(response => {
            if (response.data.status === 200) {
              contractsUserUploaded.value = true
            }
          })
      }, 1000)
    }

    getContractUploadStatus()

    const hadleFileUploadClose = () => {
      loadDocumentFiles(fileContainersData.value.filter(c => (c.id === fileContainerSelectedId.value))[0].type)
      getContractUploadStatus()
    }

    const getBtnColor = item => {
      if (item.downloadedByUser === true) {
        return 'outline-primary'
      }

      if (item.backendUserUpload !== true && item.generated !== true) {
        return 'outline-primary'
      }

      return 'primary'
    }

    const setBtnColor = item => {
      item.downloadedByUser = true
    }

    const userDocumentFields = [
      {
        key: 'icon',
        label: 'Info',
        formatter: (value, key, item) => item.fileContentType,
        thStyle: { width: '120px !important' },
      },
      { key: 'fileName', label: 'Datei' },
      { key: 't0', label: 'Erstellt am', thStyle: { width: '150px !important' } },
      { key: 'actions', label: 'Aktionen', thStyle: { width: '350px !important', textAlign: 'right', paddingRight: '70px' } },
    ]

    return {
      userDocumentFields,
      setContainerByTypeId,
      hadleFileUploadClose,
      fileContainerSelectedId,
      FilePond,
      fileContainersData,
      contractSignedAndUploaded,
      //  getContractDownloadStatus,
      //  contractsDownloaded,
      contractsUserUploaded,
      isMobile,
      getBtnColor,
      setBtnColor,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
