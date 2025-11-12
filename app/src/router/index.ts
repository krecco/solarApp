import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { adminRoutes } from './admin'
import { managerRoutes } from './manager'
import { userRoutes } from './user'
import { customerRoutes } from './customer'
import { ensureAuthInitialized } from '@/middleware/authMiddleware'

// Define routes
const routes: RouteRecordRaw[] = [
  // Admin routes
  ...adminRoutes,
  // Manager routes
  ...managerRoutes,
  // User routes
  ...userRoutes,
  // Customer routes
  ...customerRoutes,
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/auth',
    name: 'Auth',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      {
        path: 'login',
        name: 'Login',
        component: () => import('@/views/auth/LoginPage.vue'),
        meta: {
          title: 'Login',
          requiresAuth: false
        }
      },
      {
        path: 'otp-request',
        name: 'otp-request',
        component: () => import('@/views/auth/OtpRequestPage.vue'),
        meta: {
          title: 'One-Time Password',
          requiresAuth: false
        }
      },
      {
        path: 'otp-verify',
        name: 'otp-verify',
        component: () => import('@/views/auth/OtpVerifyPage.vue'),
        meta: {
          title: 'Verify Code',
          requiresAuth: false
        }
      },
      {
        path: 'forgot-password',
        name: 'ForgotPassword',
        component: () => import('@/views/auth/ForgotPassword.vue'),
        meta: {
          title: 'Forgot Password',
          requiresAuth: false
        }
      },
      {
        path: 'reset-password/:token',
        name: 'ResetPassword',
        component: () => import('@/views/auth/ResetPassword.vue'),
        meta: {
          title: 'Reset Password',
          requiresAuth: false
        }
      },
      {
        path: 'verify-email/pending',
        name: 'VerifyEmailPending',
        component: () => import('@/views/auth/VerifyEmailPending.vue'),
        meta: {
          title: 'Verify Email',
          requiresAuth: false
        }
      },
      {
        path: 'verify-email/:token?',
        name: 'VerifyEmail',
        component: () => import('@/views/auth/VerifyEmail.vue'),
        meta: {
          title: 'Verify Email',
          requiresAuth: false
        }
      }
    ]
  },
  {
    path: '/dashboard',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/MainDashboard.vue'),
        meta: {
          title: 'Dashboard',
          breadcrumb: 'Dashboard'
        }
      }
    ]
  },
  {
    path: '/notifications',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Notifications',
        component: () => import('@/views/notifications/NotificationsView.vue'),
        meta: {
          title: 'Notifications',
          breadcrumb: 'Notifications'
        }
      }
    ]
  },
  {
    path: '/403',
    name: 'Forbidden',
    component: () => import('@/views/errors/403.vue'),
    meta: {
      title: 'Access Denied',
      requiresAuth: false
    }
  },
  {
    path: '/404',
    name: 'NotFound',
    component: () => import('@/views/errors/404.vue'),
    meta: {
      title: 'Page Not Found',
      requiresAuth: false
    }
  },
  {
    path: '/500',
    name: 'ServerError',
    component: () => import('@/views/errors/500.vue'),
    meta: {
      title: 'Server Error',
      requiresAuth: false
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404'
  }
]

// Create router instance
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    } else {
      return { top: 0, behavior: 'smooth' }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  // Update page title
  const defaultTitle = 'Basic Admin'
  document.title = to.meta.title ? `${to.meta.title} | ${defaultTitle}` : defaultTitle

  // Ensure auth is initialized before checking permissions
  await ensureAuthInitialized()

  // Check authentication
  const requiresAuth = to.meta.requiresAuth !== false
  const prefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
  const isAuthenticated = localStorage.getItem(`${prefix}token`) || sessionStorage.getItem(`${prefix}token`)
  const userStr = localStorage.getItem(`${prefix}user`) || sessionStorage.getItem(`${prefix}user`)

  if (requiresAuth && !isAuthenticated) {
    // Redirect to login if not authenticated
    next({
      name: 'Login',
      query: { redirect: to.fullPath }
    })
  } else if (!requiresAuth && isAuthenticated && to.name === 'Login') {
    // Redirect to role-specific dashboard if already authenticated and trying to access login
    if (userStr) {
      try {
        const user = JSON.parse(userStr)
        const role = user.role || 'user'

        // Redirect to appropriate dashboard based on role
        if (role === 'admin') {
          next({ path: '/admin/dashboard' })
        } else if (role === 'manager') {
          next({ path: '/manager/dashboard' })
        } else {
          next({ path: '/user/dashboard' })
        }
        return
      } catch (e) {
        console.error('Error parsing user data:', e)
      }
    }
    // Fallback to generic dashboard
    next({ name: 'Dashboard' })
  } else {
    // Redirect from generic dashboard to role-specific dashboard
    if (isAuthenticated && userStr && (to.path === '/dashboard' || to.name === 'Dashboard')) {
      try {
        const user = JSON.parse(userStr)
        const role = user.role || 'user'

        // Redirect to appropriate dashboard based on role
        if (role === 'admin') {
          next({ path: '/admin/dashboard' })
          return
        } else if (role === 'manager') {
          next({ path: '/manager/dashboard' })
          return
        } else if (role === 'user') {
          next({ path: '/user/dashboard' })
          return
        }
      } catch (e) {
        console.error('Error parsing user data:', e)
      }
    }

    // Check email verification for authenticated users
    if (isAuthenticated && userStr && requiresAuth) {
      try {
        const user = JSON.parse(userStr)

        // Check email verification (except for verification pages)
        if (!to.path.includes('/auth/verify-email')) {
          // Check if email is verified
          if (!user.email_verified_at && !user.emailVerified) {
            // Store the intended destination
            sessionStorage.setItem('post_verification_redirect', to.fullPath)
            sessionStorage.setItem('verification_email', user.email)
            next('/auth/verify-email/pending')
            return
          }
        }
      } catch (e) {
        console.error('Error checking user status:', e)
      }
    }

    // Check role requirements
    if (to.meta.requiresRole) {
      const userStr = localStorage.getItem(`${prefix}user`) || sessionStorage.getItem(`${prefix}user`)
      if (userStr) {
        try {
          const user = JSON.parse(userStr)

          // Simple role detection: default to user if not set
          if (!user.role) {
            user.role = 'user'
            // Update the stored user with correct role
            const storage = localStorage.getItem(`${prefix}token`) ? localStorage : sessionStorage
            storage.setItem(`${prefix}user`, JSON.stringify(user))
          }

          // Role-based permission check
          const requiredRole = to.meta.requiresRole as 'admin' | 'manager' | 'user'
          const hasPermission = user.role === requiredRole

          if (!hasPermission) {
            console.warn(`Access denied: User role '${user.role}' does not have permission for '${requiredRole}'`)
            next({ name: 'Forbidden' }) // Return 403 for permission issues
            return
          }
        } catch (e) {
          console.error('Error parsing user data:', e)
          next({
            name: 'Login',
            query: { redirect: to.fullPath }
          })
          return
        }
      } else {
        // No user data found, redirect to login
        next({
          name: 'Login',
          query: { redirect: to.fullPath }
        })
        return
      }
    }
    next()
  }
})

router.afterEach((to, from) => {
  // Analytics tracking
  if (import.meta.env.VITE_ENABLE_ANALYTICS === 'true') {
    console.log('Page view:', to.path)
  }
})

// Handle router errors
router.onError((error) => {
  console.error('Router error:', error)
  if (error && error.message && error.message.includes('Failed to fetch dynamically imported module')) {
    // Module loading error - possibly due to deployment of new version
    window.location.reload()
  }
})

export default router
