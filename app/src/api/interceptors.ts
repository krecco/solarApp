// API Interceptors Setup
// This file is imported in main.ts to setup global interceptors

export function setupInterceptors() {
  // Store global references for interceptors
  if (typeof window !== 'undefined') {
    // Make stores available globally for interceptors
    import('@/stores/auth').then(({ useAuthStore }) => {
      window.authStore = useAuthStore()
    })
    
    import('@/stores/app').then(({ useAppStore }) => {
      window.appStore = useAppStore()
    })
    
    import('@/router').then(({ default: router }) => {
      window.router = router
    })
  }
}

// Extend Window interface
declare global {
  interface Window {
    authStore: any
    appStore: any
    router: any
    apiClient: any
  }
}