import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import investmentService, { type Investment, type CreateInvestmentDto, type InvestmentFilters } from '@/api/investment.service'
import { useToast } from 'primevue/usetoast'

export const useInvestmentStore = defineStore('investment', () => {
  const toast = useToast()

  // State
  const investments = ref<Investment[]>([])
  const currentInvestment = ref<Investment | null>(null)
  const loading = ref(false)
  const statistics = ref<any>(null)
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
  })

  // Computed
  const investmentsByStatus = computed(() => {
    const grouped: Record<string, Investment[]> = {}
    investments.value.forEach((investment) => {
      if (!grouped[investment.status]) {
        grouped[investment.status] = []
      }
      grouped[investment.status].push(investment)
    })
    return grouped
  })

  const pendingInvestments = computed(() => investments.value.filter(i => i.status === 'pending'))
  const verifiedInvestments = computed(() => investments.value.filter(i => i.verified))
  const activeInvestments = computed(() => investments.value.filter(i => i.status === 'active'))
  const completedInvestments = computed(() => investments.value.filter(i => i.status === 'completed'))

  const totalInvested = computed(() => {
    return investments.value.reduce((sum, inv) => sum + inv.amount, 0)
  })

  const totalReturns = computed(() => {
    return investments.value.reduce((sum, inv) => sum + (inv.paid_amount || 0), 0)
  })

  const expectedTotalReturns = computed(() => {
    return investments.value.reduce((sum, inv) => sum + (inv.total_repayment || 0), 0)
  })

  // Actions
  async function fetchInvestments(filters?: InvestmentFilters) {
    loading.value = true
    try {
      const response = await investmentService.getInvestments(filters)
      investments.value = response.data
      pagination.value = {
        current_page: response.current_page,
        per_page: response.per_page,
        total: response.total,
        last_page: response.last_page,
      }
      return response
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to fetch investments',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchInvestment(id: string) {
    loading.value = true
    try {
      const investment = await investmentService.getInvestment(id)
      currentInvestment.value = investment
      return investment
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to fetch investment',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function createInvestment(data: CreateInvestmentDto) {
    loading.value = true
    try {
      const investment = await investmentService.create(data)
      investments.value.unshift(investment)
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Investment created successfully',
        life: 3000,
      })
      return investment
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to create investment',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updateInvestment(id: string, data: Partial<CreateInvestmentDto>) {
    loading.value = true
    try {
      const updated = await investmentService.update(id, data)
      const index = investments.value.findIndex(i => i.id === id)
      if (index !== -1) {
        investments.value[index] = updated
      }
      if (currentInvestment.value?.id === id) {
        currentInvestment.value = updated
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Investment updated successfully',
        life: 3000,
      })
      return updated
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to update investment',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function deleteInvestment(id: string) {
    loading.value = true
    try {
      await investmentService.delete(id)
      investments.value = investments.value.filter(i => i.id !== id)
      if (currentInvestment.value?.id === id) {
        currentInvestment.value = null
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Investment deleted successfully',
        life: 3000,
      })
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to delete investment',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function verifyInvestment(id: string) {
    loading.value = true
    try {
      const updated = await investmentService.verify(id)
      const index = investments.value.findIndex(i => i.id === id)
      if (index !== -1) {
        investments.value[index] = updated
      }
      if (currentInvestment.value?.id === id) {
        currentInvestment.value = updated
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Investment verified successfully',
        life: 3000,
      })
      return updated
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to verify investment',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchStatistics() {
    try {
      statistics.value = await investmentService.getStatistics()
      return statistics.value
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error)
      throw error
    }
  }

  function clearCurrentInvestment() {
    currentInvestment.value = null
  }

  function $reset() {
    investments.value = []
    currentInvestment.value = null
    loading.value = false
    statistics.value = null
    pagination.value = {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    }
  }

  return {
    // State
    investments,
    currentInvestment,
    loading,
    statistics,
    pagination,
    // Computed
    investmentsByStatus,
    pendingInvestments,
    verifiedInvestments,
    activeInvestments,
    completedInvestments,
    totalInvested,
    totalReturns,
    expectedTotalReturns,
    // Actions
    fetchInvestments,
    fetchInvestment,
    createInvestment,
    updateInvestment,
    deleteInvestment,
    verifyInvestment,
    fetchStatistics,
    clearCurrentInvestment,
    $reset,
  }
})
