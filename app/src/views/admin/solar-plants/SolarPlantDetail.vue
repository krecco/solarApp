<template>
  <div class="page-container">
    <!-- Modern Page Header with Back Button -->
    <div class="page-header-modern" v-if="plant">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <Button
              icon="pi pi-arrow-left"
              severity="secondary"
              text
              rounded
              @click="router.back()"
              v-tooltip.top="'Back'"
              class="back-btn"
            />
            <h1 class="header-title">
              <i class="pi pi-sun"></i>
              {{ plant.title }}
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="loadPlant"
              v-tooltip.top="'Refresh'"
              :loading="store.loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle">
            {{ plant.location || 'No location specified' }}
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">
              <i class="pi pi-bolt mr-2"></i>
              {{ plant.nominal_power }} kWp
            </span>
            <span class="stat-label">Nominal Power</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              <i class="pi pi-tag mr-2"></i>
              {{ capitalizeFirst(plant.status) }}
            </span>
            <span class="stat-label">Status</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              {{ formatCurrency(plant.total_cost) }}
            </span>
            <span class="stat-label">Total Cost</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="Edit"
          icon="pi pi-pencil"
          severity="primary"
          @click="router.push({ name: 'AdminSolarPlantEdit', params: { id: plant.id } })"
          v-if="isAdmin || isManager"
          :disabled="store.loading"
          class="add-user-btn"
        />
      </div>
    </div>

    <div v-if="store.loading && !plant" class="flex justify-content-center p-5">
      <ProgressSpinner />
    </div>

    <div v-else-if="plant" class="grid">
      <!-- Overview Card -->
      <div class="col-12 lg:col-8">
        <Card>
          <template #title>Plant Details</template>
          <template #content>
            <div class="grid">
              <div class="col-12">
                <Tag :value="plant.status" :severity="getStatusSeverity(plant.status)" class="mb-2" />
              </div>
              <div class="col-12 md:col-6">
                <div class="text-sm text-gray-500 mb-1">Location</div>
                <div class="font-semibold">{{ plant.location || 'N/A' }}</div>
              </div>
              <div class="col-12 md:col-6">
                <div class="text-sm text-gray-500 mb-1">Owner</div>
                <div class="font-semibold">{{ plant.owner?.name || 'N/A' }}</div>
              </div>
              <div class="col-12" v-if="plant.description">
                <div class="text-sm text-gray-500 mb-1">Description</div>
                <div>{{ plant.description }}</div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Technical Specs -->
        <Card class="mt-3">
          <template #title>Technical Specifications</template>
          <template #content>
            <div class="grid">
              <div class="col-6 md:col-3">
                <div class="text-sm text-gray-500 mb-1">Nominal Power</div>
                <div class="font-semibold">{{ plant.nominal_power }} kWp</div>
              </div>
              <div class="col-6 md:col-3" v-if="plant.annual_production">
                <div class="text-sm text-gray-500 mb-1">Annual Production</div>
                <div class="font-semibold">{{ plant.annual_production }} kWh</div>
              </div>
              <div class="col-6 md:col-3" v-if="plant.consumption">
                <div class="text-sm text-gray-500 mb-1">Consumption</div>
                <div class="font-semibold">{{ plant.consumption }} kWh</div>
              </div>
              <div class="col-6 md:col-3" v-if="plant.peak_power">
                <div class="text-sm text-gray-500 mb-1">Peak Power</div>
                <div class="font-semibold">{{ plant.peak_power }} kWp</div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Financial Info -->
        <Card class="mt-3">
          <template #title>Financial Information</template>
          <template #content>
            <div class="grid">
              <div class="col-6 md:col-4">
                <div class="text-sm text-gray-500 mb-1">Total Cost</div>
                <div class="font-semibold">{{ formatCurrency(plant.total_cost) }}</div>
              </div>
              <div class="col-6 md:col-4" v-if="plant.investment_needed">
                <div class="text-sm text-gray-500 mb-1">Investment Needed</div>
                <div class="font-semibold">{{ formatCurrency(plant.investment_needed) }}</div>
              </div>
              <div class="col-6 md:col-4" v-if="plant.kwh_price">
                <div class="text-sm text-gray-500 mb-1">kWh Price</div>
                <div class="font-semibold">{{ plant.kwh_price }} €</div>
              </div>
              <div class="col-6 md:col-4">
                <div class="text-sm text-gray-500 mb-1">Contract Duration</div>
                <div class="font-semibold">{{ plant.contract_duration_years }} years</div>
              </div>
              <div class="col-6 md:col-4" v-if="plant.interest_rate">
                <div class="text-sm text-gray-500 mb-1">Interest Rate</div>
                <div class="font-semibold">{{ plant.interest_rate }}%</div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Status & Actions -->
      <div class="col-12 lg:col-4">
        <Card>
          <template #title>Status Management</template>
          <template #content>
            <div class="mb-3">
              <label for="status" class="block mb-2">Change Status</label>
              <Dropdown
                id="status"
                v-model="newStatus"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
                :disabled="!isAdmin && !isManager"
              />
            </div>
            <Button
              label="Update Status"
              icon="pi pi-check"
              class="w-full"
              @click="updateStatus"
              :disabled="newStatus === plant.status || (!isAdmin && !isManager)"
            />
          </template>
        </Card>

        <Card class="mt-3">
          <template #title>Key Dates</template>
          <template #content>
            <div class="mb-3" v-if="plant.start_date">
              <div class="text-sm text-gray-500 mb-1">Start Date</div>
              <div>{{ formatDate(plant.start_date) }}</div>
            </div>
            <div class="mb-3" v-if="plant.operational_date">
              <div class="text-sm text-gray-500 mb-1">Operational Date</div>
              <div>{{ formatDate(plant.operational_date) }}</div>
            </div>
            <div class="mb-3">
              <div class="text-sm text-gray-500 mb-1">Created</div>
              <div>{{ formatDate(plant.created_at) }}</div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useRole } from '@/composables/useRole'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Dropdown from 'primevue/dropdown'
import ProgressSpinner from 'primevue/progressspinner'

const route = useRoute()
const router = useRouter()
const store = useSolarPlantStore()
const { isAdmin, isManager } = useRole()

const plantId = computed(() => route.params.id as string)
const plant = computed(() => store.currentPlant)
const newStatus = ref('')

const loadPlant = async () => {
  await store.fetchPlant(plantId.value)
  newStatus.value = plant.value?.status || 'draft'
}

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Funded', value: 'funded' },
  { label: 'Operational', value: 'operational' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]

onMounted(async () => {
  await store.fetchPlant(plantId.value)
  newStatus.value = plant.value?.status || 'draft'
})

async function updateStatus() {
  if (newStatus.value && plant.value) {
    await store.updatePlantStatus(plant.value.id, newStatus.value)
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

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('de-DE')
}

function capitalizeFirst(str: string): string {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
