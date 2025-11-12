// User routes configuration
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export const userRoutes: RouteRecordRaw[] = [
  {
    path: '/user',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true, requiresRole: 'user' },
    children: [
      {
        path: '',
        redirect: '/user/dashboard'
      },
      {
        path: 'dashboard',
        name: 'UserDashboard',
        component: () => import('@/views/user/dashboard/UserDashboard.vue'),
        meta: {
          title: 'Dashboard',
          breadcrumb: 'Dashboard',
          icon: 'pi pi-home'
        }
      }
      // Future user-specific routes can be added here:
      // - /user/profile
      // - /user/settings
      // - /user/notifications
    ]
  }
]

// Route guard to check user role
export function checkUserAccess(to: any, from: any, next: any) {
  const authStore = useAuthStore()

  if (to.meta.requiresRole === 'user' && !authStore.hasRole('user')) {
    // Return 404 instead of 403 for security
    next({ name: 'NotFound' })
  } else {
    next()
  }
}
