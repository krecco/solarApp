import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import solarPlantService, { type SolarPlant, type CreateSolarPlantDto, type SolarPlantFilters } from '@/api/solarPlant.service'
import { useToast } from 'primevue/usetoast'

export const useSolarPlantStore = defineStore('solarPlant', () => {
  const toast = useToast()

  // State
  const plants = ref<SolarPlant[]>([])
  const currentPlant = ref<SolarPlant | null>(null)
  const loading = ref(false)
  const statistics = ref<any>(null)
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
  })

  // Computed
  const plantsByStatus = computed(() => {
    const grouped: Record<string, SolarPlant[]> = {}
    if (!plants.value) return grouped
    plants.value.forEach((plant) => {
      if (!grouped[plant.status]) {
        grouped[plant.status] = []
      }
      grouped[plant.status].push(plant)
    })
    return grouped
  })

  const draftPlants = computed(() => (plants.value || []).filter(p => p.status === 'draft'))
  const activePlants = computed(() => (plants.value || []).filter(p => p.status === 'active'))
  const operationalPlants = computed(() => (plants.value || []).filter(p => p.status === 'operational'))

  // Actions
  async function fetchPlants(filters?: SolarPlantFilters) {
    loading.value = true
    try {
      const response = await solarPlantService.getPlants(filters)
      plants.value = response.data
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
        detail: error.response?.data?.message || 'Failed to fetch solar plants',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchPlant(id: string) {
    loading.value = true
    try {
      const plant = await solarPlantService.getPlant(id)
      currentPlant.value = plant
      return plant
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to fetch solar plant',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function createPlant(data: CreateSolarPlantDto) {
    loading.value = true
    try {
      const plant = await solarPlantService.create(data)
      plants.value.unshift(plant)
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Solar plant created successfully',
        life: 3000,
      })
      return plant
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to create solar plant',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updatePlant(id: string, data: Partial<CreateSolarPlantDto>) {
    loading.value = true
    try {
      const updated = await solarPlantService.update(id, data)
      const index = plants.value.findIndex(p => p.id === id)
      if (index !== -1) {
        plants.value[index] = updated
      }
      if (currentPlant.value?.id === id) {
        currentPlant.value = updated
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Solar plant updated successfully',
        life: 3000,
      })
      return updated
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to update solar plant',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function deletePlant(id: string) {
    loading.value = true
    try {
      await solarPlantService.delete(id)
      plants.value = plants.value.filter(p => p.id !== id)
      if (currentPlant.value?.id === id) {
        currentPlant.value = null
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Solar plant deleted successfully',
        life: 3000,
      })
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to delete solar plant',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updatePlantStatus(id: string, status: string) {
    loading.value = true
    try {
      const updated = await solarPlantService.updateStatus(id, status)
      const index = plants.value.findIndex(p => p.id === id)
      if (index !== -1) {
        plants.value[index] = updated
      }
      if (currentPlant.value?.id === id) {
        currentPlant.value = updated
      }
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: `Plant status changed to ${status}`,
        life: 3000,
      })
      return updated
    } catch (error: any) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: error.response?.data?.message || 'Failed to update status',
        life: 3000,
      })
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchStatistics() {
    try {
      statistics.value = await solarPlantService.getStatistics()
      return statistics.value
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error)
      throw error
    }
  }

  function clearCurrentPlant() {
    currentPlant.value = null
  }

  function $reset() {
    plants.value = []
    currentPlant.value = null
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
    plants,
    currentPlant,
    loading,
    statistics,
    pagination,
    // Computed
    plantsByStatus,
    draftPlants,
    activePlants,
    operationalPlants,
    // Actions
    fetchPlants,
    fetchPlant,
    createPlant,
    updatePlant,
    deletePlant,
    updatePlantStatus,
    fetchStatistics,
    clearCurrentPlant,
    $reset,
  }
})
