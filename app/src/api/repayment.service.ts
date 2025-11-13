import api from './index'

export interface Repayment {
  id: string
  investment_id: string
  payment_number: number
  due_date: string
  amount: number
  principal_amount: number
  interest_amount: number
  status: 'pending' | 'paid' | 'overdue' | 'cancelled'
  paid_at?: string
  payment_method?: string
  payment_reference?: string
  days_overdue?: number
  late_fee?: number
  notes?: string
  investment?: any
  created_at: string
  updated_at: string
}

export interface RepaymentFilters {
  status?: string
  investment_id?: string
  start_date?: string
  end_date?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  page?: number
  per_page?: number
}

export const repaymentService = {
  /**
   * Get repayments for a specific investment
   */
  async getInvestmentRepayments(investmentId: string) {
    const response = await api.get(`/api/v1/investments/${investmentId}/repayments`)
    return response.data
  },

  /**
   * Regenerate repayment schedule for an investment (admin only)
   */
  async regenerateSchedule(investmentId: string) {
    const response = await api.post(`/api/v1/investments/${investmentId}/repayments/regenerate`)
    return response.data
  },

  /**
   * Get repayment statistics
   */
  async getStatistics() {
    const response = await api.get('/api/v1/repayments/statistics')
    return response.data
  },

  /**
   * Get overdue repayments
   */
  async getOverdue(filters?: RepaymentFilters) {
    const response = await api.get('/api/v1/repayments/overdue', { params: filters })
    return response.data
  },

  /**
   * Get upcoming repayments
   */
  async getUpcoming(daysAhead?: number) {
    const response = await api.get('/api/v1/repayments/upcoming', {
      params: { days_ahead: daysAhead },
    })
    return response.data
  },

  /**
   * Mark repayment as paid (admin/manager only)
   */
  async markAsPaid(
    repaymentId: string,
    data: {
      payment_method?: string
      payment_reference?: string
      notes?: string
    }
  ) {
    const response = await api.post(`/api/v1/repayments/${repaymentId}/mark-paid`, data)
    return response.data
  },
}

export default repaymentService
