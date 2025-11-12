<template>
  <div>
    <b-card>
      <b-row>
        <!-- User Info: Left col -->
        <b-col
          cols="21"
          xl="6"
          class="d-flex justify-content-between flex-column"
        >
          <!-- User Avatar & Action Buttons -->
          <div class="d-flex justify-content-start">
            <b-avatar
              :src="userData.avatar"
              :text="avatarText(userData.fullName)"
              variant="light-primary"
              size="104px"
              rounded
            />
            <div class="d-flex flex-column ml-1">
              <div class="mb-1">
                <h4 class="mb-0">
                  {{ userData.firstName }} {{ userData.lastName }}
                </h4>
                <span class="card-text">{{ userData.email }}</span>
              </div>
              <div class="d-flex flex-wrap">
                <b-button
                  :to="{ name: 'user-edit', params: { id: userData.id } }"
                  variant="primary"
                >
                  Bearbeiten
                </b-button>
              </div>
            </div>
          </div>

          <!-- User Stats -->
          <!--
          <div class="d-flex align-items-center mt-2">
            <div class="d-flex align-items-center">
              <b-avatar
                variant="light-success"
                rounded
              >
                <feather-icon
                  icon="TrendingUpIcon"
                  size="18"
                />
              </b-avatar>
              <div class="ml-1">
                <h5 class="mb-0">
                  12.8 kWa
                </h5>
                <small>Monthly Production</small>
              </div>
            </div>
          </div>
          -->
        </b-col>

        <!-- Right Col: Table -->
        <b-col
          cols="12"
          xl="6"
        >
          <table class="mt-2 mt-xl-0 w-100">
            <tr>
              <th class="pb-50">
                <feather-icon
                  icon="UserIcon"
                  class="mr-75"
                />
                <span class="font-weight-bold">Benutzername</span>
              </th>
              <td class="pb-50">
                {{ userData.email }}
              </td>
            </tr>
            <tr>
              <th>
                <feather-icon
                  icon="PhoneIcon"
                  class="mr-75"
                />
                <span class="font-weight-bold">Telefon</span>
              </th>
              <td>
                {{ userData.phoneNr }}
              </td>
            </tr>
            <tr>
              <th>
                <feather-icon
                  icon="MapIcon"
                  class="mr-75"
                />
                <span class="font-weight-bold">Adresse</span>
              </th>
              <td>
                <br>
                {{ addressData.street }}<br>
                {{ addressData.postNr }} {{ addressData.city }}
              </td>
            </tr>
          </table>
        </b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BButton, BAvatar, BRow, BCol,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import useUsersList from '../users-list/useUsersList'

export default {
  components: {
    BCard, BButton, BRow, BCol, BAvatar,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
    addressData: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const { resolveUserRoleVariant } = useUsersList()
    return {
      avatarText,
      resolveUserRoleVariant,
    }
  },
}
</script>

<style>

</style>
