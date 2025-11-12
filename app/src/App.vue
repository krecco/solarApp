<template>
  <div id="app" :class="appClass">
    <RouterView v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </RouterView>
    
    <!-- Global Toast Component -->
    <Toast position="top-right" />
    
    <!-- Global Confirmation Dialog -->
    <ConfirmDialog />
    
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="global-loader">
      <ProgressSpinner />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { RouterView } from 'vue-router'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import ProgressSpinner from 'primevue/progressspinner'
import { useThemeStore } from '@/stores/theme'
import { useAppStore } from '@/stores/app'

// Stores
const themeStore = useThemeStore()
const appStore = useAppStore()

// Computed
const appClass = computed(() => ({
  'app-wrapper': true,
  'dark': themeStore.isDark,
  'rtl': themeStore.isRTL,
  [`theme-${themeStore.currentTheme}`]: true,
  [`density-${themeStore.density}`]: true
}))

const isLoading = computed(() => appStore.isLoading)

// Lifecycle
onMounted(() => {
  // Initialize theme from localStorage or system preference
  themeStore.initTheme()
  
  // Check authentication status
  // authStore.checkAuth()
  
  // Setup global error handler
  window.addEventListener('unhandledrejection', handleError)
  window.addEventListener('error', handleError)
})

// Watch for theme changes
watch(() => themeStore.isDark, (isDark) => {
  if (isDark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
})

// Error handling
const handleError = (event: any) => {
  console.error('Global error:', event)
  // You can send errors to a logging service here
}

// Cleanup
onMounted(() => {
  return () => {
    window.removeEventListener('unhandledrejection', handleError)
    window.removeEventListener('error', handleError)
  }
})
</script>

<style lang="scss">
// Use modern @use syntax
@use '@/styles/main.scss';
@use '@/styles/components/primevue-overrides';

// App wrapper styles
.app-wrapper {
  min-height: 100vh;
  transition: all 0.3s ease;
  background: var(--surface-ground);
  color: var(--text-color);
}

// Global loader
.global-loader {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(5px);
}

// Page transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

// Slide transition
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from {
  transform: translateX(-100%);
}

.slide-leave-to {
  transform: translateX(100%);
}

// Scale transition
.scale-enter-active,
.scale-leave-active {
  transition: transform 0.3s ease;
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0.9);
  opacity: 0;
}

// Dark mode specific styles
.dark {
  --surface-ground: #0f172a;
  --surface-section: #1e293b;
  --surface-card: #1e293b;
  --surface-overlay: #334155;
  --surface-border: #334155;
  --surface-hover: #475569;
  --surface-0: #ffffff;
  --surface-50: #f8fafc;
  --surface-100: #f1f5f9;
  --surface-200: #e2e8f0;
  --surface-300: #cbd5e1;
  --surface-400: #94a3b8;
  --surface-500: #64748b;
  --surface-600: #475569;
  --surface-700: #334155;
  --surface-800: #1e293b;
  --surface-900: #0f172a;
  --text-color: #f1f5f9;
  --text-color-secondary: #94a3b8;
  --primary-color: #10b981;
  --primary-color-text: #ffffff;
  --primary-color-alpha: rgba(16, 185, 129, 0.2);
  --primary-600: #059669;
  --red-500: #ef4444;
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  --border-radius-sm: 0.375rem;
  --transition-duration: 150ms;
}

// Light mode specific styles (ensure all variables are defined)
:root {
  --surface-ground: #f8fafc;
  --surface-section: #ffffff;
  --surface-card: #ffffff;
  --surface-overlay: #ffffff;
  --surface-border: #e2e8f0;
  --surface-hover: #f1f5f9;
  --surface-0: #ffffff;
  --surface-50: #f8fafc;
  --surface-100: #f1f5f9;
  --surface-200: #e2e8f0;
  --surface-300: #cbd5e1;
  --surface-400: #94a3b8;
  --surface-500: #64748b;
  --surface-600: #475569;
  --surface-700: #334155;
  --surface-800: #1e293b;
  --surface-900: #0f172a;
  --text-color: #1e293b;
  --text-color-secondary: #64748b;
  --primary-color: #10b981;
  --primary-color-text: #ffffff;
  --primary-color-alpha: rgba(16, 185, 129, 0.2);
  --primary-600: #059669;
  --red-500: #ef4444;
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  --border-radius-sm: 0.375rem;
  --transition-duration: 150ms;
}

// Density variations
.density-compact {
  --spacing-unit: 0.5rem;
}

.density-normal {
  --spacing-unit: 1rem;
}

.density-comfortable {
  --spacing-unit: 1.5rem;
}

// RTL support
.rtl {
  direction: rtl;
  text-align: right;
}

// Scrollbar styles
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--surface-ground);
}

::-webkit-scrollbar-thumb {
  background: var(--surface-border);
  border-radius: 4px;
  
  &:hover {
    background: var(--primary-color);
  }
}

// Selection styles
::selection {
  background: var(--primary-color);
  color: white;
}

::-moz-selection {
  background: var(--primary-color);
  color: white;
}
</style>