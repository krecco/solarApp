import api from './index'

export interface DashboardStats {
  total_plants: number
  active_plants: number
  total_capacity: number
  total_investments: number
  total_investment_amount: number
  active_investments: number
  pending_verification: number
  total_interest_earned: number
}

export interface InvestmentAnalytics {
  by_status: Record<string, number>
  by_month: Array<{ month: string; count: number; total_amount: number }>
  top_investors: Array<{ user_id: number; user_name: string; total_invested: number; investment_count: number }>
  total_invested: number
  total_interest: number
  average_investment: number
}

export interface RepaymentAnalytics {
  total_repayments: number
  paid_repayments: number
  pending_repayments: number
  overdue_repayments: number
  total_amount: number
  paid_amount: number
  pending_amount: number
  overdue_amount: number
  by_month: Array<{ month: string; paid: number; pending: number; overdue: number }>
}

export interface PlantAnalytics {
  by_status: Record<string, number>
  total_capacity: number
  total_cost: number
  total_investment_needed: number
  fully_funded_count: number
  funding_progress: number
}

export interface MonthlyReport {
  month: string
  year: number
  investments: {
    created: number
    verified: number
    total_amount: number
  }
  repayments: {
    paid: number
    pending: number
    overdue: number
    total_amount: number
  }
  plants: {
    created: number
    operational: number
  }
}

export interface InvestmentPerformance {
  investment_id: string
  performance_score: number
  on_time_payments: number
  total_payments: number
  overdue_count: number
  total_interest_paid: number
  remaining_balance: number
  completion_percentage: number
}

export const reportsService = {
  /**
   * Get dashboard overview statistics
   */
  async getDashboard(): Promise<DashboardStats> {
    const response = await api.get('/v1/reports/dashboard')
    return response.data.data
  },

  /**
   * Get investment analytics
   */
  async getInvestmentAnalytics(filters?: {
    start_date?: string
    end_date?: string
  }): Promise<InvestmentAnalytics> {
    const response = await api.get('/v1/reports/investments/analytics', { params: filters })
    return response.data.data
  },

  /**
   * Get repayment analytics
   */
  async getRepaymentAnalytics(filters?: {
    start_date?: string
    end_date?: string
  }): Promise<RepaymentAnalytics> {
    const response = await api.get('/v1/reports/repayments/analytics', { params: filters })
    return response.data.data
  },

  /**
   * Get plant analytics
   */
  async getPlantAnalytics(): Promise<PlantAnalytics> {
    const response = await api.get('/v1/reports/plants/analytics')
    return response.data.data
  },

  /**
   * Get monthly report
   */
  async getMonthlyReport(year: number, month: number): Promise<MonthlyReport> {
    const response = await api.get(`/v1/reports/monthly/${year}/${month}`)
    return response.data.data
  },

  /**
   * Get investment performance metrics
   */
  async getInvestmentPerformance(investmentId: string): Promise<InvestmentPerformance> {
    const response = await api.get(`/v1/reports/investments/${investmentId}/performance`)
    return response.data.data
  },

  /**
   * Export investments to CSV (admin/manager only)
   */
  async exportInvestments(filters?: {
    start_date?: string
    end_date?: string
    status?: string
  }) {
    const response = await api.post('/v1/reports/investments/export', filters)
    return response.data.data
  },

  /**
   * Download exported file
   */
  async downloadExport(filename: string) {
    const response = await api.get(`/v1/reports/download/${filename}`, {
      responseType: 'blob',
    })
    return response.data
  },
}

export default reportsService
