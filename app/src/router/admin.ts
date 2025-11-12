// Admin routes configuration
import type { RouteRecordRaw } from 'vue-router'

export const adminRoutes: RouteRecordRaw[] = [
  {
    path: '/admin',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: () => import('@/views/admin/dashboard/AdminDashboard.vue'),
        meta: {
          title: 'Admin Dashboard',
          breadcrumb: 'Dashboard',
          icon: 'pi pi-home'
        }
      },
      // User Management Routes
      {
        path: 'users',
        name: 'AdminUserList',
        component: () => import('@/views/admin/users/UserList.vue'),
        meta: {
          title: 'User Management',
          breadcrumb: 'Users',
          icon: 'pi pi-users'
        }
      },
      {
        path: 'users/new',
        name: 'AdminUserCreate',
        component: () => import('@/views/admin/users/UserCreate.vue'),
        meta: {
          title: 'Create User',
          breadcrumb: 'Create User',
          parent: 'AdminUserList'
        }
      },
      {
        path: 'users/:id',
        name: 'AdminUserDetail',
        component: () => import('@/views/admin/users/UserDetail.vue'),
        meta: {
          title: 'User Details',
          breadcrumb: 'User Details',
          parent: 'AdminUserList'
        }
      },
      {
        path: 'users/:id/edit',
        name: 'AdminUserEdit',
        component: () => import('@/views/admin/users/UserEdit.vue'),
        meta: {
          title: 'Edit User',
          breadcrumb: 'Edit User',
          parent: 'AdminUserList'
        }
      }
    ]
  }
]

// Route guard to check admin role
export function checkAdminAccess(to: any, from: any, next: any) {
  const authStore = useAuthStore()

  if (to.meta.requiresRole === 'admin' && !authStore.hasRole('admin')) {
    // Return 404 instead of 403 for security
    next({ name: 'NotFound' })
  } else {
    next()
  }
}

// Import auth store for route guard
import { useAuthStore } from '@/stores/auth'
