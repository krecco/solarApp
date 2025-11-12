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
      path: '/',
      name: 'home',
      component: () => import('@/views/dashboard/Home.vue'),
      meta: {
        pageTitle: 'Home',
        breadcrumb: [
          {
            text: 'Home',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/invoice-list',
      name: 'invoice-list',
      component: () => import('@/views/invoice/Invoice.vue'),
      meta: {
        pageTitle: 'Invoice List',
        breadcrumb: [
          {
            text: 'Invoice List',
            active: true,
          },
        ],
        resource: 'Invoices',
        action: 'read',
      },
    },
    {
      path: '/reminder-list',
      name: 'reminder-list',
      component: () => import('@/views/invoice/Reminder.vue'),
      meta: {
        pageTitle: 'Reminder List',
        breadcrumb: [
          {
            text: 'Reminder List',
            active: true,
          },
        ],
      },
    },
    {
      path: '/web-info',
      name: 'web-info',
      component: () => import('@/views/webinfo/Webinfo.vue'),
      meta: {
        pageTitle: 'WEB Nachrichten',
        breadcrumb: [
          {
            text: 'WEB Nachrichten',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    /*
    {
      path: '/web-info-detail/:id',
      name: 'web-info-detail',
      component: () => import('@/views/webinfo/Webinfo.vue'),
      meta: {
        pageTitle: 'Web Info',
        breadcrumb: [
          {
            text: 'Web Info',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    */
    {
      path: '/activity',
      name: 'activity',
      component: () => import('@/views/activity/Activity.vue'),
      meta: {
        pageTitle: 'Aktivitäten',
        breadcrumb: [
          {
            text: 'Aktivitäten',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/projects',
      name: 'projects',
      component: () => import('@/views/project/List.vue'),
      meta: {
        pageTitle: 'Unsere Projekte',
        breadcrumb: [
          {
            text: 'Projektliste',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },

    {
      path: '/power-plant-list',
      name: 'power-plant-list',
      component: () => import('@/views/power-plant/list/Display.vue'),
      meta: {
        pageTitle: 'Liste PV-Anlagen',
        breadcrumb: [
          {
            text: 'Liste PV-Anlagen',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },
    {
      path: '/power-plant-detail',
      name: 'power-plant-detail',
      component: () => import('@/views/power-plant/detail/Display.vue'),
      meta: {
        pageTitle: 'Photovoltaik-Anlage Detail',
        breadcrumb: [
          {
            text: 'Photovoltaik-Anlage Detail',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },

    {
      path: '/power-plant-edit',
      name: 'power-plant-edit',
      component: () => import('@/views/power-plant/edit/Display.vue'),
      meta: {
        pageTitle: 'Photovoltaik-Anlage bearbeiten',
        breadcrumb: [
          {
            text: 'Photovoltaik-Anlage bearbeiten',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },

    {
      path: '/power-plant-repayment-list',
      name: 'power-plant-repayment-list',
      component: () => import('@/views/power-plant-repayment/list/Display.vue'),
      meta: {
        pageTitle: 'Anlagenabrechnung',
        breadcrumb: [
          {
            text: 'Liste',
            active: true,
          },
        ],
        resource: 'Chat',
        action: 'read',
      },
    },

    {
      path: '/power-plant-repayment-report',
      name: 'power-plant-repayment-report',
      component: () => import('@/views/power-plant-repayment/report/Display.vue'),
      meta: {
        pageTitle: 'Anlagenabrechnung',
        breadcrumb: [
          {
            text: 'Report',
            active: true,
          },
        ],
        resource: 'Chat',
        action: 'read',
      },
    },

    {
      path: '/power-plant-repayment-detail',
      name: 'power-plant-repayment-detail',
      component: () => import('@/views/power-plant-repayment/detail/Display.vue'),
      meta: {
        pageTitle: 'Photovoltaik-Anlage Rückzahlung Detail',
        breadcrumb: [
          {
            text: 'Photovoltaik-Anlage Rückzahlung Detail',
            active: true,
          },
        ],
        resource: 'Chat',
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
            text: 'Investment Detail',
            active: true,
          },
        ],
        resource: 'Chat',
        action: 'read',
      },
    },

    {
      path: '/investment-list',
      name: 'investment-list',
      component: () => import('@/views/investment/list/Display.vue'),
      meta: {
        pageTitle: 'Liste Investitionen',
        breadcrumb: [
          {
            text: 'Liste Investitionen',
            active: true,
          },
        ],
        resource: 'Chat',
        action: 'read',
      },
    },

    /*
    {
      path: '/power-plants',
      name: 'power-plants',
      component: () => import('@/views/power-plant/users-list/UsersList.vue'),
      meta: {
        pageTitle: 'Power Plants',
        breadcrumb: [
          {
            text: 'Power Plants',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },
    {
      path: '/plant-detail',
      name: 'plant-detail',
      component: () => import('@/views/power-plant/users-view/UsersView.vue'),
      meta: {
        pageTitle: 'Plant Detail',
        breadcrumb: [
          {
            text: 'Plant Detail',
            active: true,
          },
        ],
        resource: 'SolarPlant',
        action: 'read',
      },
    },
    */

    {
      path: '/users',
      name: 'users',
      component: () => import('@/views/user/users-list/UsersList.vue'),
      meta: {
        pageTitle: 'Kunden',
        breadcrumb: [
          {
            text: 'Kundenliste',
            active: true,
          },
        ],
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/user-detail',
      name: 'user-detail',
      component: () => import('@/views/user/users-view/UsersView.vue'),
      meta: {
        pageTitle: 'Kunden-Cockpit',
        breadcrumb: [
          {
            text: 'Kunden-Cockpit',
            active: true,
            link: '/users',
          },
        ],
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/user-edit',
      name: 'user-edit',
      component: () => import('@/views/user/users-edit/UsersEdit.vue'),
      meta: {
        pageTitle: 'Kunde bearbeiten',
        breadcrumb: [
          {
            text: 'Kunde beearbeiten',
            active: true,
          },
        ],
        resource: 'Users',
        action: 'read',
      },
    },
    {
      path: '/web-calculator',
      name: 'web-calculator',
      component: () => import('@/views/web_page/Calculator.vue'),
      meta: {
        pageTitle: 'WC',
        breadcrumb: [
          {
            text: 'WC',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/web-calculator-iframe',
      name: 'web-calculator-iframe',
      component: () => import('@/views/web_page/Calculator.vue'),
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: false,
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
      name: 'verify-account',
      component: () => import('@/views/auth/VerifyAccount.vue'),
      props: true,
      meta: {
        layout: 'full',
        resource: 'Auth',
        redirectIfLoggedIn: true,
      },
    },
    */
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
    {
      path: '/admin-settings',
      name: 'admin-settings',
      component: () => import('@/views/admin-settings/detail/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Tarif',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/admin-settings-list',
      name: 'admin-settings-list',
      component: () => import('@/views/admin-settings/list/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Tarif',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/admin-settings-campaign',
      name: 'admin-settings-campaign',
      component: () => import('@/views/admin-settings/detail-campaign/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Kampagne',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/admin-settings-list-campaign',
      name: 'admin-settings-list-campaign',
      component: () => import('@/views/admin-settings/list-campaign/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Kampagne',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },

    {
      path: '/admin-settings-list-extras',
      name: 'admin-settings-list-extras',
      component: () => import('@/views/admin-settings/extras/list/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Zusatzleistungen',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/admin-settings-extras',
      name: 'admin-settings-extras',
      component: () => import('@/views/admin-settings/extras/detail/Display.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Zusatzleistungen',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },

    {
      path: '/chat',
      name: 'apps-chat',
      component: () => import('@/views/chat/Chat.vue'),
      meta: {
        contentRenderer: 'sidebar-left',
        contentClass: 'chat-application',
        resource: 'Chat',
        action: 'read',
      },
    },
    /*
    {
      path: '/generic-view',
      name: 'generic-view',
      component: () => import('@/views/generic-view/list/Display.vue'),
      meta: {
        pageTitle: 'Generic List',
        breadcrumb: [
          {
            text: 'Generic List',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/generic-detail',
      name: 'generic-detail',
      component: () => import('@/views/generic-view/detail/Display.vue'),
      meta: {
        pageTitle: 'Generic Detail',
        breadcrumb: [
          {
            text: 'Generic Detail',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/generic-edit',
      name: 'generic-edit',
      component: () => import('@/views/generic-view/edit/Display.vue'),
      meta: {
        pageTitle: 'Generic Edit',
        breadcrumb: [
          {
            text: 'Generic Edit',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    */
    {
      path: '/upload-test',
      name: 'upload-test',
      component: () => import('@/views/UploadTest.vue'),
      meta: {
        pageTitle: 'Einstellungen',
        breadcrumb: [
          {
            text: 'Kampagne',
            active: true,
          },
        ],
        resource: 'Dashboard',
        action: 'read',
      },
    },
    {
      path: '/workflow',
      name: 'workflow',
      component: () => import('@/views/workflow/Workflow.vue'),
      meta: {
        pageTitle: 'Workflow Demo',
        breadcrumb: [
          {
            text: 'workflow',
            active: true,
          },
        ],
      },
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

  if ((_.name === null) && (to.name !== 'home') && (to.name !== 'login') && (to.name !== 'web-calculator')) {
    return next({ name: 'home' })
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
