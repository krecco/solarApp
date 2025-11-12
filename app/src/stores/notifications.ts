import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import notificationsService, {
  type Notification,
  type NotificationFilters
} from '@/api/notifications.service'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const lastPage = ref(1)
  const total = ref(0)

  // Computed
  const unreadNotifications = computed(() =>
    notifications.value.filter(n => !n.is_read)
  )

  const hasUnread = computed(() => unreadCount.value > 0)

  // Actions
  async function fetchNotifications(filters?: NotificationFilters) {
    loading.value = true
    error.value = null
    try {
      const response = await notificationsService.getNotifications(filters)
      notifications.value = response.data
      currentPage.value = response.meta.current_page
      lastPage.value = response.meta.last_page
      total.value = response.meta.total
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch notifications'
      console.error('Failed to fetch notifications:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchUnreadCount() {
    try {
      const response = await notificationsService.getUnreadCount()
      unreadCount.value = response.data.count
    } catch (err: any) {
      console.error('Failed to fetch unread count:', err)
    }
  }

  async function markAsRead(id: number) {
    try {
      await notificationsService.markAsRead(id)

      // Update local state
      const notification = notifications.value.find(n => n.id === id)
      if (notification) {
        notification.is_read = true
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to mark notification as read'
      console.error('Failed to mark notification as read:', err)
      throw err
    }
  }

  async function markAllAsRead() {
    try {
      await notificationsService.markAllAsRead()

      // Update local state
      notifications.value.forEach(n => {
        n.is_read = true
        n.read_at = new Date().toISOString()
      })
      unreadCount.value = 0
    } catch (err: any) {
      error.value = err.message || 'Failed to mark all as read'
      console.error('Failed to mark all as read:', err)
      throw err
    }
  }

  async function deleteNotification(id: number) {
    try {
      await notificationsService.deleteNotification(id)

      // Update local state
      const index = notifications.value.findIndex(n => n.id === id)
      if (index !== -1) {
        const wasUnread = !notifications.value[index].is_read
        notifications.value.splice(index, 1)
        if (wasUnread) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        total.value = Math.max(0, total.value - 1)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete notification'
      console.error('Failed to delete notification:', err)
      throw err
    }
  }

  async function clearRead() {
    try {
      await notificationsService.clearRead()

      // Update local state - keep only unread notifications
      notifications.value = notifications.value.filter(n => !n.is_read)
      total.value = notifications.value.length
    } catch (err: any) {
      error.value = err.message || 'Failed to clear read notifications'
      console.error('Failed to clear read notifications:', err)
      throw err
    }
  }

  function reset() {
    notifications.value = []
    unreadCount.value = 0
    loading.value = false
    error.value = null
    currentPage.value = 1
    lastPage.value = 1
    total.value = 0
  }

  // Auto-refresh unread count periodically
  let pollInterval: number | null = null

  function startPolling(interval = 60000) {
    if (pollInterval) return

    // Initial fetch
    fetchUnreadCount()

    // Poll every interval
    pollInterval = window.setInterval(() => {
      fetchUnreadCount()
    }, interval)
  }

  function stopPolling() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
  }

  return {
    // State
    notifications,
    unreadCount,
    loading,
    error,
    currentPage,
    lastPage,
    total,

    // Computed
    unreadNotifications,
    hasUnread,

    // Actions
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearRead,
    reset,
    startPolling,
    stopPolling,
  }
})
