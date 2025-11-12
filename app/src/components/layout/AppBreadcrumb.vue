<template>
  <nav class="app-breadcrumb">
    <Breadcrumb :model="breadcrumbItems" :home="homeItem">
      <template #item="{ item }">
        <router-link v-if="item.to" :to="item.to" class="breadcrumb-link">
          <i v-if="item.icon" :class="item.icon" class="breadcrumb-icon"></i>
          <span>{{ item.label }}</span>
        </router-link>
        <span v-else class="breadcrumb-text">
          <i v-if="item.icon" :class="item.icon" class="breadcrumb-icon"></i>
          <span>{{ item.label }}</span>
        </span>
      </template>
    </Breadcrumb>
    
    <!-- Mobile Breadcrumb (Dropdown) -->
    <div class="mobile-breadcrumb">
      <Button
        :label="currentPageLabel"
        icon="pi pi-chevron-down"
        iconPos="right"
        text
        class="breadcrumb-dropdown"
        @click="toggleMobileBreadcrumb"
      />
      <OverlayPanel ref="mobileBreadcrumbPanel">
        <Menu :model="mobileMenuItems" />
      </OverlayPanel>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Breadcrumb from 'primevue/breadcrumb'
import Button from 'primevue/button'
import OverlayPanel from 'primevue/overlaypanel'
import Menu from 'primevue/menu'
import { useAppStore } from '@/stores/app'

interface BreadcrumbItem {
  label: string
  to?: string
  icon?: string
}

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const appStore = useAppStore()

// Refs
const mobileBreadcrumbPanel = ref()

// Route metadata for breadcrumb generation
const routeMetadata: Record<string, { label: string; icon?: string; parent?: string }> = {
  '/dashboard': {
    label: 'breadcrumb.dashboard',
    icon: 'pi pi-home'
  },
  '/users': {
    label: 'breadcrumb.users',
    icon: 'pi pi-users'
  },
  '/users/add': {
    label: 'breadcrumb.addUser',
    parent: '/users'
  },
  '/users/:id': {
    label: 'breadcrumb.userDetails',
    parent: '/users'
  },
  '/users/:id/edit': {
    label: 'breadcrumb.editUser',
    parent: '/users/:id'
  },
  '/products': {
    label: 'breadcrumb.products',
    icon: 'pi pi-box'
  },
  '/products/add': {
    label: 'breadcrumb.addProduct',
    parent: '/products'
  },
  '/products/categories': {
    label: 'breadcrumb.categories',
    parent: '/products'
  },
  '/orders': {
    label: 'breadcrumb.orders',
    icon: 'pi pi-shopping-cart'
  },
  '/orders/:id': {
    label: 'breadcrumb.orderDetails',
    parent: '/orders'
  },
  '/analytics': {
    label: 'breadcrumb.analytics',
    icon: 'pi pi-chart-line'
  },
  '/analytics/reports': {
    label: 'breadcrumb.reports',
    parent: '/analytics'
  },
  '/analytics/realtime': {
    label: 'breadcrumb.realtime',
    parent: '/analytics'
  },
  '/messages': {
    label: 'breadcrumb.messages',
    icon: 'pi pi-envelope'
  },
  '/calendar': {
    label: 'breadcrumb.calendar',
    icon: 'pi pi-calendar'
  },
  '/files': {
    label: 'breadcrumb.files',
    icon: 'pi pi-folder'
  },
  '/settings': {
    label: 'breadcrumb.settings',
    icon: 'pi pi-cog'
  },
  '/settings/profile': {
    label: 'breadcrumb.profile',
    parent: '/settings'
  },
  '/settings/security': {
    label: 'breadcrumb.security',
    parent: '/settings'
  },
  '/settings/billing': {
    label: 'breadcrumb.billing',
    parent: '/settings'
  },
  '/settings/team': {
    label: 'breadcrumb.team',
    parent: '/settings'
  }
}

// Generate breadcrumb items from route
const generateBreadcrumbs = (path: string): BreadcrumbItem[] => {
  const items: BreadcrumbItem[] = []
  
  // Handle dynamic routes (e.g., /users/:id)
  const normalizedPath = path.replace(/\/\d+/g, '/:id')
  
  const metadata = routeMetadata[normalizedPath]
  
  if (metadata) {
    // Add parent breadcrumbs recursively
    if (metadata.parent) {
      items.push(...generateBreadcrumbs(metadata.parent))
    }
    
    // Add current breadcrumb
    items.push({
      label: t(metadata.label),
      to: path === route.path ? undefined : path,
      icon: metadata.icon
    })
  } else {
    // Fallback for unknown routes
    const segments = path.split('/').filter(Boolean)
    segments.forEach((segment, index) => {
      const segmentPath = '/' + segments.slice(0, index + 1).join('/')
      items.push({
        label: segment.charAt(0).toUpperCase() + segment.slice(1),
        to: segmentPath === route.path ? undefined : segmentPath
      })
    })
  }
  
  return items
}

// Computed properties
const homeItem = computed(() => ({
  icon: 'pi pi-home',
  to: '/dashboard',
  label: t('breadcrumb.home')
}))

const breadcrumbItems = computed(() => {
  if (route.path === '/dashboard' || route.path === '/') {
    return []
  }
  return generateBreadcrumbs(route.path)
})

const currentPageLabel = computed(() => {
  const items = breadcrumbItems.value
  if (items.length > 0) {
    return items[items.length - 1].label
  }
  return t('breadcrumb.home')
})

const mobileMenuItems = computed(() => {
  const items: any[] = [
    {
      label: t('breadcrumb.home'),
      icon: 'pi pi-home',
      command: () => {
        router.push('/dashboard')
        mobileBreadcrumbPanel.value.hide()
      }
    }
  ]
  
  breadcrumbItems.value.forEach((item, index) => {
    if (item.to) {
      items.push({
        label: item.label,
        icon: item.icon,
        command: () => {
          router.push(item.to!)
          mobileBreadcrumbPanel.value.hide()
        }
      })
    }
  })
  
  return items
})

// Methods
const toggleMobileBreadcrumb = (event: Event) => {
  mobileBreadcrumbPanel.value.toggle(event)
}

// Watch for route changes
watch(() => route.path, () => {
  // Auto-update breadcrumbs on route change
  // The computed properties will handle this automatically
})
</script>

<style lang="scss" scoped>
.app-breadcrumb {
  padding: 1rem 1.5rem;
  background: var(--surface-ground);
  border-bottom: 1px solid var(--surface-border);
  
  // Desktop breadcrumb
  :deep(.p-breadcrumb) {
    background: transparent;
    border: none;
    padding: 0;
    
    @media (max-width: 768px) {
      display: none;
    }
    
    .p-breadcrumb-list {
      align-items: center;
      flex-wrap: wrap;
      gap: 0.25rem;
    }
    
    .p-menuitem {
      .p-menuitem-content {
        .breadcrumb-link,
        .breadcrumb-text {
          display: flex;
          align-items: center;
          gap: 0.375rem;
          padding: 0.375rem 0.75rem;
          border-radius: 0.375rem;
          transition: all 0.2s;
          text-decoration: none;
          color: var(--text-color-secondary);
          font-size: 0.875rem;
          
          &:hover {
            background: var(--surface-hover);
            color: var(--text-color);
          }
          
          .breadcrumb-icon {
            font-size: 0.875rem;
          }
        }
        
        .breadcrumb-text {
          color: var(--text-color);
          font-weight: 600;
          cursor: default;
          
          &:hover {
            background: transparent;
          }
        }
      }
      
      &:last-child {
        .p-menuitem-content {
          .breadcrumb-link,
          .breadcrumb-text {
            background: var(--primary-color-alpha);
            color: var(--primary-color);
          }
        }
      }
    }
    
    .p-breadcrumb-chevron {
      color: var(--text-color-secondary);
      font-size: 0.75rem;
    }
  }
  
  // Mobile breadcrumb
  .mobile-breadcrumb {
    display: none;
    
    @media (max-width: 768px) {
      display: block;
    }
    
    .breadcrumb-dropdown {
      font-weight: 600;
      color: var(--text-color);
      
      :deep(.p-button-icon) {
        font-size: 0.75rem;
      }
    }
  }
}

// Overlay panel for mobile
:deep(.p-overlaypanel) {
  .p-overlaypanel-content {
    padding: 0.5rem 0;
    
    .p-menu {
      border: none;
      width: 100%;
      
      .p-menuitem-link {
        padding: 0.75rem 1rem;
        
        .p-menuitem-icon {
          color: var(--text-color-secondary);
          margin-right: 0.75rem;
        }
        
        .p-menuitem-text {
          color: var(--text-color);
        }
        
        &:hover {
          background: var(--surface-hover);
        }
      }
    }
  }
}
</style>
