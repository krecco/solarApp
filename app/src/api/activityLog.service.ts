import api from './index'

export interface ActivityLog {
  id: number
  log_name?: string
  description: string
  subject_type?: string
  subject_id?: string
  causer_type?: string
  causer_id?: number
  event?: string
  properties?: Record<string, any>
  batch_uuid?: string
  created_at: string
  updated_at: string
  subject?: any
  causer?: any
}

export interface ActivityLogFilters {
  subject_type?: string
  subject_id?: string
  causer_id?: number
  event?: string
  start_date?: string
  end_date?: string
  search?: string
  per_page?: number
  page?: number
}

export interface ActivityStatistics {
  total_activities: number
  by_event: Array<{ event: string; count: number }>
  by_subject_type: Array<{ subject_type: string; count: number }>
  recent_activity_count: {
    last_hour: number
    last_24_hours: number
    last_7_days: number
    last_30_days: number
  }
}

export const activityLogService = {
  /**
   * Get activity logs with filters
   */
  async getLogs(filters?: ActivityLogFilters) {
    const response = await api.get('/api/v1/activity-logs', { params: filters })
    return response.data
  },

  /**
   * Get single activity log
   */
  async getLog(activityId: number): Promise<ActivityLog> {
    const response = await api.get(`/api/v1/activity-logs/${activityId}`)
    return response.data.data
  },

  /**
   * Get activity statistics
   */
  async getStatistics(filters?: {
    start_date?: string
    end_date?: string
  }): Promise<ActivityStatistics> {
    const response = await api.get('/api/v1/activity-logs/statistics', { params: filters })
    return response.data.data
  },

  /**
   * Get activities for a specific model
   */
  async getForModel(
    modelType: 'investment' | 'solar_plant' | 'user' | 'repayment',
    modelId: string,
    perPage?: number
  ) {
    const response = await api.get(`/api/v1/activity-logs/model/${modelType}/${modelId}`, {
      params: { per_page: perPage },
    })
    return response.data
  },

  /**
   * Get activities by a specific user
   */
  async getByUser(userId: number, perPage?: number) {
    const response = await api.get(`/api/v1/activity-logs/user/${userId}`, {
      params: { per_page: perPage },
    })
    return response.data
  },
}

export default activityLogService
