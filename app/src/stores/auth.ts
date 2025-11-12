import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/api/auth.service'
import { apiClient } from '@/api'
import preferencesService, { type UserPreferences } from '@/api/preferences.service'
import { useThemeStore } from './theme'

// Export interfaces for use in other files
export interface User {
  id: string | number
  name: string
  email: string
  avatar_url?: string
  role: 'admin' | 'manager' | 'user'
  permissions?: string[]
  email_verified_at?: string | null
  emailVerified?: boolean
  createdAt?: string
  created_at?: string
  lastLogin?: string
  preferences?: UserPreferences
  tenant?: any
  needs_profile_completion?: boolean
}

export interface LoginCredentials {
  email: string
  password: string
  rememberMe?: boolean
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  acceptTerms?: boolean
}

export interface SimpleRegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface ProfileCompletionData {
  company_name?: string
  tenant_name?: string
  [key: string]: any
}

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  
  // Get storage prefix from env
  const STORAGE_PREFIX = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
  
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const refreshToken = ref<string | null>(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const preferences = ref<UserPreferences | null>(null)
  const profileCompleted = ref(true)
  const socialProvider = ref<string | null>(null)
  const needsProfileCompletion = ref(false)

  
  // Computed
  const userInitials = computed(() => {
    if (!user.value?.name) return 'U'
    const names = user.value.name.split(' ')
    if (names.length > 1) {
      return names[0][0] + names[names.length - 1][0]
    }
    return names[0].substring(0, 2).toUpperCase()
  })

  // Helper: Load user preferences with localStorage caching
  const loadPreferences = async () => {
    try {
      // Try to load from localStorage first (for offline support)
      const cachedPreferences = localStorage.getItem(`${STORAGE_PREFIX}preferences`)
      if (cachedPreferences) {
        preferences.value = JSON.parse(cachedPreferences)
      }

      // Then fetch from backend to sync
      const response = await preferencesService.getPreferences()
      preferences.value = response.data

      // Update localStorage cache
      localStorage.setItem(`${STORAGE_PREFIX}preferences`, JSON.stringify(response.data))

      // Update user object if it exists
      if (user.value) {
        user.value.preferences = response.data
      }

      // Apply preferences to the app
      if (preferences.value) {
        // Apply language if available
        if (preferences.value.language && typeof window !== 'undefined') {
          // Language will be applied by the i18n composable on mount
          localStorage.setItem('locale', preferences.value.language)
        }

        // Apply theme and primaryColor via theme store
        if ((preferences.value.theme || preferences.value.primaryColor) && typeof window !== 'undefined') {
          const themeStore = useThemeStore()
          console.log('🎨 Applying preferences from backend:', preferences.value)
          themeStore.syncFromPreferences({
            theme: preferences.value.theme,
            primaryColor: preferences.value.primaryColor
          })
          console.log('✅ Theme synced with primaryColor:', preferences.value.primaryColor)
        }
      }
    } catch (error) {
      console.error('Failed to load preferences:', error)
      // Keep cached preferences if backend fails
    }
  }

  // Helper: Update preferences and sync with backend
  const updateUserPreferences = async (updates: Partial<UserPreferences>) => {
    try {
      const response = await preferencesService.updatePreferences(updates)
      preferences.value = response.data

      // Update localStorage cache
      localStorage.setItem(`${STORAGE_PREFIX}preferences`, JSON.stringify(response.data))

      // Update user object if it exists
      if (user.value) {
        user.value.preferences = response.data
      }

      return response
    } catch (error) {
      console.error('Failed to update preferences:', error)
      throw error
    }
  }

  // Initialize from localStorage
  const init = async () => {
    console.log('🔐 Auth store initializing...')
    const savedToken = localStorage.getItem(`${STORAGE_PREFIX}token`) || sessionStorage.getItem(`${STORAGE_PREFIX}token`)
    const savedUser = localStorage.getItem(`${STORAGE_PREFIX}user`) || sessionStorage.getItem(`${STORAGE_PREFIX}user`)
    const savedNeedsCompletion = localStorage.getItem(`${STORAGE_PREFIX}needs_profile_completion`)
    
    console.log('🔍 Checking saved auth data:', { hasToken: !!savedToken, hasUser: !!savedUser })
    
    if (savedToken && savedUser) {
      try {
        // Parse and set user data FIRST
        const parsedUser = JSON.parse(savedUser)
        
        // Ensure user has all required fields, especially role
        if (parsedUser) {
          // Simple role detection: default to user if missing
          if (!parsedUser.role) {
            parsedUser.role = 'user'
            console.log('⚠️ User role was missing, defaulting to user')
          }

          // Set the user data immediately
          user.value = parsedUser
          token.value = savedToken
          isAuthenticated.value = true
          
          // Check profile completion status
          if (savedNeedsCompletion === 'true' || !parsedUser.tenant) {
            needsProfileCompletion.value = true
            profileCompleted.value = false
          }
          
          console.log('✅ Auth restored from storage:', {
            userId: user.value?.id,
            role: user.value?.role,
            name: user.value?.name,
            email: user.value?.email
          })

          // Load user preferences in the background
          loadPreferences().catch(err => {
            console.log('⚠️ Failed to load preferences on init:', err)
          })

          // Try to verify token in the background, but don't clear user on failure
          // This prevents the UI from flickering or losing state
          // DISABLED FOR DEMO - Skip API verification since no backend exists
          /*
          try {
            const response = await authService.getCurrentUser()
            if (response?.user) {
              // Only update if we got valid user data
              const updatedUser = response.user
              
              // IMPORTANT: Preserve the existing role if backend doesn't return it or returns wrong role
              // The stored role is more trustworthy than the API response in some cases
              if ((!updatedUser.role || updatedUser.role === 'user') && user.value?.role === 'admin') {
                updatedUser.role = 'admin' // Keep admin role
              }
              
              user.value = updatedUser
              
              // Update stored user data
              const storage = localStorage.getItem(`${STORAGE_PREFIX}token`) ? localStorage : sessionStorage
              storage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(updatedUser))
              console.log('✅ Token verified and user data updated:', { 
                userId: updatedUser.id, 
                role: updatedUser.role 
              })
            }
          } catch (verifyError: any) {
            // Only logout if we get an explicit 401 Unauthorized
            if (verifyError?.response?.status === 401) {
              console.log('❌ Token invalid (401), logging out')
              await logout()
            } else {
              // Keep the user logged in with cached data for any other error
              console.log('⚠️ Token verification failed but keeping existing auth:', verifyError?.message)
              console.log('ℹ️ User remains logged in with cached data')
            }
          }
          */
          console.log('ℹ️ Skipping API token verification (demo mode)')
        } else {
          console.log('❌ Parsed user data is null/undefined')
          clearAuthData()
        }
      } catch (parseError) {
        console.log('❌ Failed to parse saved user data:', parseError)
        clearAuthData()
      }
    } else {
      console.log('ℹ️ No saved auth data found')
    }
  }
  
  // Actions
  const login = async (credentials: LoginCredentials) => {
    isLoading.value = true
    
    try {
      const response = await authService.login(credentials)
      
      if (response.requiresTwoFactor) {
        // Store temp data for 2FA
        sessionStorage.setItem(`${STORAGE_PREFIX}temp_token`, response.tempToken || '')
        sessionStorage.setItem(`${STORAGE_PREFIX}temp_email`, credentials.email)
        return { success: false, requiresTwoFactor: true }
      }
      
      // Set state
      token.value = response.token
      refreshToken.value = response.refreshToken
      user.value = response.user
      isAuthenticated.value = true

      // Save to storage (localStorage if rememberMe, sessionStorage otherwise)
      const storage = credentials.rememberMe ? localStorage : sessionStorage
      storage.setItem(`${STORAGE_PREFIX}token`, response.token)
      storage.setItem(`${STORAGE_PREFIX}refresh_token`, response.refreshToken)
      storage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))

      // Clear the other storage
      const otherStorage = credentials.rememberMe ? sessionStorage : localStorage
      otherStorage.removeItem(`${STORAGE_PREFIX}token`)
      otherStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
      otherStorage.removeItem(`${STORAGE_PREFIX}user`)

      // Set preferences from login response (included since backend update)
      if (response.preferences) {
        preferences.value = response.preferences
        localStorage.setItem(`${STORAGE_PREFIX}preferences`, JSON.stringify(response.preferences))

        // Apply preferences to the app immediately
        if (response.preferences.language && typeof window !== 'undefined') {
          localStorage.setItem('locale', response.preferences.language)
        }

        if ((response.preferences.theme || response.preferences.primaryColor) && typeof window !== 'undefined') {
          const themeStore = useThemeStore()
          console.log('🎨 Applying preferences from login:', response.preferences)
          themeStore.syncFromPreferences({
            theme: response.preferences.theme,
            primaryColor: response.preferences.primaryColor
          })
          console.log('✅ Theme synced with primaryColor:', response.preferences.primaryColor)
        }
      } else {
        // Fallback: Load preferences from API if not in login response (backward compatibility)
        loadPreferences().catch(err => {
          console.log('⚠️ Failed to load preferences after login:', err)
        })
      }

      return { success: true }
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const register = async (data: RegisterData) => {
    isLoading.value = true
    
    try {
      const response = await authService.register(data)
      
      // If token is provided, automatically log in
      if (response.token && response.user) {
        token.value = response.token
        refreshToken.value = response.token
        user.value = response.user
        isAuthenticated.value = true
        
        // Save to localStorage by default for new registrations
        localStorage.setItem(`${STORAGE_PREFIX}token`, response.token)
        localStorage.setItem(`${STORAGE_PREFIX}refresh_token`, response.token)
        localStorage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))

        // Set preferences from register response (included since backend update)
        if (response.preferences) {
          preferences.value = response.preferences
          localStorage.setItem(`${STORAGE_PREFIX}preferences`, JSON.stringify(response.preferences))

          // Apply preferences to the app immediately
          if (response.preferences.language && typeof window !== 'undefined') {
            localStorage.setItem('locale', response.preferences.language)
          }

          if ((response.preferences.theme || response.preferences.primaryColor) && typeof window !== 'undefined') {
            const themeStore = useThemeStore()
            themeStore.syncFromPreferences({
              theme: response.preferences.theme,
              primaryColor: response.preferences.primaryColor
            })
          }
        } else {
          // Fallback: Load preferences from API if not in register response (backward compatibility)
          loadPreferences().catch(err => {
            console.log('⚠️ Failed to load preferences after registration:', err)
          })
        }
      }

      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const socialLogin = async (provider: string) => {
    isLoading.value = true
    
    try {
      const response = await authService.getSocialRedirectUrl(provider)
      
      if (response.redirect_url) {
        // Store provider for callback
        sessionStorage.setItem(`${STORAGE_PREFIX}social_provider`, provider)
        
        // Redirect to OAuth provider
        window.location.href = response.redirect_url
      } else {
        throw new Error('Failed to get OAuth redirect URL')
      }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const simpleRegister = async (data: SimpleRegisterData) => {
    isLoading.value = true
    
    try {
      const response = await authService.simpleRegister(data)
      
      // Set auth state
      token.value = response.token
      refreshToken.value = response.token
      user.value = response.user
      isAuthenticated.value = true
      needsProfileCompletion.value = response.needs_profile_completion || !response.user.tenant
      
      // Save to localStorage
      localStorage.setItem(`${STORAGE_PREFIX}token`, response.token)
      localStorage.setItem(`${STORAGE_PREFIX}refresh_token`, response.token)
      localStorage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))
      localStorage.setItem(`${STORAGE_PREFIX}needs_profile_completion`, String(needsProfileCompletion.value))

      // Load user preferences
      loadPreferences().catch(err => {
        console.log('⚠️ Failed to load preferences after simple registration:', err)
      })

      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const completeProfile = async (data: ProfileCompletionData) => {
    isLoading.value = true
    
    try {
      const response = await authService.completeProfile(data)
      
      // Update user with tenant info
      if (response.user) {
        user.value = response.user
        needsProfileCompletion.value = false
        profileCompleted.value = true
        
        // Update storage
        localStorage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))
        localStorage.removeItem(`${STORAGE_PREFIX}needs_profile_completion`)
      }
      
      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const handleOAuthCallback = async (provider: string, code: string, state?: string) => {
    isLoading.value = true
    
    try {
      const params = new URLSearchParams()
      params.append('code', code)
      if (state) {
        params.append('state', state)
      }
      
      const response = await authService.handleSocialCallback(provider, params)
      
      // Set auth state
      token.value = response.token
      refreshToken.value = response.token
      user.value = response.user
      isAuthenticated.value = true
      socialProvider.value = provider
      needsProfileCompletion.value = response.needs_profile_completion || !response.user.tenant
      
      // Save to localStorage
      localStorage.setItem(`${STORAGE_PREFIX}token`, response.token)
      localStorage.setItem(`${STORAGE_PREFIX}refresh_token`, response.token)
      localStorage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))
      localStorage.setItem(`${STORAGE_PREFIX}social_provider`, provider)
      localStorage.setItem(`${STORAGE_PREFIX}needs_profile_completion`, String(needsProfileCompletion.value))
      
      // Clear session storage
      sessionStorage.removeItem(`${STORAGE_PREFIX}social_provider`)

      // Load user preferences
      loadPreferences().catch(err => {
        console.log('⚠️ Failed to load preferences after OAuth callback:', err)
      })

      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const checkProfileStatus = async () => {
    // Check profile status from current user data
    if (user.value) {
      needsProfileCompletion.value = !user.value.tenant || user.value.needs_profile_completion === true
      profileCompleted.value = !needsProfileCompletion.value
      return { needs_profile_completion: needsProfileCompletion.value }
    }

    needsProfileCompletion.value = false
    profileCompleted.value = true
    return { needs_profile_completion: false }
  }


  const requestOtp = async (data: { 
    method: 'email' | 'sms'
    recipient: string
    rememberDevice?: boolean 
  }) => {
    isLoading.value = true
    
    try {
      const response = await authService.generateOTP(data.recipient)
      
      // Store OTP request data temporarily
      sessionStorage.setItem(`${STORAGE_PREFIX}otp_request`, JSON.stringify({
        ...data,
        expiresAt: response.expiresAt
      }))
      
      return { 
        success: true, 
        message: response.message || `Code sent to ${data.method === 'email' ? data.recipient : '***' + data.recipient.slice(-4)}` 
      }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const verifyOtp = async (data: { 
    code: string
    method: string
    recipient: string
    trustDevice?: boolean 
  }) => {
    isLoading.value = true
    
    try {
      const response = await authService.verifyOTP(data.recipient, data.code)
      
      // Set state
      token.value = response.token
      refreshToken.value = response.refreshToken
      user.value = response.user
      isAuthenticated.value = true
      
      // Save to storage (localStorage if trustDevice, sessionStorage otherwise)
      const storage = data.trustDevice ? localStorage : sessionStorage
      storage.setItem(`${STORAGE_PREFIX}token`, response.token)
      storage.setItem(`${STORAGE_PREFIX}refresh_token`, response.refreshToken)
      storage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))
      
      // Clear the other storage
      const otherStorage = data.trustDevice ? sessionStorage : localStorage
      otherStorage.removeItem(`${STORAGE_PREFIX}token`)
      otherStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
      otherStorage.removeItem(`${STORAGE_PREFIX}user`)
      
      // Clear OTP request data
      sessionStorage.removeItem(`${STORAGE_PREFIX}otp_request`)
      
      return { success: true }
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const verifyTwoFactor = async (code: string) => {
    isLoading.value = true
    
    try {
      const tempToken = sessionStorage.getItem(`${STORAGE_PREFIX}temp_token`) || ''
      const response = await authService.verifyTwoFactor(tempToken, code)
      
      // Set state
      token.value = response.token
      refreshToken.value = response.refreshToken
      user.value = response.user
      isAuthenticated.value = true
      
      // Save to localStorage (2FA users should stay logged in)
      localStorage.setItem(`${STORAGE_PREFIX}token`, response.token)
      localStorage.setItem(`${STORAGE_PREFIX}refresh_token`, response.refreshToken)
      localStorage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(response.user))
      
      // Clear temp data
      sessionStorage.removeItem(`${STORAGE_PREFIX}temp_token`)
      sessionStorage.removeItem(`${STORAGE_PREFIX}temp_email`)
      
      return { success: true }
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const verifyEmail = async (code: string) => {
    isLoading.value = true
    
    try {
      const response = await authService.verifyEmail(code)
      
      // Update user's email verification status
      if (user.value) {
        user.value.emailVerified = true
        const storage = localStorage.getItem(`${STORAGE_PREFIX}token`) ? localStorage : sessionStorage
        storage.setItem(`${STORAGE_PREFIX}user`, JSON.stringify(user.value))
      }
      
      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const resendVerificationEmail = async () => {
    isLoading.value = true
    
    try {
      const response = await authService.resendVerificationEmail()
      return response
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }
  
  const logout = async () => {
    try {
      // Call API logout
      await authService.logout()
    } catch (error) {
      // Continue with local logout even if API fails
      console.error('Logout API error:', error)
    }

    // Clear state
    user.value = null
    token.value = null
    refreshToken.value = null
    isAuthenticated.value = false
    preferences.value = null
    profileCompleted.value = true
    socialProvider.value = null
    needsProfileCompletion.value = false

    // Clear storage
    localStorage.removeItem(`${STORAGE_PREFIX}token`)
    localStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
    localStorage.removeItem(`${STORAGE_PREFIX}user`)
    localStorage.removeItem(`${STORAGE_PREFIX}preferences`)
    localStorage.removeItem(`${STORAGE_PREFIX}needs_profile_completion`)
    localStorage.removeItem(`${STORAGE_PREFIX}social_provider`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}token`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}user`)

    // Clear any temp data
    sessionStorage.removeItem(`${STORAGE_PREFIX}temp_token`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}temp_email`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}otp_request`)

    // Redirect to login
    router.push('/auth/login')
  }

  const requestPasswordReset = async (email: string) => {
    try {
      const response = await apiClient.post('/api/v1/password/forgot', { email })
      return response.data
    } catch (error: any) {
      const errorMessage = error.response?.data?.message || 'Failed to send password reset email'
      throw new Error(errorMessage)
    }
  }

  const refreshAccessToken = async () => {
    const savedRefreshToken = refreshToken.value || 
      localStorage.getItem(`${STORAGE_PREFIX}refresh_token`) || 
      sessionStorage.getItem(`${STORAGE_PREFIX}refresh_token`)
    
    if (!savedRefreshToken) {
      throw new Error('No refresh token available')
    }
    
    try {
      const response = await authService.refreshToken(savedRefreshToken)
      
      // Update tokens
      token.value = response.token
      refreshToken.value = response.refreshToken
      
      // Update storage
      const storage = localStorage.getItem(`${STORAGE_PREFIX}token`) ? localStorage : sessionStorage
      storage.setItem(`${STORAGE_PREFIX}token`, response.token)
      storage.setItem(`${STORAGE_PREFIX}refresh_token`, response.refreshToken)
      
      return response.token
    } catch (error) {
      await logout()
      throw error
    }
  }
  
  const clearAuthData = () => {
    // Clear state
    user.value = null
    token.value = null
    refreshToken.value = null
    isAuthenticated.value = false
    preferences.value = null
    profileCompleted.value = true
    socialProvider.value = null
    needsProfileCompletion.value = false

    // Clear storage
    localStorage.removeItem(`${STORAGE_PREFIX}token`)
    localStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
    localStorage.removeItem(`${STORAGE_PREFIX}user`)
    localStorage.removeItem(`${STORAGE_PREFIX}preferences`)
    localStorage.removeItem(`${STORAGE_PREFIX}needs_profile_completion`)
    localStorage.removeItem(`${STORAGE_PREFIX}social_provider`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}token`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}refresh_token`)
    sessionStorage.removeItem(`${STORAGE_PREFIX}user`)
  }
  
  // Role checking function
  const hasRole = (role: string): boolean => {
    return user.value?.role === role
  }
  
  // Make store available globally for interceptors
  if (typeof window !== 'undefined') {
    window.authStore = {
      refreshAccessToken,
      clearAuthData
    }
  }
  
  return {
    // State
    user,
    token,
    isAuthenticated,
    isLoading,
    profileCompleted,
    socialProvider,
    needsProfileCompletion,
    preferences,

    // Computed
    userInitials,

    // Actions
    init,
    login,
    register,
    simpleRegister,
    completeProfile,
    socialLogin,
    handleOAuthCallback,
    checkProfileStatus,
    requestOtp,
    verifyOtp,
    verifyTwoFactor,
    verifyEmail,
    resendVerificationEmail,
    logout,
    requestPasswordReset,
    refreshAccessToken,
    clearAuthData,
    hasRole,
    loadPreferences,
    updateUserPreferences
  }
})
