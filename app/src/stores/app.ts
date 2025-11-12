import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const isSidebarOpen = ref(true)
  const isSidebarMobileOpen = ref(false)
  const isMobile = ref(false)
  const isLoading = ref(false)
  const pageTitle = ref('')
  
  // Initialize mobile detection
  const checkMobile = () => {
    isMobile.value = window.innerWidth < 768
    if (isMobile.value) {
      isSidebarOpen.value = false
    }
  }
  
  // Actions
  const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
  }
  
  const toggleSidebarMobile = () => {
    isSidebarMobileOpen.value = !isSidebarMobileOpen.value
  }
  
  const setSidebarOpen = (open: boolean) => {
    isSidebarOpen.value = open
  }
  
  const setSidebarMobileOpen = (open: boolean) => {
    isSidebarMobileOpen.value = open
  }
  
  const setLoading = (loading: boolean) => {
    isLoading.value = loading
  }
  
  const setPageTitle = (title: string) => {
    pageTitle.value = title
    document.title = title ? `${title} - Admin Panel` : 'Admin Panel'
  }
  
  // Initialize
  if (typeof window !== 'undefined') {
    checkMobile()
    window.addEventListener('resize', checkMobile)
  }
  
  return {
    isSidebarOpen,
    isSidebarMobileOpen,
    isMobile,
    isLoading,
    pageTitle,
    toggleSidebar,
    toggleSidebarMobile,
    setSidebarOpen,
    setSidebarMobileOpen,
    setLoading,
    setPageTitle
  }
})