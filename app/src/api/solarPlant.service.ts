import api from './index'

export interface SolarPlant {
  id: string
  title: string
  description?: string
  location?: string
  address?: string
  postal_code?: string
  city?: string
  country: string
  nominal_power: number
  annual_production?: number
  consumption?: number
  peak_power?: number
  total_cost: number
  investment_needed?: number
  kwh_price?: number
  contract_duration_years: number
  interest_rate?: number
  monthly_forecast?: Record<string, number>
  repayment_calculation?: any
  status: 'draft' | 'active' | 'funded' | 'operational' | 'completed' | 'cancelled'
  start_date?: string
  operational_date?: string
  end_date?: string
  user_id: number
  manager_id?: number
  file_container_id?: string
  contracts_signed: boolean
  contract_signed_at?: string
  owner?: any
  manager?: any
  property_owner?: any
  extras?: any[]
  investments?: any[]
  file_container?: any
  created_at: string
  updated_at: string
}

export interface CreateSolarPlantDto {
  title: string
  description?: string
  location?: string
  address?: string
  postal_code?: string
  city?: string
  country?: string
  nominal_power: number
  annual_production?: number
  consumption?: number
  peak_power?: number
  total_cost: number
  investment_needed?: number
  kwh_price?: number
  contract_duration_years?: number
  interest_rate?: number
  status?: string
  start_date?: string
  operational_date?: string
  end_date?: string
  user_id: number
  manager_id?: number
}

export interface SolarPlantFilters {
  status?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  page?: number
  per_page?: number
}

export const solarPlantService = {
  /**
   * Get list of solar plants with filters
   */
  async getPlants(filters?: SolarPlantFilters) {
    const response = await api.get('/api/v1/solar-plants', { params: filters })
    return response  // api.get() already extracts res.data, so this is the pagination object
  },

  /**
   * Get single solar plant by ID
   */
  async getPlant(id: string) {
    const response = await api.get(`/api/v1/solar-plants/${id}`)
    return response.data  // api.get() already extracts res.data
  },

  /**
   * Create new solar plant
   */
  async create(data: CreateSolarPlantDto) {
    const response = await api.post('/api/v1/solar-plants', data)
    return response.data  // api.post() already extracts res.data
  },

  /**
   * Update existing solar plant
   */
  async update(id: string, data: Partial<CreateSolarPlantDto>) {
    const response = await api.put(`/api/v1/solar-plants/${id}`, data)
    return response.data  // api.put() already extracts res.data
  },

  /**
   * Delete solar plant
   */
  async delete(id: string) {
    const response = await api.delete(`/api/v1/solar-plants/${id}`)
    return response.data
  },

  /**
   * Update plant status
   */
  async updateStatus(id: string, status: string) {
    const response = await api.post(`/api/v1/solar-plants/${id}/status`, { status })
    return response.data  // api.post() already extracts res.data
  },

  /**
   * Get solar plant statistics
   */
  async getStatistics() {
    const response = await api.get('/api/v1/solar-plants/statistics')
    return response.data
  },
}

export default solarPlantService
