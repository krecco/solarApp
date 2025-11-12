import api from './index'

export interface Investment {
  id: string
  user_id: number
  solar_plant_id: string
  amount: number
  duration_months: number
  interest_rate: number
  repayment_interval: 'monthly' | 'quarterly' | 'annually'
  status: 'pending' | 'verified' | 'active' | 'completed' | 'cancelled'
  contract_status?: string
  verified: boolean
  verified_at?: string
  verified_by?: number
  file_container_id?: string
  start_date?: string
  end_date?: string
  total_repayment?: number
  total_interest?: number
  paid_amount: number
  notes?: string
  user?: any
  solar_plant?: any
  verified_by_user?: any
  repayments?: any[]
  file_container?: any
  created_at: string
  updated_at: string
}

export interface CreateInvestmentDto {
  solar_plant_id: string
  amount: number
  duration_months: number
  interest_rate: number
  repayment_interval: 'monthly' | 'quarterly' | 'annually'
  notes?: string
}

export interface InvestmentFilters {
  status?: string
  verified?: boolean
  solar_plant_id?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  page?: number
  per_page?: number
}

export const investmentService = {
  /**
   * Get list of investments with filters
   */
  async getInvestments(filters?: InvestmentFilters) {
    const response = await api.get('/v1/investments', { params: filters })
    return response.data
  },

  /**
   * Get single investment by ID
   */
  async getInvestment(id: string) {
    const response = await api.get(`/v1/investments/${id}`)
    return response.data.data
  },

  /**
   * Create new investment
   */
  async create(data: CreateInvestmentDto) {
    const response = await api.post('/v1/investments', data)
    return response.data.data
  },

  /**
   * Update existing investment
   */
  async update(id: string, data: Partial<CreateInvestmentDto>) {
    const response = await api.put(`/v1/investments/${id}`, data)
    return response.data.data
  },

  /**
   * Delete investment
   */
  async delete(id: string) {
    const response = await api.delete(`/v1/investments/${id}`)
    return response.data
  },

  /**
   * Verify investment (admin/manager only)
   */
  async verify(id: string) {
    const response = await api.post(`/v1/investments/${id}/verify`)
    return response.data.data
  },

  /**
   * Get investment statistics
   */
  async getStatistics() {
    const response = await api.get('/v1/investments/statistics')
    return response.data
  },
}

export default investmentService
