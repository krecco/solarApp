// Global type declarations
declare global {
  interface Window {
    authStore?: {
      refreshAccessToken: () => Promise<string>
      clearAuthData: () => void
    }
    appStore?: {
      recordApiResponseTime: (time: number) => void
      addNotification: (notification: {
        type: 'success' | 'error' | 'warning' | 'info'
        title: string
        message: string
      }) => void
    }
    router?: any
    apiClient?: any
  }
}

export {}
