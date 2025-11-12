<template>
  <div class="notifications-view p-4">
    <!-- Page Header -->
    <div class="mb-5">
      <div class="flex justify-content-between align-items-center mb-2">
        <h1 class="text-3xl font-bold m-0">Notifications</h1>
        <div class="flex gap-2">
          <Button
            v-if="unreadCount > 0"
            label="Mark All Read"
            icon="pi pi-check"
            severity="secondary"
            text
            @click="handleMarkAllRead"
          />
          <Button
            v-if="notifications.length > 0"
            label="Clear Read"
            icon="pi pi-trash"
            severity="danger"
            text
            @click="handleClearRead"
          />
        </div>
      </div>
      <p class="text-color-secondary m-0">
        Manage your notifications and stay updated
      </p>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-3 flex-wrap">
      <Button
        :label="`All (${notifications.length})`"
        :severity="filter === 'all' ? 'primary' : 'secondary'"
        :outlined="filter !== 'all'"
        @click="filter = 'all'"
      />
      <Button
        :label="`Unread (${unreadCount})`"
        :severity="filter === 'unread' ? 'primary' : 'secondary'"
        :outlined="filter !== 'unread'"
        @click="filter = 'unread'"
      />
      <Button
        :label="`Read (${readCount})`"
        :severity="filter === 'read' ? 'primary' : 'secondary'"
        :outlined="filter !== 'read'"
        @click="filter = 'read'"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-content-center align-items-center" style="min-height: 400px;">
      <ProgressSpinner />
    </div>

    <!-- Error State -->
    <Message v-else-if="error" severity="error" :closable="false" class="mb-4">
      <div class="flex justify-content-between align-items-center">
        <span>{{ error }}</span>
        <Button label="Retry" icon="pi pi-refresh" severity="danger" text @click="loadNotifications" />
      </div>
    </Message>

    <!-- Empty State -->
    <div v-else-if="filteredNotifications.length === 0" class="empty-state">
      <i class="pi pi-bell text-6xl text-color-secondary mb-3"></i>
      <h3 class="text-xl mb-2">No notifications</h3>
      <p class="text-color-secondary">You're all caught up!</p>
    </div>

    <!-- Notifications List -->
    <div v-else class="notifications-list">
      <Card
        v-for="notification in filteredNotifications"
        :key="notification.id"
        class="notification-card mb-3"
        :class="{ 'unread': !notification.is_read }"
      >
        <template #content>
          <div class="flex gap-3">
            <div class="notification-icon">
              <i :class="getNotificationIcon(notification.type)" class="text-2xl"></i>
            </div>
            <div class="flex-grow-1">
              <div class="flex justify-content-between align-items-start mb-2">
                <div>
                  <h4 class="m-0 mb-1 text-lg">{{ notification.title }}</h4>
                  <p class="m-0 text-color-secondary">{{ notification.message }}</p>
                </div>
                <Badge v-if="!notification.is_read" value="New" severity="info" />
              </div>
              <div class="flex justify-content-between align-items-center mt-3">
                <span class="text-sm text-color-secondary">
                  {{ formatNotificationTime(notification.created_at) }}
                </span>
                <div class="flex gap-2">
                  <Button
                    v-if="!notification.is_read"
                    label="Mark as Read"
                    icon="pi pi-check"
                    size="small"
                    text
                    @click="handleMarkAsRead(notification.id)"
                  />
                  <Button
                    label="Delete"
                    icon="pi pi-trash"
                    size="small"
                    severity="danger"
                    text
                    @click="handleDelete(notification.id)"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-content-center mt-4">
      <Paginator
        v-model:first="first"
        :rows="perPage"
        :total-records="totalRecords"
        @page="onPageChange"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Badge from 'primevue/badge'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import Paginator from 'primevue/paginator'
import { useToast } from 'primevue/usetoast'

const notificationsStore = useNotificationsStore()
const toast = useToast()

// State
const filter = ref<'all' | 'unread' | 'read'>('all')
const first = ref(0)
const perPage = ref(10)

// Computed
const notifications = computed(() => notificationsStore.notifications)
const unreadCount = computed(() => notificationsStore.unreadCount)
const loading = computed(() => notificationsStore.loading)
const error = computed(() => notificationsStore.error)
const totalRecords = computed(() => notificationsStore.total)
const totalPages = computed(() => Math.ceil(totalRecords.value / perPage.value))

const readCount = computed(() => {
  return notifications.value.filter(n => n.is_read).length
})

const filteredNotifications = computed(() => {
  if (filter.value === 'unread') {
    return notifications.value.filter(n => !n.is_read)
  } else if (filter.value === 'read') {
    return notifications.value.filter(n => n.is_read)
  }
  return notifications.value
})

// Methods
const loadNotifications = async () => {
  await notificationsStore.fetchNotifications({
    page: Math.floor(first.value / perPage.value) + 1,
    per_page: perPage.value
  })
}

const handleMarkAsRead = async (id: string) => {
  try {
    await notificationsStore.markAsRead(id)
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Notification marked as read',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to mark notification as read',
      life: 3000
    })
  }
}

const handleMarkAllRead = async () => {
  try {
    await notificationsStore.markAllAsRead()
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'All notifications marked as read',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to mark all as read',
      life: 3000
    })
  }
}

const handleClearRead = async () => {
  try {
    await notificationsStore.clearRead()
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Read notifications cleared',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to clear read notifications',
      life: 3000
    })
  }
}

const handleDelete = async (id: string) => {
  try {
    await notificationsStore.deleteNotification(id)
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Notification deleted',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to delete notification',
      life: 3000
    })
  }
}

const onPageChange = (event: any) => {
  first.value = event.first
  loadNotifications()
}

const getNotificationIcon = (type: string): string => {
  const icons: Record<string, string> = {
    system: 'pi pi-cog',
    subscription: 'pi pi-credit-card',
    tenant: 'pi pi-building',
    billing: 'pi pi-dollar',
    info: 'pi pi-info-circle',
    warning: 'pi pi-exclamation-triangle',
    error: 'pi pi-times-circle',
    success: 'pi pi-check-circle'
  }
  return icons[type] || 'pi pi-bell'
}

const formatNotificationTime = (timestamp: string): string => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`

  return date.toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  loadNotifications()
})
</script>

<style lang="scss" scoped>
.notifications-view {
  max-width: 1200px;
  margin: 0 auto;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.notifications-list {
  .notification-card {
    transition: all 0.2s;

    &.unread {
      border-left: 4px solid var(--primary-color);
      background: rgba(var(--primary-color-rgb), 0.02);
    }

    &:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
  }

  .notification-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: rgba(var(--primary-color-rgb), 0.1);
    color: var(--primary-color);
    flex-shrink: 0;
  }
}
</style>
