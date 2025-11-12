<template>
  <aside
    class="app-sidebar"
    :class="sidebarClasses"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <router-link to="/" class="sidebar-logo">
        <i class="pi pi-bolt logo-icon"></i>
        <Transition name="fade">
          <span v-if="!isCollapsed" class="logo-text">Admin Panel</span>
        </Transition>
      </router-link>

      <!-- Collapse Toggle for Desktop -->
      <Button
        v-if="!appStore.isMobile"
        icon="pi pi-bars"
        class="sidebar-toggle"
        text
        rounded
        @click="appStore.toggleSidebar"
      />

      <!-- Close Button for Mobile -->
      <Button
        v-else
        icon="pi pi-times"
        class="sidebar-close"
        text
        rounded
        @click="appStore.toggleSidebarMobile"
      />
    </div>

    <!-- User Profile Section -->
    <div class="sidebar-profile">
      <Avatar
        v-if="authStore.user?.avatar_url"
        :image="authStore.user.avatar_url"
        class="profile-avatar"
        size="large"
        shape="circle"
      />
      <Avatar
        v-else
        :label="authStore.userInitials"
        :style="{ backgroundColor: getAvatarColor(authStore.user?.name || ''), color: '#fff' }"
        class="profile-avatar"
        size="large"
        shape="circle"
      />
      <Transition name="fade">
        <div v-if="!isCollapsed" class="profile-info">
          <div class="profile-name">{{ authStore.user?.name || 'User' }}</div>
          <div class="profile-role">{{ getUserRoleDisplay() }}</div>
        </div>
      </Transition>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="sidebar-scroll">
        <!-- Main Menu -->
        <div class="menu-section">
          <Transition name="fade">
            <div v-if="!isCollapsed" class="menu-section-title">
              <i class="pi pi-th-large"></i>
              {{ $t('sidebar.menu') || 'Menu' }}
            </div>
          </Transition>
          <ul class="menu-list">
            <li
              v-for="item in menuItems"
              :key="item.key"
              class="menu-item"
            >
              <SidebarMenuItem
                :item="item"
                :collapsed="isCollapsed"
                :active="isActiveRoute(item)"
              />
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <Button
        v-tooltip.right="$t('sidebar.help') || 'Help'"
        icon="pi pi-question-circle"
        class="footer-button"
        text
        rounded
        @click="openHelp"
      />
    </div>
  </aside>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import SidebarMenuItem from './SidebarMenuItem.vue'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const appStore = useAppStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()

// State
const hoverExpand = ref(false)

// Define menu item interface
interface MenuItem {
  key: string
  label: string
  icon: string
  to?: string
  badge?: string | null
  badgeClass?: string
  items?: MenuItem[]
}

// Role-based menu items
const menuItems = computed<MenuItem[]>(() => {
  const role = authStore.user?.role || 'user'

  // Admin menu items
  if (role === 'admin') {
    return [
      {
        key: 'admin-dashboard',
        label: 'Dashboard',
        icon: 'pi pi-chart-bar',
        to: '/admin/dashboard',
      },
      {
        key: 'admin-users',
        label: 'Users',
        icon: 'pi pi-users',
        to: '/admin/users',
      },
      {
        key: 'admin-solar-plants',
        label: 'Solar Plants',
        icon: 'pi pi-sun',
        to: '/admin/solar-plants',
      },
      {
        key: 'admin-investments',
        label: 'Investments',
        icon: 'pi pi-wallet',
        to: '/admin/investments',
      },
      {
        key: 'notifications',
        label: 'Notifications',
        icon: 'pi pi-bell',
        to: '/notifications',
      }
    ]
  }

  // Manager menu items
  if (role === 'manager') {
    return [
      {
        key: 'manager-dashboard',
        label: 'Dashboard',
        icon: 'pi pi-briefcase',
        to: '/manager/dashboard',
      },
      {
        key: 'manager-solar-plants',
        label: 'Solar Plants',
        icon: 'pi pi-sun',
        to: '/admin/solar-plants',
      },
      {
        key: 'manager-investments',
        label: 'Investments',
        icon: 'pi pi-wallet',
        to: '/admin/investments',
      },
      {
        key: 'notifications',
        label: 'Notifications',
        icon: 'pi pi-bell',
        to: '/notifications',
      }
    ]
  }

  // Regular user menu items
  return [
    {
      key: 'user-dashboard',
      label: 'Dashboard',
      icon: 'pi pi-home',
      to: '/user/dashboard',
    },
    {
      key: 'notifications',
      label: 'Notifications',
      icon: 'pi pi-bell',
      to: '/notifications',
    }
  ]
})

// Computed properties
const isCollapsed = computed(() => {
  if (appStore.isMobile) return false
  return !appStore.isSidebarOpen && !hoverExpand.value
})

const sidebarClasses = computed(() => ({
  'sidebar-collapsed': isCollapsed.value,
  'sidebar-expanded': !isCollapsed.value,
  'sidebar-mobile': appStore.isMobile,
  'sidebar-mobile-active': appStore.isSidebarMobileOpen,
  'sidebar-dark': themeStore.isDark,
  'sidebar-light': !themeStore.isDark,
  'sidebar-hoverable': !appStore.isSidebarOpen && !appStore.isMobile
}))

// Methods
const handleMouseEnter = () => {
  if (!appStore.isSidebarOpen && !appStore.isMobile) {
    hoverExpand.value = true
  }
}

const handleMouseLeave = () => {
  if (!appStore.isSidebarOpen && !appStore.isMobile) {
    hoverExpand.value = false
  }
}

const isActiveRoute = (item: any): boolean => {
  if (item.to) {
    return route.path === item.to || route.path.startsWith(item.to + '/')
  }
  if (item.items) {
    return item.items.some((subItem: any) => isActiveRoute(subItem))
  }
  return false
}

const openHelp = () => {
  window.open('https://github.com/krecco/basicAdmin', '_blank')
}

const getUserRoleDisplay = () => {
  if (!authStore.user) return 'User'
  const role = authStore.user.role || 'user'

  const roleDisplayMap: Record<string, string> = {
    'admin': 'Administrator',
    'manager': 'Manager',
    'user': 'User'
  }

  return roleDisplayMap[role] || 'User'
}

// Avatar color generation
const getAvatarColor = (name: string): string => {
  const colors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4',
    '#FFEAA7', '#DDA5E9', '#FD79A8', '#A29BFE'
  ]
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}
</script>

<style lang="scss" scoped>
.app-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: var(--sidebar-width, 280px);
  background: var(--surface-section);
  border-right: 1px solid var(--surface-border);
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 999;
  overflow: hidden;

  // Glassmorphism effect
  &::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.1) 0%,
      rgba(255, 255, 255, 0.05) 100%
    );
    pointer-events: none;
  }

  // Collapsed state
  &.sidebar-collapsed {
    width: var(--sidebar-collapsed-width, 80px);

    .sidebar-header {
      justify-content: center;
      padding: 0.75rem 0.5rem;
      height: 3.5rem;
    }

    .sidebar-profile {
      padding: 1rem 0.5rem;
      justify-content: center;

      .profile-avatar {
        margin: 0;
      }
    }

    .menu-section-title {
      opacity: 0;
      visibility: hidden;
    }
  }

  // Mobile styles
  &.sidebar-mobile {
    transform: translateX(-100%);

    &.sidebar-mobile-active {
      transform: translateX(0);
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
  }

  // Hoverable state
  &.sidebar-hoverable:hover {
    width: var(--sidebar-width, 280px);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  }

  // Header
  .sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    height: 3.5rem;
    border-bottom: 1px solid var(--surface-border);

    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      color: var(--text-color);
      font-weight: 600;
      font-size: 1.25rem;

      .logo-icon {
        font-size: 1.5rem;
        color: var(--primary-color);
      }
    }

    .sidebar-toggle,
    .sidebar-close {
      flex-shrink: 0;
    }
  }

  // Profile
  .sidebar-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-bottom: 1px solid var(--surface-border);

    .profile-avatar {
      flex-shrink: 0;
    }

    .profile-info {
      flex: 1;
      min-width: 0;

      .profile-name {
        font-weight: 600;
        color: var(--text-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .profile-role {
        font-size: 0.875rem;
        color: var(--text-color-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
  }

  // Navigation
  .sidebar-nav {
    flex: 1;
    overflow: hidden;

    .sidebar-scroll {
      height: 100%;
      overflow-y: auto;
      overflow-x: hidden;

      // Custom scrollbar styling
      &::-webkit-scrollbar {
        width: 6px;
      }

      &::-webkit-scrollbar-track {
        background: transparent;
      }

      &::-webkit-scrollbar-thumb {
        background: var(--surface-border);
        border-radius: 3px;
        opacity: 0;
        transition: opacity 0.2s;
      }

      &:hover::-webkit-scrollbar-thumb {
        opacity: 1;
        background: var(--surface-400);
      }
    }

    .menu-section {
      padding: 0.5rem 0;

      .menu-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-color-secondary);
        letter-spacing: 0.05em;

        i {
          font-size: 0.875rem;
        }
      }
    }

    .menu-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
  }

  // Footer
  .sidebar-footer {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 1rem;
    border-top: 1px solid var(--surface-border);
  }

  // Dark mode
  &.sidebar-dark {
    background: #1e293b;

    &::before {
      background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.05) 0%,
        rgba(255, 255, 255, 0.02) 100%
      );
    }
  }
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
