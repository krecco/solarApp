import { api } from './index'

// Types
export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number | null
  to: number | null
}

export interface AdminDashboard {
  users: {
    total: number
    verified: number
    created_today: number
    created_this_week: number
    created_this_month: number
  }
  roles: {
    admins: number
    managers: number
    users: number
  }
}

export interface SystemStats {
  total_users: number
  active_users: number
  revenue: {
    mrr: number
    arr: number
    growth_rate: number
  }
  by_plan: {
    [planName: string]: {
      count: number
      mrr: number
    }
  }
}

export interface AdminUser {
  id: number
  name: string
  email: string
  avatar_url?: string | null
  email_verified_at?: string
  roles: string[]
  created_at: string
  updated_at: string
}

export interface CreateUserData {
  name: string
  email: string
  password: string
  role: 'admin' | 'manager' | 'user'
  email_verified?: boolean
}

// User interface for backward compatibility
export interface User {
  id: number
  name: string
  email: string
  avatar_url?: string | null
  email_verified_at?: string
  roles: string[]
  permissions?: string[]
  created_at: string
  updated_at: string
}


// API Service
export const adminService = {
  // Get admin dashboard data
  async getDashboard(): Promise<{ data: AdminDashboard }> {
    return api.get('/api/v1/admin/dashboard')
  },

  // Get system statistics (legacy)
  async getStats(): Promise<{ data: SystemStats }> {
    return api.get('/api/v1/admin/stats')
  },

  // User Management
  async listUsers(filters?: {
    page?: number
    per_page?: number
    search?: string
    roles?: string[]
    email_verified?: boolean
    sort_by?: string
    sort_direction?: 'asc' | 'desc'
  }): Promise<PaginatedResponse<AdminUser>> {
    return api.post('/api/v1/admin/users/search', filters || {})
  },

  async createUser(data: CreateUserData): Promise<{ data: AdminUser }> {
    return api.post('/api/v1/admin/users', data)
  },

  async updateUser(id: number, data: Partial<AdminUser>): Promise<{ data: AdminUser }> {
    return api.put(`/api/v1/admin/users/${id}`, data)
  },

  async deleteUser(id: number): Promise<{ meta: { message: string } }> {
    return api.delete(`/api/v1/admin/users/${id}`)
  },

  // Get user details
  async getUser(id: number): Promise<{ data: User }> {
    return api.get(`/api/v1/admin/users/${id}`)
  },

  // Send welcome email
  async sendWelcomeEmail(userId: number): Promise<{ message: string }> {
    return api.post(`/api/v1/admin/users/${userId}/send-welcome-email`)
  },

  // Update user avatar
  async updateAvatar(userId: number, formData: FormData): Promise<{ data: AdminUser }> {
    return api.post(`/api/v1/admin/users/${userId}/avatar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Delete user avatar
  async deleteAvatar(userId: number): Promise<{ data: AdminUser }> {
    return api.delete(`/api/v1/admin/users/${userId}/avatar`)
  }
}

// Export individual functions for convenience
export const updateUserAvatar = adminService.updateAvatar
export const deleteUserAvatar = adminService.deleteAvatar
