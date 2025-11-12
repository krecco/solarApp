// Customer routes configuration
import type { RouteRecordRaw } from 'vue-router'

export const customerRoutes: RouteRecordRaw[] = [
  {
    path: '/my',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true, requiresRole: 'customer' },
    children: [
      {
        path: '',
        redirect: '/my/plants'
      },
      // My Solar Plants
      {
        path: 'plants',
        name: 'MyPlants',
        component: () => import('@/views/customer/MyPlants.vue'),
        meta: {
          title: 'My Solar Plants',
          breadcrumb: 'My Plants',
          icon: 'pi pi-sun'
        }
      },
      {
        path: 'plants/:id',
        name: 'MyPlantDetail',
        component: () => import('@/views/customer/PlantDetail.vue'),
        meta: {
          title: 'Plant Details',
          breadcrumb: 'Plant Details',
          parent: 'MyPlants'
        }
      },
      // My Investments
      {
        path: 'investments',
        name: 'MyInvestments',
        component: () => import('@/views/customer/MyInvestments.vue'),
        meta: {
          title: 'My Investments',
          breadcrumb: 'My Investments',
          icon: 'pi pi-wallet'
        }
      },
      {
        path: 'investments/new',
        name: 'CreateInvestment',
        component: () => import('@/views/customer/CreateInvestment.vue'),
        meta: {
          title: 'New Investment',
          breadcrumb: 'New Investment',
          parent: 'MyInvestments'
        }
      },
      {
        path: 'investments/:id',
        name: 'MyInvestmentDetail',
        component: () => import('@/views/customer/InvestmentDetail.vue'),
        meta: {
          title: 'Investment Details',
          breadcrumb: 'Investment Details',
          parent: 'MyInvestments'
        }
      }
    ]
  }
]

// Route guard to check customer role
export function checkCustomerAccess(to: any, from: any, next: any) {
  const authStore = useAuthStore()

  if (to.meta.requiresRole === 'customer' && !authStore.hasRole('customer')) {
    next({ name: 'Forbidden' })
  } else {
    next()
  }
}

// Import auth store for route guard
import { useAuthStore } from '@/stores/auth'
