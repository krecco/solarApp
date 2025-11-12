<template>
  <div class="plant-detail">
    <PageHeader :title="plant?.title || 'Plant Details'">
      <template #actions>
        <Button
          label="Back to My Plants"
          icon="pi pi-arrow-left"
          severity="secondary"
          @click="router.push({ name: 'MyPlants' })"
        />
        <Button
          label="Invest in This Plant"
          icon="pi pi-plus-circle"
          @click="router.push({ name: 'CreateInvestment', query: { plantId: route.params.id } })"
          v-if="canInvest"
        />
      </template>
    </PageHeader>

    <div v-if="loading" class="flex justify-content-center py-8">
      <ProgressSpinner />
    </div>

    <div v-else-if="plant" class="grid">
      <!-- Main Info Card -->
      <div class="col-12 lg:col-8">
        <Card class="mb-3">
          <template #title>
            <div class="flex justify-content-between align-items-center">
              <span>Plant Information</span>
              <Tag :value="plant.status" :severity="getStatusSeverity(plant.status)" />
            </div>
          </template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Plant Name</label>
                  <div class="font-semibold text-lg">{{ plant.title }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Location</label>
                  <div class="font-semibold">{{ plant.location || 'Not specified' }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Description</label>
                  <div>{{ plant.description || 'No description available' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Nominal Power</label>
                  <div class="font-semibold text-lg text-green-600">{{ plant.nominal_power }} kWp</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Expected Annual Production</label>
                  <div class="font-semibold">{{ plant.expected_annual_production || 0 }} kWh/year</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Module Type</label>
                  <div>{{ plant.module_type || 'Not specified' }}</div>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Financial Details -->
        <Card class="mb-3">
          <template #title>Financial Details</template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Total Cost</label>
                  <div class="font-semibold text-xl text-primary">{{ formatCurrency(plant.total_cost) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Funding Goal</label>
                  <div class="font-semibold">{{ formatCurrency(plant.funding_goal || 0) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Current Funding</label>
                  <div class="font-semibold text-green-600">{{ formatCurrency(plant.current_funding || 0) }}</div>
                </div>
              </div>
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Minimum Investment</label>
                  <div class="font-semibold">{{ formatCurrency(plant.minimum_investment || 0) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Expected ROI</label>
                  <div class="font-semibold text-orange-600">{{ plant.expected_roi || 0 }}% per year</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Investment Period</label>
                  <div>{{ plant.investment_period_years || 0 }} years</div>
                </div>
              </div>
            </div>

            <!-- Funding Progress -->
            <div class="field mb-3" v-if="plant.funding_goal">
              <label class="text-sm text-gray-500 mb-2">Funding Progress</label>
              <ProgressBar :value="fundingPercentage" :showValue="true" />
              <div class="text-sm text-gray-500 mt-1">
                {{ formatCurrency(plant.current_funding || 0) }} of {{ formatCurrency(plant.funding_goal) }} raised
              </div>
            </div>
          </template>
        </Card>

        <!-- Technical Specifications -->
        <Card class="mb-3">
          <template #title>Technical Specifications</template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Number of Modules</label>
                  <div class="font-semibold">{{ plant.number_of_modules || 'N/A' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Inverter Type</label>
                  <div class="font-semibold">{{ plant.inverter_type || 'N/A' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Grid Connection Date</label>
                  <div class="font-semibold">{{ formatDate(plant.grid_connection_date) || 'Not connected' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Commissioning Date</label>
                  <div class="font-semibold">{{ formatDate(plant.commissioning_date) || 'Not commissioned' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Orientation</label>
                  <div class="font-semibold">{{ plant.orientation || 'N/A' }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Tilt Angle</label>
                  <div class="font-semibold">{{ plant.tilt_angle || 'N/A' }}°</div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="col-12 lg:col-4">
        <!-- Quick Stats -->
        <Card class="mb-3">
          <template #title>Quick Stats</template>
          <template #content>
            <div class="flex align-items-center justify-content-between mb-3 pb-3 border-bottom-1 border-gray-200">
              <div>
                <div class="text-sm text-gray-500">Status</div>
                <div class="font-semibold">{{ plant.status }}</div>
              </div>
              <i class="pi pi-info-circle text-2xl text-primary-300"></i>
            </div>
            <div class="flex align-items-center justify-content-between mb-3 pb-3 border-bottom-1 border-gray-200">
              <div>
                <div class="text-sm text-gray-500">Power Output</div>
                <div class="font-semibold text-green-600">{{ plant.nominal_power }} kWp</div>
              </div>
              <i class="pi pi-bolt text-2xl text-green-300"></i>
            </div>
            <div class="flex align-items-center justify-content-between mb-3 pb-3 border-bottom-1 border-gray-200">
              <div>
                <div class="text-sm text-gray-500">Total Investment</div>
                <div class="font-semibold text-orange-600">{{ formatCurrency(plant.total_cost) }}</div>
              </div>
              <i class="pi pi-euro text-2xl text-orange-300"></i>
            </div>
            <div class="flex align-items-center justify-content-between">
              <div>
                <div class="text-sm text-gray-500">Expected ROI</div>
                <div class="font-semibold text-blue-600">{{ plant.expected_roi || 0 }}%</div>
              </div>
              <i class="pi pi-chart-line text-2xl text-blue-300"></i>
            </div>
          </template>
        </Card>

        <!-- Owner Info -->
        <Card class="mb-3" v-if="plant.owner">
          <template #title>Plant Owner</template>
          <template #content>
            <div class="flex align-items-center">
              <Avatar :label="getInitials(plant.owner.name)" size="large" shape="circle" class="mr-3" />
              <div>
                <div class="font-semibold">{{ plant.owner.name }}</div>
                <div class="text-sm text-gray-500">{{ plant.owner.email }}</div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Manager Info -->
        <Card class="mb-3" v-if="plant.manager">
          <template #title>Account Manager</template>
          <template #content>
            <div class="flex align-items-center">
              <Avatar :label="getInitials(plant.manager.name)" size="large" shape="circle" class="mr-3" />
              <div>
                <div class="font-semibold">{{ plant.manager.name }}</div>
                <div class="text-sm text-gray-500">{{ plant.manager.email }}</div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Investment CTA -->
        <Card v-if="canInvest">
          <template #content>
            <div class="text-center">
              <i class="pi pi-wallet text-5xl text-primary-300 mb-3"></i>
              <div class="text-lg font-semibold mb-2">Ready to Invest?</div>
              <div class="text-sm text-gray-500 mb-3">
                Start earning returns from this solar plant
              </div>
              <Button
                label="Create Investment"
                icon="pi pi-plus-circle"
                class="w-full"
                @click="router.push({ name: 'CreateInvestment', query: { plantId: route.params.id } })"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <i class="pi pi-exclamation-triangle text-5xl text-orange-400 mb-3"></i>
      <div class="text-xl text-gray-500">Plant not found</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import type { SolarPlant } from '@/api/solarPlant.service'
import PageHeader from '@/components/layout/PageHeader.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import ProgressSpinner from 'primevue/progressspinner'
import ProgressBar from 'primevue/progressbar'
import Avatar from 'primevue/avatar'

const router = useRouter()
const route = useRoute()
const plantStore = useSolarPlantStore()

const plant = ref<SolarPlant | null>(null)
const loading = ref(false)

const fundingPercentage = computed(() => {
  if (!plant.value || !plant.value.funding_goal) return 0
  return Math.min(((plant.value.current_funding || 0) / plant.value.funding_goal) * 100, 100)
})

const canInvest = computed(() => {
  return plant.value && ['active', 'funded'].includes(plant.value.status)
})

onMounted(async () => {
  await fetchPlant()
})

async function fetchPlant() {
  loading.value = true
  try {
    const plantId = route.params.id as string
    plant.value = await plantStore.getPlant(plantId)
  } catch (error) {
    console.error('Error fetching plant:', error)
  } finally {
    loading.value = false
  }
}

function getStatusSeverity(status: string): string {
  const severityMap: Record<string, string> = {
    draft: 'secondary',
    active: 'info',
    funded: 'success',
    operational: 'success',
    completed: 'secondary',
    cancelled: 'danger',
  }
  return severityMap[status] || 'info'
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
  }).format(value)
}

function formatDate(date?: string): string {
  if (!date) return ''
  return new Date(date).toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}
</script>

<style scoped>
.plant-detail {
  max-width: 1400px;
}

.field label {
  display: block;
  margin-bottom: 0.25rem;
}
</style>
