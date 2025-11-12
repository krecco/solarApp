<template>

  <div>

    <user-list-add-new
      :is-add-new-user-sidebar-active.sync="isAddNewUserSidebarActive"
      @refetch-data="refetchData"
    />

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
    >

      <div class="m-2">

        <!-- Table Top -->
        <b-row>
          <!-- Per Page -->
          <b-col
            cols="12"
            md="3"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>Einträge anzeigen</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="4"
            :class="filterTypeClass()"
          >
            <label>Typ</label>
            <v-select
              v-model="customerType"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="customerTypeOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
              style="min-width:220px !important;"
            />
          </b-col>
          <b-col
            cols="12"
            md="5"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Suche..."
              />
              <b-button
                variant="primary"
                @click="isAddNewUserSidebarActive = true"
              >
                <span class="text-nowrap">Kunden hinzufügen</span>
              </b-button>
            </div>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchUsers"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="Keine passenden Einträge gefunden"
        style="min-height:200px"
        :sort-desc.sync="isSortDirDesc"
      >

        <template #cell(avatar)="data">
          <b-avatar
            :text="avatarText(fullName(data.item.lastName,data.item.firstName))"
            variant="success"
          />
          <b-link
            :to="{ name: 'user-detail', params: { id: data.item.id } }"
            class="font-weight-bold d-block text-nowrap"
          />
        </template>

        <template #cell(street)="data">
          {{ data.value }}<br>
          {{ data.item.postNr }} {{ data.item.city }}
        </template>

        <template #cell(customerType)="data">
          <div v-if="data.item.customerType == 30">
            <b-badge
              pill
              variant="light-primary"
            >
              <feather-icon
                icon="ZapIcon"
                size="19"
              />
            </b-badge>
            <b-badge
              pill
              variant="light-info"
            >
              <feather-icon
                icon="DollarSignIcon"
                size="19"
              />
            </b-badge>
          </div>
          <div v-if="data.item.customerType == 10">
            <b-badge
              pill
              variant="light-primary"
            >
              <feather-icon
                icon="ZapIcon"
                size="19"
              />
            </b-badge>
          </div>
          <div v-if="data.item.customerType == 20">
            <b-badge
              pill
              variant="light-info"
            >
              <feather-icon
                icon="DollarSignIcon"
                size="19"
              />
            </b-badge>
          </div>
        </template>

        <template #cell(lastName)="data">
          <b-link
            :to="{ name: 'user-detail', params: { id: data.item.id } }"
            class="font-weight-bold d-block text-nowrap"
          >
            {{ data.item.lastName }} {{ data.item.firstName }}
          </b-link>
        </template>

        <template #cell(t0)="data">
          {{ data.item.t0 | moment("DD.MM. YYYY") }}
        </template>

        <!-- Column: Actions -->
        <template #cell(actions)="data">
          <b-dropdown
            variant="link"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >

            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>
            <b-dropdown-item :to="{ name: 'user-detail', params: { id: data.item.id } }">
              <feather-icon icon="FileTextIcon" />
              <span class="align-middle ml-50">Mehr</span>
            </b-dropdown-item>

            <b-dropdown-item :to="{ name: 'user-edit', params: { id: data.item.id } }">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Bearbeiten</span>
            </b-dropdown-item>

            <b-dropdown-item @click="deleteUser(data.item)">
              <feather-icon icon="XCircleIcon" />
              <span class="align-middle ml-50">Kunde Löschen</span>
            </b-dropdown-item>

            <!--
            <b-dropdown-item>
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
            -->
          </b-dropdown>
        </template>

      </b-table>
      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">{{ dataMeta.from }} bis {{ dataMeta.to }} von {{ dataMeta.of }} Einträge</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalUsers"
              :per-page="perPage"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
              prev-class="prev-item"
              next-class="next-item"
            >
              <template #prev-text>
                <feather-icon
                  icon="ChevronLeftIcon"
                  size="18"
                />
              </template>
              <template #next-text>
                <feather-icon
                  icon="ChevronRightIcon"
                  size="18"
                />
              </template>
            </b-pagination>

          </b-col>

        </b-row>
      </div>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BLink,
  BDropdown, BDropdownItem, BPagination, BAvatar, BBadge,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import store from '@/store'
import useUsersList from './useUsersList'
import userStoreModule from '../userStoreModule'
import UserListAddNew from './UserListAddNew.vue'

export default {
  components: {
    UserListAddNew,

    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BDropdown,
    BDropdownItem,
    BPagination,
    BLink,
    BAvatar,
    BBadge,

    vSelect,
  },
  methods: {
    fullName(lastName, firstname) {
      return `${lastName} ${firstname}`
    },
    filterTypeClass() {
      let style = 'd-flex align-items-center justify-content-end mb-1 mb-md-0'
      if ((this.$store.getters['app/currentBreakPoint'] === 'xs') || (this.$store.getters['app/currentBreakPoint'] === 'sm')) {
        style = 'd-flex align-items-center justify-content-start mb-1 mb-md-0'
      }

      return style
    },
    deleteUser(user) {
      this.$bvModal
        .msgBoxConfirm('Bitte bestätigen Sie, dass Sie der Kunde löschen möchten.', {
          title: 'Kunde löschen?',
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
            store.dispatch('app-user/deleteUser', { userId: user.id })
              .then(response => {
                if (response.data.status === 200) {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Daten wurden gelöscht',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                  this.refetchData()
                } else {
                  this.$toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Daten wurden nicht gelöscht!',
                      text: response.data.payload.message,
                      icon: 'AlertTriangleIcon',
                      variant: 'warning',
                    },
                  })
                }
              })
              .catch(() => {
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Fehler bei der Verbindung zum Server',
                    icon: 'AlertTriangleIcon',
                    variant: 'warning',
                  },
                })
              })
          }
        })
    },
  },
  setup() {
    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const isAddNewUserSidebarActive = ref(false)

    const customerTypeOptions = [
      { label: 'Alle', value: -1 },
      { label: 'Interesent', value: 0 },
      { label: 'PV-Anlage', value: 10 },
      { label: 'Investor', value: 20 },
      { label: 'PV-Anlage  + Investor', value: 30 },
    ]

    const {
      fetchUsers,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      customerType,
      refetchData,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,
    } = useUsersList()

    return {
      // Sidebar
      isAddNewUserSidebarActive,

      fetchUsers,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      customerType,
      refetchData,

      // Filter
      avatarText,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,
      customerTypeOptions,
    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
