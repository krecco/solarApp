import Vue from 'vue'
import VueRouter from 'vue-router'
import { canNavigate } from '@/libs/acl/routeProtection'
import { isUserLoggedIn } from '@/auth/utils'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  scrollBehavior() {
    return { x: 0, y: 0 }
  },
  routes: [
    {
      path: '/chat-lite',
      name: 'chat-lite',
      component: () => import('@/views/chat/ChatLite.vue'),
      meta: {
        resource: 'SolarPlant',
        action: 'read',
        pageTitle: 'User Chat',
      },
    },
    {
      path: '/',
      name: 'user-detail',
      component: () => import('@/views/user/users-view/UsersView.vue'),
      meta: {
        pageTitle: '',
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/user-detail-edit',
      name: 'user-detail-edit',
      component: () => import('@/views/user/users-view/UsersDetailEdit.vue'),
      meta: {
        pageTitle: 'Kundendaten',
        breadcrumb: [
          {
            text: 'Kundendaten',
            active: true,
          },
        ],
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/upload-single-file',
      name: 'upload-single-file',
      component: () => import('@/views/power-plant/detail/UploadSingeFile.vue'),
      meta: {
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/upload-power-bill',
      name: 'upload-power-bill',
      component: () => import('@/views/power-plant/detail/PowerBillUpload.vue'),
      meta: {
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/upload-roof-images',
      name: 'upload-roof-images',
      component: () => import('@/views/power-plant/detail/RoofImagesUpload.vue'),
      meta: {
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/download-offer',
      name: 'download-offer',
      component: () => import('@/views/power-plant/detail/DownloadOfferAndCalculation.vue'),
      meta: {
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/contracts',
      name: 'contracts',
      component: () => import('@/views/power-plant/detail/Contracts.vue'),
      meta: {
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/user-detail-files',
      name: 'user-detail-files',
      component: () => import('@/views/user/users-view/UsersDetailFiles.vue'),
      meta: {
        pageTitle: 'Upload Dokumente',
        breadcrumb: [
          {
            text: 'Upload Dokumente',
            active: true,
          },
        ],
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/power-plant-detail',
      name: 'power-plant-detail',
      component: () => import('@/views/power-plant/detail/Display.vue'),
      meta: {
        resource: 'SolarPlant',
        action: 'read',
      },
    },
    {
      path: '/investment-detail',
      name: 'investment-detail',
      component: () => import('@/views/investment/detail/Display.vue'),
      meta: {
        pageTitle: 'Investitionen Detail',
        breadcrumb: [
          {
            text: 'Investition',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/Login.vue'),
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    /*
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/Register.vue'),
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    */
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/auth/ForgotPassword.vue'),
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    {
      path: '/reset-password/:token/:userId',
      name: 'reset-password',
      component: () => import('@/views/auth/ResetPassword.vue'),
      props: true,
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    {
      path: '/verify-account/:token/:userId',
      name: 'reset-password',
      component: () => import('@/views/auth/VerifyAccount.vue'),
      props: true,
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    {
      path: '/error-404',
      name: 'error-404',
      component: () => import('@/views/error/Error404.vue'),
      meta: {
        layout: 'full',
      },
    },
    {
      path: '/not-authorized',
      name: 'misc-not-authorized',
      component: () => import('@/views/error/NotAuthorized.vue'),
      meta: {
        layout: 'full',
        resource: 'Auth',
      },
    },
    {
      path: '*',
      redirect: 'error-404',
    },
  ],
})

router.beforeEach((to, _, next) => {
  const isLoggedIn = isUserLoggedIn()

  if (!canNavigate(to)) {
    // Redirect to login if not logged in
    if (!isLoggedIn) return next({ name: 'login' })

    // If logged in => not authorized
    return next({ name: 'misc-not-authorized' })
  }

  // Redirect if logged in
  if (to.meta.redirectIfLoggedIn && isLoggedIn) {
    next('/')
  }

  if ((_.name === null) && (to.name !== 'user-detail') && (to.name !== 'chat-lite') && (to.name !== 'login') && (to.name !== 'reset-password')) {
    return next({ name: 'user-detail' })
  }

  return next()
})

// ? For splash screen
// Remove afterEach hook if you are not using splash screen
router.afterEach(() => {
  // Remove initial loading
  const appLoading = document.getElementById('loading-bg')
  if (appLoading) {
    appLoading.style.display = 'none'
  }
})

export default router
