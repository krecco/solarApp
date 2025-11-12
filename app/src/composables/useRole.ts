/**
 * Simple role-based access control composable for admin/customer roles
 * Designed for simple 2-role system: 'admin' and 'customer'
 */

import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export const useRole = () => {
  const authStore = useAuthStore()

  // Get current user role - simple and direct
  const userRole = computed(() => {
    if (!authStore.user) return 'customer'
    return authStore.user.role || 'customer'
  })

  // Role checking functions
  const isAdmin = computed(() => userRole.value === 'admin')
  const isCustomer = computed(() => userRole.value === 'customer')

  // Permission checking functions
  const hasRole = (role: 'admin' | 'customer'): boolean => {
    return userRole.value === role
  }

  const hasAnyRole = (roles: ('admin' | 'customer')[]): boolean => {
    return roles.includes(userRole.value)
  }

  const canAccess = (requiredRole: 'admin' | 'customer'): boolean => {
    if (!authStore.user) return false
    // Admin can access everything, customer can only access customer
    if (requiredRole === 'customer') return true
    return userRole.value === 'admin'
  }

  // Common permission helpers
  const canAccessAdmin = computed(() => isAdmin.value)
  const canAccessCustomer = computed(() => isCustomer.value || isAdmin.value) // Admin can access customer areas

  return {
    // Current user role
    userRole,

    // Role checks
    isAdmin,
    isCustomer,

    // Functions
    hasRole,
    hasAnyRole,
    canAccess,

    // Common permissions
    canAccessAdmin,
    canAccessCustomer
  }
}

// Vue directive for role-based rendering
export const vRole = {
  mounted(el: HTMLElement, binding: any) {
    const authStore = useAuthStore()
    const requiredRole = binding.value

    const userRole = authStore.user?.role || 'customer'
    const hasAccess = requiredRole === 'customer' || userRole === 'admin'

    if (!hasAccess) {
      el.style.display = 'none'
    }
  },

  updated(el: HTMLElement, binding: any) {
    const authStore = useAuthStore()
    const requiredRole = binding.value

    const userRole = authStore.user?.role || 'customer'
    const hasAccess = requiredRole === 'customer' || userRole === 'admin'

    el.style.display = hasAccess ? '' : 'none'
  }
}

// Simple role checking utilities for templates
export const roleUtils = {
  isAdmin: (user: any) => user?.role === 'admin',
  isCustomer: (user: any) => user?.role === 'customer' || !user?.role,
  canAccess: (user: any, role: 'admin' | 'customer') => {
    if (role === 'customer') return true
    return user?.role === 'admin'
  }
}
