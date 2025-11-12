<template>
  <div class="app-layout" :class="layoutClasses">
    <!-- Overlay for mobile sidebar -->
    <Transition name="fade">
      <div
        v-if="appStore.isSidebarMobileOpen"
        class="layout-overlay"
        @click="appStore.toggleSidebarMobile"
      />
    </Transition>

    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content Area -->
    <div class="layout-main-container">
      <!-- Header -->
      <AppHeader />

      <!-- Main Content -->
      <main class="layout-main">
        <div class="layout-content">
          <RouterView v-slot="{ Component, route }">
            <Transition name="fade-slide" mode="out-in">
              <component :is="Component" :key="route.path" />
            </Transition>
          </RouterView>
        </div>
      </main>

      <!-- Footer -->
      <AppFooter />
    </div>

    <!-- Toast Notifications -->
    <Toast position="top-right" />

    <!-- Confirm Dialog -->
    <ConfirmDialog />

    <!-- Dynamic Dialog -->
    <DynamicDialog />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import DynamicDialog from 'primevue/dynamicdialog'
import { useAppStore } from '@/stores/app'
import { useThemeStore } from '@/stores/theme'

const router = useRouter()
const appStore = useAppStore()
const themeStore = useThemeStore()

// Computed layout classes
const layoutClasses = computed(() => ({
  'layout-sidebar-collapsed': !appStore.isSidebarOpen,
  'layout-sidebar-mobile-active': appStore.isSidebarMobileOpen,
  'layout-static': true,
  'layout-dark': themeStore.isDark,
  'layout-light': !themeStore.isDark,
  [`layout-theme-${themeStore.primaryColor}`]: true,
  'layout-mobile': appStore.isMobile,
  'layout-desktop': !appStore.isMobile
}))

// Handle window resize
const handleResize = () => {
  /*
  appStore.setMobile(window.innerWidth < 1024)
  if (!appStore.isMobile && appStore.sidebarMobileActive) {
    appStore.setSidebarMobileActive(false)
  }
    */
}

// Keyboard shortcuts  
const handleKeyboard = (e: KeyboardEvent) => {
  // Cmd/Ctrl + K for command palette
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    // Trigger command palette in sidebar
    const searchInput = document.querySelector('.sidebar-search .search-input') as HTMLInputElement
    const searchButton = document.querySelector('.search-button-collapsed') as HTMLButtonElement
    if (searchInput) {
      searchInput.click()
    } else if (searchButton) {
      searchButton.click()
    }
  }
  
  // Cmd/Ctrl + B for sidebar toggle
  if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
    e.preventDefault()
    appStore.toggleSidebar()
  }

}

// Watch for route changes
watch(() => router.currentRoute.value, () => {
  if (appStore.isSidebarMobileOpen) {
    appStore.setSidebarMobileOpen(false)
  }
})

// Lifecycle hooks
onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
  window.addEventListener('keydown', handleKeyboard)
  
  // Apply theme on mount
  themeStore.initTheme()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('keydown', handleKeyboard)
})
</script>

<style lang="scss" scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: var(--surface-ground);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

  // Layout overlay for mobile
  .layout-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 998;
    cursor: pointer;
  }

  // Main container
  .layout-main-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    
    // With sidebar
    @media (min-width: 1024px) {
      margin-left: var(--sidebar-width, 280px);
      
      .layout-sidebar-collapsed & {
        margin-left: var(--sidebar-collapsed-width, 80px);
      }
    }
    
    // Horizontal menu mode
    .layout-horizontal & {
      margin-left: 0;
    }
  }

  // Main content area
  .layout-main {
    flex: 1;
    padding: 1.5rem;
    
    @media (min-width: 768px) {
      padding: 2rem;
    }
    
    .layout-content {
      max-width: 100%;
      margin: 0 auto;
      
      // Optional: Constrain max width for better readability
      &.constrained {
        max-width: 1440px;
      }
    }
  }

  // Dark mode styles
  &.layout-dark {
    --surface-ground: #0f172a;
    --surface-section: #1e293b;
    --surface-card: #334155;
    --surface-overlay: #475569;
    --surface-border: #64748b;
    --text-color: #f1f5f9;
    --text-color-secondary: #cbd5e1;
  }

  // Light mode styles
  &.layout-light {
    --surface-ground: #f8fafc;
    --surface-section: #ffffff;
    --surface-card: #ffffff;
    --surface-overlay: #ffffff;
    --surface-border: #e2e8f0;
    --text-color: #1e293b;
    --text-color-secondary: #64748b;
  }

  // Component size variations
  &.layout-size-small {
    --component-size: 0.875;
  }
  
  &.layout-size-normal {
    --component-size: 1;
  }
  
  &.layout-size-large {
    --component-size: 1.125;
  }

  // Border radius variations
  &.layout-radius-none {
    --border-radius: 0;
  }
  
  &.layout-radius-small {
    --border-radius: 0.25rem;
  }
  
  &.layout-radius-default {
    --border-radius: 0.5rem;
  }
  
  &.layout-radius-large {
    --border-radius: 1rem;
  }
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
