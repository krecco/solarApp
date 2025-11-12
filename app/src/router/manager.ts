// Manager routes configuration
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export const managerRoutes: RouteRecordRaw[] = [
  {
    path: '/manager',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true, requiresRole: 'manager' },
    children: [
      {
        path: '',
        redirect: '/manager/dashboard'
      },
      {
        path: 'dashboard',
        name: 'ManagerDashboard',
        component: () => import('@/views/manager/dashboard/ManagerDashboard.vue'),
        meta: {
          title: 'Manager Dashboard',
          breadcrumb: 'Dashboard',
          icon: 'pi pi-briefcase'
        }
      }
      // Future manager-specific routes can be added here:
      // - /manager/reports
      // - /manager/team
      // - /manager/tasks
    ]
  }
]

// Route guard to check manager role
export function checkManagerAccess(to: any, from: any, next: any) {
  const authStore = useAuthStore()

  if (to.meta.requiresRole === 'manager' && !authStore.hasRole('manager')) {
    // Return 404 instead of 403 for security
    next({ name: 'NotFound' })
  } else {
    next()
  }
}
