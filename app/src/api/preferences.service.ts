import { api } from './index'

export interface UserPreferences {
  language: string
  theme: string
  primaryColor: string
  timezone: string
  notifications: {
    email: boolean
    push: boolean
    system: boolean
    subscription: boolean
    tenant: boolean
  }
}

export interface PreferencesResponse {
  data: UserPreferences
  meta?: {
    status: string
    message: string
  }
}

class PreferencesService {
  /**
   * Get user preferences
   */
  async getPreferences(): Promise<PreferencesResponse> {
    return api.get<PreferencesResponse>('/api/v1/preferences')
  }

  /**
   * Update user preferences
   */
  async updatePreferences(preferences: Partial<UserPreferences>): Promise<PreferencesResponse> {
    return api.put<PreferencesResponse>('/api/v1/preferences', preferences)
  }

  /**
   * Reset preferences to default
   */
  async resetPreferences(): Promise<PreferencesResponse> {
    return api.post<PreferencesResponse>('/api/v1/preferences/reset')
  }

  /**
   * Update specific preference value
   */
  async updatePreference(key: string, value: any): Promise<PreferencesResponse> {
    const updates: any = {}

    // Handle nested keys like 'notifications.email'
    if (key.includes('.')) {
      const [parent, child] = key.split('.')
      updates[parent] = { [child]: value }
    } else {
      updates[key] = value
    }

    return this.updatePreferences(updates)
  }
}

export default new PreferencesService()
