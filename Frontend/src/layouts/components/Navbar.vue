<template>
  <div class="navbar-container d-flex content align-items-center">

    <!-- Nav Menu Toggler -->
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item">
        <b-link
          class="nav-link"
          @click="toggleVerticalMenuActive"
        >
          <feather-icon
            icon="MenuIcon"
            size="21"
          />
        </b-link>
      </li>
    </ul>

    <b-navbar-nav class="nav align-items-center ml-auto">
      <!-- new msg indicator
      <new-message-indicator />
      &nbsp;&nbsp;
      -->
      <notification-dropdown />
      &nbsp;&nbsp;
      <language-switcher />
      &nbsp;&nbsp;
      <b-nav-item-dropdown
        right
        toggle-class="d-flex align-items-center dropdown-user-link"
        class="dropdown-user"
      >
        <template #button-content>
          <div class="d-sm-flex d-none user-nav">
            <p class="user-name font-weight-bolder mb-0">
              {{ userName }}
            </p>
          </div>
          <b-avatar
            size="40"
            variant="light-primary"
            class="badge-minimal"
          />
        </template>

        <b-dropdown-item
          link-class="d-flex align-items-center"
          @click="logout"
        >
          <feather-icon
            size="16"
            icon="LogOutIcon"
            class="mr-50"
          />
          <span>Abmelden</span>
        </b-dropdown-item>
      </b-nav-item-dropdown>
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav, BNavItemDropdown, BDropdownItem, BAvatar,
} from 'bootstrap-vue'
import useJwt from '@/auth/jwt/useJwt'
import { initialAbility } from '@/libs/acl/config'

//  import NewMessageIndicator from './NewMessageIndicator.vue'
import NotificationDropdown from './NotificationDropdown.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'

export default {
  components: {
    BLink,
    BNavbarNav,
    BNavItemDropdown,
    BDropdownItem,
    //  BDropdownDivider,
    BAvatar,
    NotificationDropdown,
    LanguageSwitcher,
    //  NewMessageIndicator,
  },
  props: {
    toggleVerticalMenuActive: {
      type: Function,
      default: () => {},
    },
  },
  data() {
    return {
      userName: JSON.parse(localStorage.getItem('userData')).fullName,
    }
  },
  methods: {
    logout() {
      useJwt.logout()

      //  Reset ability
      this.$ability.update(initialAbility)

      //  Redirect to login page
      this.$router.push({ name: 'login' })
    },
  },
}
</script>
