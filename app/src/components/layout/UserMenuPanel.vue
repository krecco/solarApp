<template>
  <OverlayPanel
    ref="panel"
    :style="{
      minWidth: '250px',
      width: '250px',
      maxWidth: '250px'
    }"
    class="user-menu-panel"
    @hide="emit('hide')"
  >
    <!-- Header -->
    <div class="user-menu-header">
      <div class="user-info">
        <div class="user-display-name">{{ user?.name || 'User' }}</div>
        <div class="user-email">{{ user?.email || 'user@example.com' }}</div>
        <Tag 
          :value="user?.role || 'User'" 
          :severity="getRoleSeverity(user?.role)"
          class="mt-2"
        />
      </div>
    </div>
    
    <Divider class="my-0" />
    
    <!-- Menu Items -->
    <div class="menu-items">
      <button
        v-for="item in menuItems"
        :key="item.label"
        class="menu-item"
        @click="handleItemClick(item)"
      >
        <i :class="item.icon" class="menu-item-icon"></i>
        <span class="menu-item-label">{{ item.label }}</span>
        <i v-if="item.badge" class="pi pi-circle-fill text-xs text-red-500"></i>
      </button>
    </div>
    
    <Divider class="my-0" />
    
    <!-- Footer -->
    <div class="user-menu-footer">
      <Button
        :label="t('auth.signOut')"
        text
        class="w-full"
        @click="$emit('signOut')"
      />
    </div>
  </OverlayPanel>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import OverlayPanel from 'primevue/overlaypanel'
import Avatar from 'primevue/avatar'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import Button from 'primevue/button'

const { t } = useI18n()

interface User {
  name?: string
  email?: string
  avatar?: string
  initials?: string
  role?: string
}

interface MenuItem {
  label: string
  icon: string
  command?: () => void
  badge?: boolean
}

defineProps<{
  user?: User
  menuItems: MenuItem[]
}>()

const emit = defineEmits<{
  signOut: []
  hide: []
}>()

const panel = ref()

const toggle = (event: Event) => {
  panel.value.toggle(event)
}

const hide = () => {
  panel.value.hide()
}

const handleItemClick = (item: MenuItem) => {
  if (item.command) {
    item.command()
  }
  hide()
}

const getRoleSeverity = (role?: string) => {
  switch (role) {
    case 'Admin': return 'danger'
    case 'Manager': return 'warning'
    case 'User': return 'info'
    default: return 'secondary'
  }
}

defineExpose({
  toggle,
  hide
})
</script>

<style scoped>
:deep(.user-menu-panel) {
  min-width: 320px;
  max-width: 360px;
  width: auto;
  background: var(--surface-0) !important;
  border: 1px solid var(--surface-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15),
              0 10px 15px -3px rgba(0, 0, 0, 0.12),
              0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 0 0 1px rgba(0, 0, 0, 0.05);
}

:deep(.p-overlaypanel-content) {
  padding: 0;
  border-radius: 12px;
  overflow: hidden;
}

.user-menu-header {
  padding: 2rem 1.5rem;
  text-align: center;
  background: var(--surface-50);
  position: relative;
  overflow: hidden;

  
  :deep(.p-avatar) {
    width: 5rem;
    height: 5rem;
    font-size: 2rem;
    background: var(--surface-0);
    border: 3px solid var(--surface-0);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 2;
  }
  
  .user-info {
    position: relative;
    z-index: 1;
    
    .user-display-name {
      font-weight: 700;
      color: var(--text-color);
      margin-bottom: 0.25rem;
      font-size: 1.125rem;
      letter-spacing: -0.025em;
    }
    
    .user-email {
      font-size: 0.875rem;
      color: var(--text-color-secondary);
      word-break: break-all;
      opacity: 0.85;
      margin-bottom: 0.75rem;
    }
    
    :deep(.p-tag) {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      letter-spacing: 0.025em;

      &.p-tag-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
      }

      &.p-tag-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
      }

      &.p-tag-info {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.2);
      }
    }
  }
}

.menu-items {
  padding: 0.5rem 0;
}

.menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1.5rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: left;
  color: var(--text-color);
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: var(--primary-color);
    transition: height 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  &:hover {
    background: var(--surface-hover);
    padding-left: 1.75rem;
    
    &::before {
      height: 60%;
    }
    
    .menu-item-icon {
      color: var(--primary-color);
      transform: scale(1.1);
    }
  }
  
  &:active {
    background: var(--surface-100);
  }
  
  .menu-item-icon {
    font-size: 1.125rem;
    color: var(--text-color-secondary);
    width: 1.5rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .menu-item-label {
    flex: 1;
    font-size: 0.9375rem;
    font-weight: 500;
    letter-spacing: 0.01em;
  }
}

.user-menu-footer {
  padding: 1rem;
  background: var(--surface-50);
  border-top: 1px solid var(--surface-border);

  :deep(.p-button) {
    font-weight: 600;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);

    &:hover {
      color: var(--primary-600);
      background: var(--primary-50);
    }
  }
}

</style>