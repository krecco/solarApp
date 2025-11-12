<template>
  <Card class="quick-actions-card">
    <template #header>
      <div class="flex align-items-center justify-content-between p-3 pb-0">
        <div>
          <h3 class="text-xl font-semibold m-0">{{ $t('admin.dashboard.quickActions') }}</h3>
          <p class="text-color-secondary text-sm m-0 mt-1">Frequently used administrative tasks</p>
        </div>
        <Button icon="pi pi-ellipsis-v" text rounded size="small" severity="secondary" />
      </div>
    </template>
    <template #content>
      <div class="actions-grid">
        <div
          v-for="action in actions"
          :key="action.id"
          class="action-item"
          @click="handleAction(action)"
        >
          <div class="action-icon-wrapper" :class="`action-${action.severity}`">
            <i :class="action.icon" class="action-icon"></i>
          </div>
          <div class="action-content">
            <span class="action-label">{{ action.label }}</span>
            <span class="action-description">{{ action.description }}</span>
          </div>
        </div>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'

const router = useRouter()
const { t } = useI18n()
const toast = useToast()

interface QuickAction {
  id: string
  label: string
  description: string
  icon: string
  severity: 'primary' | 'success' | 'info' | 'warning' | 'danger' | 'secondary'
  route?: string
  action?: () => void
}

const actions = ref<QuickAction[]>([
  {
    id: 'add-user',
    label: t('admin.dashboard.addUser'),
    description: 'Create a new user account',
    icon: 'pi pi-user-plus',
    severity: 'success',
    route: '/admin/users/new'
  },
  {
    id: 'manage-tenants',
    label: 'Manage Tenants',
    description: 'View and manage tenant accounts',
    icon: 'pi pi-building',
    severity: 'info',
    route: '/admin/tenants'
  },
  {
    id: 'view-events',
    label: t('admin.dashboard.viewEvents'),
    description: 'Monitor system events and logs',
    icon: 'pi pi-calendar',
    severity: 'warning',
    route: '/admin/monitoring/events'
  },
  {
    id: 'export-data',
    label: t('admin.dashboard.exportData'),
    description: 'Export system data and reports',
    icon: 'pi pi-download',
    severity: 'secondary',
    action: () => {
      toast.add({
        severity: 'info',
        summary: 'Export Started',
        detail: 'Data export is being prepared...',
        life: 3000
      })
    }
  },
  {
    id: 'system-settings',
    label: 'System Settings',
    description: 'Configure application settings',
    icon: 'pi pi-cog',
    severity: 'primary',
    route: '/admin/settings'
  },
  {
    id: 'view-analytics',
    label: 'Analytics',
    description: 'View detailed analytics and insights',
    icon: 'pi pi-chart-line',
    severity: 'danger',
    route: '/admin/analytics'
  }
])

const handleAction = (action: QuickAction) => {
  if (action.route) {
    router.push(action.route)
  } else if (action.action) {
    action.action()
  }
}
</script>

<style scoped lang="scss">
.quick-actions-card {
  height: 100%;
  border: 1px solid var(--surface-border);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  
  :deep(.p-card-header) {
    background: var(--surface-50);
    border-bottom: 1px solid var(--surface-border);
    
    .dark & {
      background: var(--surface-800);
    }
  }
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.action-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--surface-50);
  border-radius: var(--border-radius);
  border: 1px solid var(--surface-border);
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover {
    background: var(--surface-100);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    
    .action-icon-wrapper {
      transform: scale(1.1);
    }
  }
  
  .dark & {
    background: var(--surface-800);
    
    &:hover {
      background: var(--surface-700);
    }
  }
}

.action-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s ease;
  
  .action-icon {
    font-size: 1.25rem;
  }
  
  &.action-primary {
    background: rgba(59, 130, 246, 0.1);
    color: var(--blue-500);
  }
  
  &.action-success {
    background: rgba(34, 197, 94, 0.1);
    color: var(--green-500);
  }
  
  &.action-info {
    background: rgba(14, 165, 233, 0.1);
    color: var(--cyan-500);
  }
  
  &.action-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--yellow-600);
  }
  
  &.action-danger {
    background: rgba(239, 68, 68, 0.1);
    color: var(--red-500);
  }
  
  &.action-secondary {
    background: rgba(107, 114, 128, 0.1);
    color: var(--gray-500);
  }
}

.action-content {
  flex: 1;
  min-width: 0;
}

.action-label {
  display: block;
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 0.25rem;
}

.action-description {
  display: block;
  font-size: 0.75rem;
  color: var(--text-color-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 768px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .action-item {
    padding: 0.75rem;
  }
}
</style>
