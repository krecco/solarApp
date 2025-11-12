import { api } from './index'

export interface Notification {
  id: number
  user_id: number
  type: 'info' | 'warning' | 'error' | 'success'
  category: 'system' | 'subscription' | 'tenant' | 'feature'
  title: string
  message: string
  data?: Record<string, any>
  action_url?: string
  action_label?: string
  is_read: boolean
  read_at?: string
  created_at: string
  updated_at: string
}

export interface NotificationsListResponse {
  data: Notification[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface NotificationCountResponse {
  data: {
    count: number
  }
}

export interface NotificationActionResponse {
  meta: {
    status: string
    message: string
  }
}

export interface NotificationFilters {
  unread_only?: boolean
  category?: 'system' | 'subscription' | 'tenant' | 'feature'
  type?: 'info' | 'warning' | 'error' | 'success'
  per_page?: number
  page?: number
}

class NotificationsService {
  /**
   * Get user notifications with optional filters
   */
  async getNotifications(filters?: NotificationFilters): Promise<NotificationsListResponse> {
    return api.get<NotificationsListResponse>('/api/v1/notifications', { params: filters })
  }

  /**
   * Get unread notifications count
   */
  async getUnreadCount(): Promise<NotificationCountResponse> {
    return api.get<NotificationCountResponse>('/api/v1/notifications/unread-count')
  }

  /**
   * Mark a notification as read
   */
  async markAsRead(id: number): Promise<NotificationActionResponse> {
    return api.post<NotificationActionResponse>(`/api/v1/notifications/${id}/read`)
  }

  /**
   * Mark all notifications as read
   */
  async markAllAsRead(): Promise<NotificationActionResponse> {
    return api.post<NotificationActionResponse>('/api/v1/notifications/mark-all-read')
  }

  /**
   * Delete a notification
   */
  async deleteNotification(id: number): Promise<NotificationActionResponse> {
    return api.delete<NotificationActionResponse>(`/api/v1/notifications/${id}`)
  }

  /**
   * Clear all read notifications
   */
  async clearRead(): Promise<NotificationActionResponse> {
    return api.delete<NotificationActionResponse>('/api/v1/notifications/clear-read')
  }
}

export default new NotificationsService()
