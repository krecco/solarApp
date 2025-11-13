import api from './index'

export type SettingGroup = 'general' | 'email' | 'investment' | 'payment' | 'notification' | 'security'
export type SettingType = 'string' | 'integer' | 'boolean' | 'json' | 'decimal'

export interface Setting {
  id: string
  group: SettingGroup
  key: string
  value: any
  type: SettingType
  description?: string
  is_public: boolean
  created_at: string
  updated_at: string
}

export interface SettingValue {
  value: any
  type: SettingType
  description?: string
  is_public: boolean
}

export interface SettingsResponse {
  [group: string]: {
    [key: string]: SettingValue
  }
}

export interface CreateSettingData {
  group: SettingGroup
  key: string
  value: any
  type: SettingType
  description?: string
  is_public?: boolean
}

export interface UpdateSettingData {
  value: any
  type?: SettingType
  description?: string
  is_public?: boolean
}

export interface BulkUpdateItem {
  group: string
  key: string
  value: any
}

export const settingsService = {
  /**
   * Get public settings (no auth required)
   */
  async getPublicSettings(): Promise<SettingsResponse> {
    const response = await api.get('/api/v1/settings/public')
    return response.data.data
  },

  /**
   * Get all settings or by group
   */
  async getSettings(group?: SettingGroup): Promise<SettingsResponse> {
    const response = await api.get('/api/v1/settings', {
      params: group ? { group } : undefined,
    })
    return response.data.data
  },

  /**
   * Get single setting
   */
  async getSetting(group: string, key: string): Promise<SettingValue> {
    const response = await api.get(`/api/v1/settings/${group}/${key}`)
    return response.data.data
  },

  /**
   * Create new setting (admin only)
   */
  async create(data: CreateSettingData): Promise<Setting> {
    const response = await api.post('/api/v1/settings', data)
    return response.data.data
  },

  /**
   * Update setting (admin only)
   */
  async update(group: string, key: string, data: UpdateSettingData): Promise<Setting> {
    const response = await api.put(`/api/v1/settings/${group}/${key}`, data)
    return response.data.data
  },

  /**
   * Delete setting (admin only)
   */
  async delete(group: string, key: string) {
    const response = await api.delete(`/api/v1/settings/${group}/${key}`)
    return response.data
  },

  /**
   * Bulk update settings (admin only)
   */
  async bulkUpdate(settings: BulkUpdateItem[]) {
    const response = await api.post('/api/v1/settings/bulk-update', { settings })
    return response.data.data
  },

  /**
   * Reset settings to default (admin only)
   */
  async reset(group?: SettingGroup) {
    const response = await api.post('/api/v1/settings/reset', group ? { group } : undefined)
    return response.data.data
  },

  /**
   * Helper to get typed value
   */
  getTypedValue(setting: SettingValue): any {
    switch (setting.type) {
      case 'integer':
        return parseInt(setting.value, 10)
      case 'boolean':
        return setting.value === true || setting.value === 'true' || setting.value === 1 || setting.value === '1'
      case 'decimal':
        return parseFloat(setting.value)
      case 'json':
        return typeof setting.value === 'string' ? JSON.parse(setting.value) : setting.value
      case 'string':
      default:
        return setting.value
    }
  },
}

export default settingsService
