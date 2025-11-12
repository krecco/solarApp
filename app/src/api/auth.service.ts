import { api } from './index'
import type { LoginCredentials, RegisterData, User } from '@/stores/auth'

// Interface definitions for API responses
interface LoginResponse {
  data: {
    user: any
    token: string
  }
}

interface RegisterResponse {
  data: {
    user: any
    token: string
  }
  meta: {
    status: string
    message: string
  }
}

interface UserResponse {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  trial_ends_at: string | null
  created_at: string
  roles: string[]
  permissions: string[]
}

interface OTPGenerateResponse {
  data: {
    expires_at: string
  }
  meta: {
    status: string
    message: string
  }
}

interface OTPVerifyResponse {
  data: {
    user: any
    token: string
  }
  meta: {
    status: string
    message: string
  }
}

// Helper function to transform Laravel user to app user format
function transformUser(laravelUser: any): User {
  // Simple role detection: just use the role field from backend
  const userRole = laravelUser.role || 'user'

  return {
    id: String(laravelUser.id),
    email: laravelUser.email,
    name: laravelUser.name,
    avatar_url: laravelUser.avatar_url || null,
    role: userRole,
    permissions: [],
    email_verified_at: laravelUser.email_verified_at,
    emailVerified: !!laravelUser.email_verified_at,
    twoFactorEnabled: false, // Always false, 2FA not implemented
    createdAt: laravelUser.created_at,
    created_at: laravelUser.created_at,
    lastLogin: laravelUser.last_login || new Date().toISOString()
  }
}

export const authService = {
  // Existing methods...
  async login(credentials: LoginCredentials) {
    try {
      const response = await api.post<LoginResponse>('/api/v1/login', {
        email: credentials.email,
        password: credentials.password
      })
      
      return {
        token: response.data.token,
        refreshToken: response.data.token, // Laravel Sanctum uses same token
        user: transformUser(response.data.user),
        requiresTwoFactor: false
      }
    } catch (error: any) {
      if (error.response?.status === 401) {
        throw new Error('Invalid email or password')
      }
      throw error
    }
  },
  
  async register(data: RegisterData) {
    try {
      const response = await api.post<RegisterResponse>('/api/v1/register', {
        name: data.name,
        email: data.email,
        password: data.password,
        password_confirmation: data.password_confirmation
      })

      // Store token if provided
      if (response.data?.token) {
        const prefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
        localStorage.setItem(`${prefix}token`, response.data.token)
      }

      return {
        success: true,
        message: response.meta?.message || 'Registration successful. Please check your email to verify your account.',
        user: response.data?.user ? transformUser(response.data.user) : undefined,
        token: response.data?.token
      }
    } catch (error: any) {
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors
        const firstError = Object.values(errors)[0]
        throw new Error(Array.isArray(firstError) ? firstError[0] : firstError)
      }
      throw error
    }
  },
  
  async logout() {
    try {
      await api.post('/api/v1/logout')
      return { success: true }
    } catch (error) {
      // Even if logout fails on server, clear local data
      return { success: true }
    }
  },
  
  async getCurrentUser() {
    try {
      const response = await api.get<UserResponse>('/api/v1/user')
      return { user: transformUser(response) }
    } catch (error) {
      throw error
    }
  },
  
  async verifyToken() {
    // Use getCurrentUser to verify token
    return this.getCurrentUser()
  },
  
  async refreshToken(refreshToken: string) {
    // Laravel Sanctum doesn't have a refresh endpoint, tokens are long-lived
    // Return the same token
    return {
      token: refreshToken,
      refreshToken: refreshToken
    }
  },
  
  // Email Verification (PUBLIC - no auth required)
  async verifyEmail(email: string, code: string) {
    try {
      const response = await api.post('/api/v1/email/verify', { email, code })
      
      // If verification successful, user is logged in
      if (response.data?.token) {
        const prefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
        localStorage.setItem(`${prefix}token`, response.data.token)
        if (response.data.user) {
          localStorage.setItem(`${prefix}user`, JSON.stringify(transformUser(response.data.user)))
        }
      }
      
      return {
        success: true,
        message: response.meta?.message || 'Email verified successfully',
        token: response.data?.token,
        user: response.data?.user ? transformUser(response.data.user) : null
      }
    } catch (error: any) {
      if (error.response?.data?.errors?.code) {
        throw new Error(error.response.data.errors.code[0])
      }
      throw error
    }
  },
  
  async resendVerificationEmail(email: string) {
    try {
      const response = await api.post('/api/v1/email/resend', { email })
      return {
        success: true,
        message: response.meta?.message || 'Verification code sent successfully'
      }
    } catch (error: any) {
      if (error.response?.status === 429) {
        throw new Error('Too many requests. Please try again later.')
      }
      throw error
    }
  },
  
  // OTP Authentication
  async generateOTP(email: string) {
    try {
      const response = await api.post<OTPGenerateResponse>('/api/v1/otp/send', { email })
      return {
        success: true,
        message: response.meta?.message || 'OTP sent to your email address',
        expiresAt: response.data?.expires_at
      }
    } catch (error) {
      throw error
    }
  },
  
  async verifyOTP(email: string, otp: string) {
    try {
      const response = await api.post<OTPVerifyResponse>('/api/v1/otp/verify', { 
        email, 
        code: otp  // Changed from 'otp' to 'code' to match backend
      })
      
      return {
        token: response.data.token,
        refreshToken: response.data.token,
        user: transformUser(response.data.user),
        success: true,
        message: response.meta?.message || 'OTP verified successfully'
      }
    } catch (error: any) {
      if (error.response?.data?.message) {
        throw new Error(error.response.data.message)
      }
      throw error
    }
  },
  
  // Two-Factor Authentication (keeping for backward compatibility)
  async verifyTwoFactor(tempToken: string, code: string) {
    // This would need to be implemented based on your Laravel 2FA setup
    // For now, using OTP verification as fallback
    const email = sessionStorage.getItem('temp_email') || ''
    return this.verifyOTP(email, code)
  },
  
  async forgotPassword(email: string) {
    // This endpoint needs to be implemented in Laravel
    // Using a placeholder for now
    try {
      const response = await api.post('/api/v1/password/forgot', { email })
      return {
        success: true,
        message: 'Password reset link sent to your email'
      }
    } catch (error) {
      throw error
    }
  },
  
  async resetPassword(token: string, password: string) {
    // This endpoint needs to be implemented in Laravel
    try {
      const response = await api.post('/api/v1/password/reset', {
        token,
        password,
        password_confirmation: password
      })
      return {
        success: true,
        message: 'Password reset successful'
      }
    } catch (error) {
      throw error
    }
  },

  /**
   * Resend verification email (authenticated users)
   * POST /api/v1/email/resend-authenticated
   */
  async resendVerificationEmailAuthenticated() {
    try {
      const response = await api.post('/api/v1/email/resend-authenticated')
      return {
        success: true,
        message: response.meta?.message || 'Verification code sent successfully'
      }
    } catch (error: any) {
      if (error.response?.status === 429) {
        throw new Error('Too many requests. Please try again later.')
      }
      throw error
    }
  },

  /**
   * Verify email when already authenticated
   * POST /api/v1/email/verify-authenticated
   */
  async verifyEmailAuthenticated(code: string) {
    try {
      const response = await api.post('/api/v1/email/verify-authenticated', { code })
      return {
        success: true,
        message: response.meta?.message || 'Email verified successfully',
        user: response.data?.user ? transformUser(response.data.user) : null
      }
    } catch (error: any) {
      if (error.response?.data?.errors?.code) {
        throw new Error(error.response.data.errors.code[0])
      }
      throw error
    }
  },

  /**
   * Resend OTP code
   * POST /api/v1/otp/resend
   */
  async resendOTP(email: string) {
    try {
      const response = await api.post('/api/v1/otp/resend', { email })
      return {
        success: true,
        message: response.meta?.message || 'New OTP sent to your email'
      }
    } catch (error: any) {
      if (error.response?.status === 429) {
        throw new Error('Too many requests. Please try again later.')
      }
      throw error
    }
  },

  /**
   * Validate password reset token
   * POST /api/v1/password/validate-token
   */
  async validateResetToken(email: string, token: string) {
    try {
      const response = await api.post('/api/v1/password/validate-token', { email, token })
      return {
        valid: true,
        message: response.meta?.message || 'Token is valid'
      }
    } catch (error: any) {
      if (error.response?.status === 422) {
        throw new Error('Invalid or expired reset token')
      }
      throw error
    }
  },

}
