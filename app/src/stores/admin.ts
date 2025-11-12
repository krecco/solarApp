import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminService } from '@/api/admin.service'
import type {
  AdminDashboard,
  SystemStats,
  AdminUser,
  CreateUserData,
  PaginatedResponse
} from '@/api/admin.service'
import { transformUser, transformUsers, type TransformedUser } from '@/utils/userTransform'

export const useAdminStore = defineStore('admin', () => {
  // State
  const dashboard = ref<AdminDashboard | null>(null)
  const stats = ref<SystemStats | null>(null)
  const users = ref<TransformedUser[]>([])
  const currentUser = ref<TransformedUser | null>(null)
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    perPage: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const verifiedUsers = computed(() =>
    users.value.filter(u => u.email_verified_at)
  )

  const adminUsers = computed(() =>
    users.value.filter(u => u.roles.includes('admin'))
  )

  const managerUsers = computed(() =>
    users.value.filter(u => u.roles.includes('manager'))
  )

  const regularUsers = computed(() =>
    users.value.filter(u => u.roles.includes('user'))
  )

  // Dashboard Actions
  async function fetchDashboard() {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.getDashboard()
      dashboard.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch dashboard data'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Stats Actions (legacy)
  async function fetchStats() {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.getStats()
      stats.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch system statistics'
      throw err
    } finally {
      loading.value = false
    }
  }

  // User Management Actions
  async function fetchUsers(filters?: {
    page?: number
    per_page?: number
    search?: string
    roles?: string[]
    email_verified?: boolean
    sort_by?: string
    sort_direction?: 'asc' | 'desc'
  }) {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.listUsers(filters)

      // Transform API users to frontend format
      users.value = transformUsers(response.data)

      // Update pagination
      pagination.value = {
        currentPage: response.current_page,
        totalPages: Math.ceil(response.total / response.per_page),
        perPage: response.per_page,
        total: response.total
      }

      return response
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch users'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createUser(data: CreateUserData) {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.createUser(data)
      const transformedUser = transformUser(response.data)
      users.value.unshift(transformedUser)
      return transformedUser
    } catch (err: any) {
      error.value = err.message || 'Failed to create user'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateUser(id: number, data: Partial<AdminUser>) {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.updateUser(id, data)
      const transformedUser = transformUser(response.data)

      // Update in list
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = transformedUser
      }

      // Update current user if it's the same
      if (currentUser.value?.id === id) {
        currentUser.value = transformedUser
      }

      return transformedUser
    } catch (err: any) {
      error.value = err.message || 'Failed to update user'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteUser(id: number) {
    loading.value = true
    error.value = null

    try {
      await adminService.deleteUser(id)

      // Remove from list
      users.value = users.value.filter(u => u.id !== id)

      // Clear current user if it's the same
      if (currentUser.value?.id === id) {
        currentUser.value = null
      }

      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete user'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchUser(id: number) {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.getUser(id)
      const transformedUser = transformUser(response.data)
      currentUser.value = transformedUser

      // Update in list if exists
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = transformedUser
      }

      return transformedUser
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch user details'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Send welcome email to user
  async function sendWelcomeEmail(userId: number) {
    loading.value = true
    error.value = null

    try {
      const response = await adminService.sendWelcomeEmail(userId)
      return response
    } catch (err: any) {
      error.value = err.message || 'Failed to send welcome email'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Utility Actions
  function clearCurrentUser() {
    currentUser.value = null
  }

  function clearStats() {
    stats.value = null
    dashboard.value = null
  }

  function clearAdmin() {
    dashboard.value = null
    stats.value = null
    users.value = []
    currentUser.value = null
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      perPage: 15,
      total: 0
    }
  }

  return {
    // State
    dashboard,
    stats,
    users,
    currentUser,
    pagination,
    loading,
    error,

    // Computed
    verifiedUsers,
    adminUsers,
    managerUsers,
    regularUsers,

    // Dashboard Actions
    fetchDashboard,

    // Stats Actions (legacy)
    fetchStats,

    // User Management Actions
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    fetchUser,
    sendWelcomeEmail,

    // Utility Actions
    clearCurrentUser,
    clearStats,
    clearAdmin
  }
})
