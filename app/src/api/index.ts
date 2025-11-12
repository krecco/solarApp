import axios from 'axios'
import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios'

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
  timeout: parseInt(import.meta.env.VITE_API_TIMEOUT || '30000'),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Mock mode for development
const isMockMode = import.meta.env.VITE_ENABLE_MOCK === 'true'

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    // Ensure headers object exists
    if (!config.headers) {
      config.headers = {}
    }
    
    // Add auth token if available
    const prefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
    const token = localStorage.getItem(`${prefix}token`) || sessionStorage.getItem(`${prefix}token`)
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      console.log('🔑 [API] Adding auth token:', token.substring(0, 20) + '...')
    } else {
      console.warn('⚠️ [API] No auth token found!')
    }
    
    // Add API version header
    const apiVersion = import.meta.env.VITE_API_VERSION || 'v1'
    config.headers['X-API-Version'] = apiVersion
    
    // Add request timestamp for performance tracking
    config.metadata = { startTime: Date.now() }
    
    // Log request in development
    if (import.meta.env.DEV) {
      console.log(`🚀 [API] ${config.method?.toUpperCase()} ${config.url}`, {
        headers: config.headers,
        data: config.data
      })
    }
    
    return config
  },
  (error) => {
    console.error('Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    // Calculate response time
    const responseTime = Date.now() - response.config.metadata?.startTime
    
    // Log response in development
    if (import.meta.env.DEV) {
      console.log(`✅ [API] Response (${responseTime}ms):`, response.data)
    }
    
    // Track response time (optional performance monitoring)
    // Note: Performance monitoring removed in simplified version
    // if (typeof window !== 'undefined' && window.appStore && typeof window.appStore.recordApiResponseTime === 'function') {
    //   window.appStore.recordApiResponseTime(responseTime)
    // }
    
    return response
  },
  async (error) => {
    const originalRequest = error.config
    
    // Log error in development
    if (import.meta.env.DEV) {
      console.error('❌ [API] Error:', error.response?.data || error.message)
    }
    
    // Handle 404 Not Found - Don't logout, just log the error
    if (error.response?.status === 404) {
      console.error('Resource not found:', error.config.url)
      // Don't reject for admin stats endpoint - it's deprecated
      if (error.config.url?.includes('/admin/stats')) {
        console.warn('Using deprecated /admin/stats endpoint, use /admin/dashboard instead')
      }
      return Promise.reject(error)
    }
    
    // Handle 401 Unauthorized - Token expired
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      
      try {
        // Try to refresh token
        const prefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
        const refreshToken = localStorage.getItem(`${prefix}refresh_token`) || sessionStorage.getItem(`${prefix}refresh_token`)
        
        if (refreshToken && typeof window !== 'undefined' && window.authStore) {
          const newToken = await window.authStore.refreshAccessToken()
          originalRequest.headers.Authorization = `Bearer ${newToken}`
          return apiClient(originalRequest)
        }
      } catch (refreshError) {
        // Refresh failed, redirect to login
        if (typeof window !== 'undefined' && window.authStore) {
          window.authStore.clearAuthData()
          window.location.href = '/auth/login'
        }
      }
    }
    
    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      console.error('❌ [API] 403 Forbidden:', error.config.url)
      console.error('Response:', error.response.data)
      if (typeof window !== 'undefined' && window.router) {
        window.router.push('/403')
      }
    }
    
    // Handle 429 Too Many Requests
    if (error.response?.status === 429) {
      const retryAfter = error.response.headers['retry-after']
      console.error(`Rate limited. Retry after ${retryAfter} seconds`)
      // Note: Toast notifications can be added here if needed
    }
    
    // Handle 500+ Server Errors
    if (error.response?.status >= 500) {
      console.error('Server error:', error.response?.data)
      // Note: Toast notifications can be added here if needed
    }
    
    // Handle network errors
    if (!error.response && error.message === 'Network Error') {
      console.error('Network error - please check your internet connection')
      // Note: Toast notifications can be added here if needed
    }
    
    return Promise.reject(error)
  }
)

// Helper functions for common HTTP methods
export const api = {
  get<T = any>(url: string, config?: AxiosRequestConfig): Promise<T> {
    return apiClient.get(url, config).then(res => res.data)
  },
  
  post<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
    return apiClient.post(url, data, config).then(res => res.data)
  },
  
  put<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
    return apiClient.put(url, data, config).then(res => res.data)
  },
  
  patch<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
    return apiClient.patch(url, data, config).then(res => res.data)
  },
  
  delete<T = any>(url: string, config?: AxiosRequestConfig): Promise<T> {
    return apiClient.delete(url, config).then(res => res.data)
  },
  
  upload<T = any>(url: string, formData: FormData, onProgress?: (progress: number) => void): Promise<T> {
    return apiClient.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (onProgress && progressEvent.total) {
          const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          onProgress(progress)
        }
      }
    }).then(res => res.data)
  },
  
  download(url: string, filename?: string): Promise<void> {
    return apiClient.get(url, {
      responseType: 'blob'
    }).then(response => {
      const blob = new Blob([response.data])
      const downloadUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = downloadUrl
      link.download = filename || 'download'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(downloadUrl)
    })
  }
}

// Export the axios instance for advanced usage
export { apiClient }

// Setup function to be called when app initializes
export function setupInterceptors() {
  // Store references globally for interceptors to use
  if (typeof window !== 'undefined') {
    window.apiClient = apiClient
  }
}