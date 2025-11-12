<template>
  <!-- Backdrop overlay for dropdowns -->
  <div
    v-if="showBackdrop"
    class="dropdown-backdrop"
    @click="closeAllDropdowns"
  ></div>

  <header class="app-header">
    <div class="header-content">
      <!-- Left Section -->
      <div class="header-left">
        <!-- Mobile Menu Toggle -->
        <Button
          v-if="appStore.isMobile"
          icon="pi pi-bars"
          class="menu-toggle"
          text
          rounded
          @click="appStore.toggleSidebarMobile"
        />
      </div>

      <!-- Right Section -->
      <div class="header-right">
        <!-- Theme Color Picker -->
        <Button
          v-tooltip.bottom="tooltips.theme"
          icon="pi pi-palette"
          class="header-button"
          text
          rounded
          @click="toggleThemeMenu"
        />
        
        <!-- Language Selector -->
        <Button
          v-tooltip.bottom="tooltips.language"
          icon="pi pi-globe"
          class="header-button"
          text
          rounded
          @click="toggleLanguageMenu"
        />
        
        <!-- Notifications -->
        <div class="notification-wrapper">
          <Button
            v-tooltip.bottom="tooltips.notifications"
            icon="pi pi-bell"
            class="header-button notification-button"
            text
            rounded
            severity="secondary"
            :badge="notificationsStore.unreadCount > 0 ? notificationsStore.unreadCount.toString() : null"
            badgeSeverity="danger"
            @click="toggleNotifications"
          />
          <NotificationsPanel
            ref="notificationsPanel"
            :notifications="notifications"
            @markAllAsRead="markAllAsRead"
            @selectNotification="openNotification"
            @markAsRead="markAsRead"
            @viewAll="viewAllNotifications"
            @hide="showBackdrop = false"
          />
        </div>
        
        <!-- User Menu -->
        <div class="user-menu-wrapper">
          <Button
            class="user-menu-trigger"
            text
            @click="toggleUserMenu"
            aria-haspopup="true"
            aria-controls="user-menu"
          >
            <Avatar
              v-if="authStore.user?.avatar_url"
              :image="authStore.user.avatar_url"
              shape="circle"
              class="user-avatar"
            />
            <Avatar
              v-else
              :label="authStore.userInitials"
              :style="{ backgroundColor: getAvatarColor(authStore.user?.name || ''), color: '#fff' }"
              shape="circle"
              class="user-avatar"
            />
          </Button>
          <UserMenuPanel
            ref="userMenuPanel"
            :user="authStore.user"
            :menuItems="userMenuItems"
            @signOut="signOut"
            @hide="showBackdrop = false"
          />
        </div>
      </div>
    </div>
    
    <!-- Theme Menu -->
    <Popover ref="themeMenu" class="theme-menu-panel" @hide="showBackdrop = false">
      <div class="theme-menu-content">
        <h6 class="menu-title">{{ t('header.primaryColor') }}</h6>
        <div class="color-palette">
          <button
            v-for="color in colorOptions"
            :key="color.value"
            class="color-option"
            :class="{ active: themeStore.primaryColor === color.value }"
            :style="{ backgroundColor: color.hex }"
            @click="setPrimaryColor(color.value)"
            :title="color.label"
          >
            <i v-if="themeStore.primaryColor === color.value" class="pi pi-check"></i>
          </button>
        </div>
      </div>
    </Popover>
    
    <!-- Language Menu -->
    <Popover ref="languageMenu" class="language-menu-panel" @hide="showBackdrop = false">
      <div class="language-menu-content">
        <h6 class="menu-title">{{ t('header.selectLanguage') }}</h6>
        <div class="language-list">
          <button
            v-for="lang in languages"
            :key="lang.code"
            class="language-item"
            :class="{ active: locale === lang.code }"
            @click="setLanguage(lang.code)"
          >
            <span class="language-flag">{{ lang.flag }}</span>
            <span class="language-name">{{ lang.name }}</span>
            <i v-if="locale === lang.code" class="pi pi-check check-icon"></i>
          </button>
        </div>
      </div>
    </Popover>
    
    <!-- Command Palette Dialog -->
    <Dialog
      v-model:visible="commandPaletteVisible"
      modal
      :closable="false"
      :showHeader="false"
      class="command-palette-dialog"
      :pt="{
        mask: { 
          class: 'command-palette-mask',
          style: 'backdrop-filter: blur(4px); background: rgba(0, 0, 0, 0.6);'
        }
      }"
    >
      <CommandPalette @close="commandPaletteVisible = false" />
    </Dialog>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLocale } from '@/composables/useLocale'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Popover from 'primevue/popover'
import Divider from 'primevue/divider'
import Dialog from 'primevue/dialog'
import NotificationsPanel from '@/components/notifications/NotificationsPanel.vue'
import UserMenuPanel from './UserMenuPanel.vue'
import CommandPalette from './CommandPalette.vue'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const { t, locale, changeLocale } = useLocale()
const appStore = useAppStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const notificationsStore = useNotificationsStore()

// Refs
const themeMenu = ref()
const languageMenu = ref()
const notificationsPanel = ref()
const userMenuPanel = ref()

// State
const commandPaletteVisible = ref(false)
const showBackdrop = ref(false)

// Computed for tooltips
const tooltips = computed(() => ({
  createNew: t('header.createNew'),
  theme: t('header.theme'),
  language: t('header.language'),
  notifications: t('header.notifications')
}))

// Format notifications for the panel
const notifications = computed(() => {
  return notificationsStore.notifications.slice(0, 10).map(notification => ({
    id: notification.id,
    icon: getNotificationIcon(notification.type),
    type: notification.type,
    title: notification.title,
    message: notification.message,
    time: formatNotificationTime(notification.created_at),
    read: notification.is_read
  }))
})

// Helper to get icon for notification type
const getNotificationIcon = (type: string): string => {
  const iconMap: Record<string, string> = {
    success: 'pi pi-check-circle',
    info: 'pi pi-info-circle',
    warning: 'pi pi-exclamation-triangle',
    error: 'pi pi-times-circle'
  }
  return iconMap[type] || 'pi pi-bell'
}

// Helper to format notification time
const formatNotificationTime = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

  if (diffInSeconds < 60) {
    return t('notifications.timeJustNow')
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60)
    return t('notifications.timeMinutesAgo', { count: minutes })
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return t('notifications.timeHoursAgo', { count: hours })
  } else {
    const days = Math.floor(diffInSeconds / 86400)
    return t('notifications.timeDaysAgo', { count: days })
  }
}

// Color options for theme
const colorOptions = computed(() => [
  { value: 'blue', hex: '#3B82F6', label: t('header.colors.blue') },
  { value: 'green', hex: '#10B981', label: t('header.colors.green') },
  { value: 'purple', hex: '#8B5CF6', label: t('header.colors.purple') },
  { value: 'red', hex: '#EF4444', label: t('header.colors.red') },
  { value: 'orange', hex: '#F97316', label: t('header.colors.orange') },
  { value: 'teal', hex: '#14B8A6', label: t('header.colors.teal') },
  { value: 'pink', hex: '#EC4899', label: t('header.colors.pink') },
  { value: 'indigo', hex: '#6366F1', label: t('header.colors.indigo') }
])

// Languages
const languages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'es', name: 'Español', flag: '🇪🇸' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'de', name: 'Deutsch', flag: '🇩🇪' }
]

// User menu items - simplified for basic admin
const userMenuItems = computed(() => {
  const items = [
    {
      label: t('header.settings') || 'Settings',
      icon: 'pi pi-cog',
      command: () => {
        // Settings not implemented yet
        console.log('Settings - to be implemented')
      }
    },
    {
      label: t('header.help') || 'Help',
      icon: 'pi pi-question-circle',
      command: () => window.open('https://github.com/krecco/basicAdmin', '_blank')
    }
  ]

  return items
})

// Methods
const openCommandPalette = () => {
  commandPaletteVisible.value = true
}

const openCreateMenu = () => {
  // Implement create menu
  console.log('Open create menu')
}

const closeAllDropdowns = () => {
  themeMenu.value?.hide()
  languageMenu.value?.hide()
  notificationsPanel.value?.hide()
  userMenuPanel.value?.hide()
  showBackdrop.value = false
}

const toggleThemeMenu = (event: Event) => {
  // Close other menus but keep track if theme menu is currently open
  const isCurrentlyOpen = themeMenu.value?.overlayVisible
  languageMenu.value?.hide()
  notificationsPanel.value?.hide()
  userMenuPanel.value?.hide()

  // Toggle the menu
  themeMenu.value.toggle(event)

  // Show backdrop if opening, hide if closing
  setTimeout(() => {
    showBackdrop.value = !isCurrentlyOpen
  }, 0)
}

const toggleLanguageMenu = (event: Event) => {
  const isCurrentlyOpen = languageMenu.value?.overlayVisible
  themeMenu.value?.hide()
  notificationsPanel.value?.hide()
  userMenuPanel.value?.hide()

  languageMenu.value.toggle(event)

  setTimeout(() => {
    showBackdrop.value = !isCurrentlyOpen
  }, 0)
}

const toggleNotifications = (event: Event) => {
  const isCurrentlyOpen = notificationsPanel.value?.panel?.overlayVisible
  themeMenu.value?.hide()
  languageMenu.value?.hide()
  userMenuPanel.value?.hide()

  notificationsPanel.value.toggle(event)

  setTimeout(() => {
    showBackdrop.value = !isCurrentlyOpen
  }, 0)
}

const toggleUserMenu = (event: Event) => {
  const isCurrentlyOpen = userMenuPanel.value?.panel?.overlayVisible
  themeMenu.value?.hide()
  languageMenu.value?.hide()
  notificationsPanel.value?.hide()

  userMenuPanel.value.toggle(event)

  setTimeout(() => {
    showBackdrop.value = !isCurrentlyOpen
  }, 0)
}

const setPrimaryColor = (color: string) => {
  themeStore.setPrimaryColor(color)
  themeMenu.value.hide()
  showBackdrop.value = false
}

const setLanguage = async (lang: string) => {
  try {
    await changeLocale(lang as any)
    languageMenu.value.hide()
    showBackdrop.value = false

    // Sync with backend if authenticated
    if (authStore.isAuthenticated) {
      await authStore.updateUserPreferences({ language: lang })
    }
  } catch (error) {
    console.error('Failed to change language:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await notificationsStore.markAllAsRead()
    notificationsPanel.value.hide()
    showBackdrop.value = false
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const markAsRead = async (notification: any) => {
  try {
    await notificationsStore.markAsRead(notification.id)
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const openNotification = async (notification: any) => {
  await markAsRead(notification)
  notificationsPanel.value.hide()
  showBackdrop.value = false

  // Navigate to action URL if available
  const realNotification = notificationsStore.notifications.find(n => n.id === notification.id)
  if (realNotification?.action_url) {
    router.push(realNotification.action_url)
  }
}

const viewAllNotifications = () => {
  notificationsPanel.value.hide()
  showBackdrop.value = false
  router.push('/notifications')
}

const signOut = async () => {
  userMenuPanel.value.hide()
  showBackdrop.value = false
  await authStore.logout()
  router.push('/auth/login')
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

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    openCommandPalette()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)

  // Fetch notifications if authenticated
  if (authStore.isAuthenticated) {
    notificationsStore.fetchNotifications({ per_page: 10 })
    notificationsStore.fetchUnreadCount()
    // Start polling for new notifications every 60 seconds
    notificationsStore.startPolling(60000)
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  // Stop polling when component unmounts
  notificationsStore.stopPolling()
})
</script>

<style lang="scss" scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid var(--surface-border);
  padding: 0 1.5rem;
  height: 3.5rem;
  background: var(--surface-0);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);

  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    gap: 1rem;
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    
    .menu-toggle {
      flex-shrink: 0;
    }
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    
    .header-button {
      position: relative;

      :deep(.p-badge) {
        position: absolute;
        top: 0.1rem;
        right: 0.4rem;
        min-width: 1rem;
        height: 1rem;
        font-size: 0.525rem;
        line-height: 1.25rem;
      }
    }
    
    .notification-button {
      position: relative;
    }
    
    .user-menu-trigger {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      border-radius: 0.5rem;
      
      &:hover {
        background: var(--surface-hover);
      }
      
      .user-avatar {
        width: 2rem;
        height: 2rem;
      }
      
      .user-name {
        font-weight: 500;
        color: var(--text-color);
        
        @media (max-width: 768px) {
          display: none;
        }
      }
      
      .user-chevron {
        font-size: 0.75rem;
        color: var(--text-color-secondary);
      }
    }
  }
}

// Theme Menu Panel Styles
:deep(.theme-menu-panel) {
  min-width: 420px;
  max-width: 460px;
  background: var(--surface-0) !important;
  border: 1px solid var(--surface-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15),
              0 10px 15px -3px rgba(0, 0, 0, 0.12),
              0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 0 0 1px rgba(0, 0, 0, 0.05);
}

.theme-menu-content {
  //padding: 1.25rem;

  .menu-title {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-color);
    letter-spacing: 0.05em;
  }

  .color-palette {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.625rem;

    .color-option {
      width: 2rem;
      height: 2rem;
      border-radius: 0.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;

      &::before {
        content: '';
        position: absolute;
        inset: 0;
        background: inherit;
        opacity: 0.1;
        transition: opacity 0.2s;
      }

      &:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);

        &::before {
          opacity: 0.2;
        }
      }

      &.active {
        border-color: var(--surface-0);
        box-shadow: 0 0 0 3px var(--surface-border);
      }

      i {
        color: white;
        font-size: 0.875rem;
        font-weight: bold;
        position: relative;
        z-index: 1;
      }
    }
  }
}

// Language Menu Panel Styles
:deep(.language-menu-panel) {
  min-width: 320px;
  max-width: 360px;
  background: var(--surface-0) !important;
  border: 1px solid var(--surface-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15),
              0 10px 15px -3px rgba(0, 0, 0, 0.12),
              0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 0 0 1px rgba(0, 0, 0, 0.05);
}

.language-menu-content {
  //padding: 1.25rem;

  .menu-title {
    margin: 0 0 0.875rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-color);
    letter-spacing: 0.05em;
  }

  .language-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .language-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.625rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    color: var(--text-color);

    &:hover {
      background: var(--surface-hover);
    }

    &.active {
      background: var(--primary-50);
      border-color: var(--primary-200);
      color: var(--primary-color);
    }

    .language-flag {
      font-size: 1.25rem;
      width: 1.5rem;
    }

    .language-name {
      flex: 1;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .check-icon {
      color: var(--primary-color);
      margin-left: auto;
    }
  }
}

// Command palette dialog styling
:deep(.command-palette-dialog) {
  width: 90vw;
  max-width: 650px;
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  
  .p-dialog-content {
    padding: 0;
    border-radius: 1rem;
    overflow: hidden;
  }
}

// Enhanced overlay mask with proper blur
:deep(.command-palette-mask) {
  animation: fadeIn 0.15s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(4px);
  }
}

// Backdrop overlay for dropdowns
.dropdown-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  z-index: 99;
  animation: backdropFadeIn 0.2s ease-in-out;
  cursor: pointer;
}

@keyframes backdropFadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
    -webkit-backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
  }
}
</style>