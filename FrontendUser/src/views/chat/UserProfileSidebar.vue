<template>
  <div
    class="chat-profile-sidebar"
    :class="{'show': shallShowUserProfileSidebar}"
  >
    <!-- Header -->
    <header class="chat-profile-header">
      <span class="close-icon">
        <feather-icon
          icon="XIcon"
          @click="$emit('close-sidebar')"
        />
      </span>

      <div class="header-profile-sidebar">
        <div class="avatar box-shadow-1 avatar-xl avatar-border">
          <b-avatar
            size="70"
            :text="profileUserData.avatar"
            style="background-color:red"
          />
          <span
            class="avatar-status-xl"
            :class="`avatar-status-${profileUserData.status}`"
          />
        </div>
        <h4 class="chat-user-name">
          {{ profileUserData.fullName }}
        </h4>
        <span class="user-post text-capitalize">{{ profileUserData.role }}</span>
      </div>
    </header>

    <!-- User Details -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="profile-sidebar-area scroll-area"
    >

      <!-- About -->
      <h6 class="section-label mb-1">
        {{ $t('About') }}
      </h6>
      <div class="about-user">
        <b-form-textarea
          v-model="profileUserData.about"
          :placeholder="$t('Something about yourself...')"
          rows="5"
        />
      </div>

      <!-- Status -->
      <h6 class="section-label mb-1 mt-3">
        {{ $t('Status') }}
      </h6>
      <b-form-radio-group
        id="user-status-options"
        v-model="profileUserData.status"
        stacked
      >
        <!-- :options="userStatusOptions" -->
        <b-form-radio
          v-for="option in userStatusOptions"
          :key="option.value"
          :value="option.value"
          :class="`custom-control-${option.radioVariant}`"
        >
          {{ option.text }}
        </b-form-radio>
      </b-form-radio-group>

      <!-- Settings -->
      <h6 class="section-label mb-1 mt-2">
        {{ $t('Settings') }}
      </h6>
      <ul
        v-if="profileUserData.settings"
        class="list-unstyled"
      >

        <!-- Two Step Auth -->
        <li class="d-flex justify-content-between align-items-center mb-1">
          <div class="d-flex align-items-center">
            <feather-icon
              icon="CheckSquareIcon"
              size="18"
              class="mr-75"
            />
            <span class="align-middle">{{ $t('Two-step Verification') }}</span>
          </div>
          <b-form-checkbox
            v-model="profileUserData.settings.isTwoStepAuthVerificationEnabled"
            switch
          />
        </li>

        <!-- Notifications -->
        <li class="d-flex justify-content-between align-items-center mb-1">
          <div class="d-flex align-items-center">
            <feather-icon
              icon="BellIcon"
              size="18"
              class="mr-75"
            />
            <span class="align-middle">{{ $t('Notification') }}</span>
          </div>
          <b-form-checkbox
            v-model="profileUserData.settings.isNotificationsOn"
            switch
          />
        </li>

        <!-- Invite Friends -->
        <li class="mb-1 d-flex align-items-center cursor-pointer">
          <feather-icon
            icon="UserIcon"
            class="mr-75"
            size="18"
          />
          <span class="align-middle">{{ $t('Invite Friends') }}</span>
        </li>

        <!-- Delete Account -->
        <li class="d-flex align-items-center cursor-pointer">
          <feather-icon
            icon="TrashIcon"
            class="mr-75"
            size="18"
          />
          <span class="align-middle">{{ $t('Delete Account') }}</span>
        </li>
      </ul>

      <div class="mt-3">
        <b-button variant="primary">
          {{ $t('Logout') }}
        </b-button>
      </div>
    </vue-perfect-scrollbar>
  </div>
</template>

<script>
import {
  BAvatar, BFormTextarea, BFormRadioGroup, BFormRadio, BFormCheckbox, BButton,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'

export default {
  components: {
    BAvatar,
    BFormTextarea,
    BFormRadioGroup,
    BFormRadio,
    BFormCheckbox,
    BButton,
    VuePerfectScrollbar,
  },
  props: {
    shallShowUserProfileSidebar: {
      type: Boolean,
      required: true,
    },
    profileUserData: {
      type: Object,
      required: true,
    },

  },
  setup() {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const userStatusOptions = [
      { text: 'Active', value: 'online', radioVariant: 'success' },
      { text: 'Do Not Disturb', value: 'busy', radioVariant: 'danger' },
      { text: 'Away', value: 'away', radioVariant: 'warning' },
      { text: 'Offline', value: 'offline', radioVariant: 'secondary' },
    ]

    return {
      perfectScrollbarSettings,
      userStatusOptions,
    }
  },
}
</script>

<style lang="scss" scoped>
#user-status-options ::v-deep {
  .custom-radio {
    margin-bottom: 1rem;
  }
}
</style>
