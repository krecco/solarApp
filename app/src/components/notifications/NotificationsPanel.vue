<template>
  <OverlayPanel
    ref="panel"
    class="notifications-panel"
    :style="{
      minWidth: '350px',
      width: '350px',
      maxWidth: '350px'
    }"
    @hide="emit('hide')"
  >
    <!-- Header -->
    <div class="notifications-header">
      <h6>{{ t('notifications.title') }}</h6>
      <Button
        :label="t('notifications.markAllAsRead')"
        text
        size="small"
        class="text-primary"
        @click="$emit('markAllAsRead')"
      />
    </div>
    
    <Divider class="my-2" />
    
    <!-- Notifications List -->
    <div class="notifications-list">
      <div
        v-if="notifications.length === 0"
        class="empty-state"
      >
        <i class="pi pi-inbox text-4xl text-surface-300 mb-3"></i>
        <p class="text-surface-500">{{ t('notifications.noNotifications') }}</p>
      </div>
      
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="notification-item"
        :class="{ unread: !notification.read }"
        @click="$emit('selectNotification', notification)"
      >
        <div class="notification-icon-wrapper">
          <Avatar
            :icon="notification.icon"
            :class="'notification-icon ' + notification.type"
            shape="circle"
            size="normal"
            :aria-label="t('notifications.types.' + notification.type)"
          />
        </div>
        <div class="notification-content">
          <div class="notification-title">{{ notification.title }}</div>
          <div class="notification-message">{{ notification.message }}</div>
          <div class="notification-time">
            <i class="pi pi-clock text-xs mr-1"></i>
            {{ notification.time }}
          </div>
        </div>
        <Button
          v-if="!notification.read"
          icon="pi pi-circle-fill"
          text
          rounded
          size="small"
          class="mark-read-btn"
          :aria-label="t('notifications.markAsRead')"
          @click.stop="$emit('markAsRead', notification)"
        />
      </div>
    </div>
    
    <Divider class="my-2" />
    
    <!-- Footer -->
    <div class="notifications-footer">
      <Button
        :label="t('notifications.viewAllNotifications')"
        text
        class="w-full"
        @click="$emit('viewAll')"
      />
    </div>
  </OverlayPanel>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useLocale } from '@/composables/useLocale'
import OverlayPanel from 'primevue/overlaypanel'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const { t } = useLocale()

interface Notification {
  id: number
  icon: string
  type: string
  title: string
  message: string
  time: string
  read: boolean
}

defineProps<{
  notifications: Notification[]
}>()

const emit = defineEmits<{
  markAllAsRead: []
  selectNotification: [notification: Notification]
  markAsRead: [notification: Notification]
  viewAll: []
  hide: []
}>()

const panel = ref()

const toggle = (event: Event) => {
  panel.value.toggle(event)
}

const hide = () => {
  panel.value.hide()
}

defineExpose({
  toggle,
  hide
})
</script>

<style scoped>
:deep(.notifications-panel) {
  min-width: 480px;
  max-width: 520px;
  width: auto;
  background: var(--surface-0) !important;
  border: 1px solid var(--surface-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15),
              0 10px 15px -3px rgba(0, 0, 0, 0.12),
              0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 0 0 1px rgba(0, 0, 0, 0.05);

  @media (max-width: 640px) {
    min-width: unset;
    width: 90vw;
    max-width: 400px;
  }
}

:deep(.p-overlaypanel-content) {
  padding: 0;
  border-radius: 0.75rem;
  overflow: hidden;
}

.notifications-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(to right, var(--surface-50), var(--surface-100));
  border-bottom: 1px solid var(--surface-border);

  h6 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-color);
    letter-spacing: -0.025em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    
    /* Removed emoji, now using only text */
  }
}

.notifications-list {
  max-height: 400px;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.empty-state {
  padding: 3rem 1rem;
  text-align: center;
  
  i {
    display: block;
  }
  
  p {
    margin: 0;
    font-size: 0.875rem;
  }
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-bottom: 1px solid var(--surface-border);
  
  &:last-child {
    border-bottom: none;
  }
  
  &:hover {
    background: var(--surface-hover);
    padding-left: 1.75rem;
    
    .notification-icon-wrapper {
      transform: scale(1.05);
    }
  }
  
  &.unread {
    background: linear-gradient(to right, rgba(var(--primary-500-rgb), 0.04), transparent);

    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: linear-gradient(to bottom, var(--primary-400), var(--primary-600));
    }
  }
  
  .notification-icon-wrapper {
    flex-shrink: 0;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .notification-icon {
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
    
    &.success {
      background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
      color: #22c55e;
      border: 1.5px solid rgba(34, 197, 94, 0.2);
      box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
    }

    &.info {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
      color: #3b82f6;
      border: 1.5px solid rgba(59, 130, 246, 0.2);
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
    }

    &.warning {
      background: linear-gradient(135deg, rgba(251, 146, 60, 0.1), rgba(251, 146, 60, 0.05));
      color: #fb923c;
      border: 1.5px solid rgba(251, 146, 60, 0.2);
      box-shadow: 0 2px 8px rgba(251, 146, 60, 0.15);
    }

    &.danger {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
      color: #ef4444;
      border: 1.5px solid rgba(239, 68, 68, 0.2);
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
    }
  }
  
  .notification-content {
    flex: 1;
    min-width: 0;
    
    .notification-title {
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
      line-height: 1.25rem;
    }
    
    .notification-message {
      font-size: 0.813rem;
      color: var(--text-color-secondary);
      margin-bottom: 0.375rem;
      line-height: 1.25rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .notification-time {
      font-size: 0.75rem;
      color: var(--text-color-secondary);
      opacity: 0.8;
      display: flex;
      align-items: center;
    }
  }
  
  .mark-read-btn {
    flex-shrink: 0;
    align-self: center;
    
    :deep(.p-button-icon) {
      font-size: 0.5rem;
      color: var(--primary-color);
    }
  }
}

.notifications-footer {
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