<template>
  <div class="my-plants">
    <PageHeader title="My Solar Plants">
      <template #actions>
        <Button
          label="Browse Available Plants"
          icon="pi pi-search"
          @click="router.push({ name: 'BrowsePlants' })"
          severity="secondary"
        />
      </template>
    </PageHeader>

    <!-- Stats Overview -->
    <div class="grid mb-3">
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="flex justify-content-between align-items-center">
              <div>
                <div class="text-sm text-gray-500 mb-2">Total Plants</div>
                <div class="text-2xl font-bold text-primary">{{ plantStore.plants.length }}</div>
              </div>
              <i class="pi pi-sun text-4xl text-primary-300"></i>
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="flex justify-content-between align-items-center">
              <div>
                <div class="text-sm text-gray-500 mb-2">Total Power</div>
                <div class="text-2xl font-bold text-green-600">{{ totalPower.toFixed(2) }} kWp</div>
              </div>
              <i class="pi pi-bolt text-4xl text-green-300"></i>
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="flex justify-content-between align-items-center">
              <div>
                <div class="text-sm text-gray-500 mb-2">Operational</div>
                <div class="text-2xl font-bold text-blue-600">{{ operationalCount }}</div>
              </div>
              <i class="pi pi-check-circle text-4xl text-blue-300"></i>
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="flex justify-content-between align-items-center">
              <div>
                <div class="text-sm text-gray-500 mb-2">Total Value</div>
                <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(totalValue) }}</div>
              </div>
              <i class="pi pi-euro text-4xl text-orange-300"></i>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <Card>
      <template #content>
        <!-- Filters -->
        <div class="grid mb-3">
          <div class="col-12 md:col-6">
            <InputText
              v-model="filters.search"
              placeholder="Search my plants..."
              class="w-full"
              @input="onSearch"
            />
          </div>
          <div class="col-12 md:col-4">
            <Dropdown
              v-model="filters.status"
              :options="statusOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Filter by Status"
              class="w-full"
              @change="fetchData"
            />
          </div>
          <div class="col-12 md:col-2">
            <Button
              label="Clear"
              icon="pi pi-filter-slash"
              severity="secondary"
              class="w-full"
              @click="clearFilters"
            />
          </div>
        </div>

        <!-- DataTable -->
        <DataTable
          :value="plantStore.plants"
          :loading="plantStore.loading"
          dataKey="id"
          stripedRows
        >
          <template #empty>
            <div class="text-center py-5">
              <i class="pi pi-sun text-5xl text-gray-300 mb-3"></i>
              <div class="text-xl text-gray-500">You don't own any solar plants yet</div>
              <div class="text-sm text-gray-400 mb-3">Browse available plants to start investing</div>
              <Button
                label="Browse Plants"
                icon="pi pi-search"
                @click="router.push({ name: 'BrowsePlants' })"
              />
            </div>
          </template>

          <Column field="title" header="Plant Name" sortable>
            <template #body="{ data }">
              <div class="font-semibold">{{ data.title }}</div>
              <div class="text-sm text-gray-500">{{ data.location || 'Location not specified' }}</div>
            </template>
          </Column>

          <Column field="nominal_power" header="Power" sortable>
            <template #body="{ data }">
              <div class="font-semibold">{{ data.nominal_power }} kWp</div>
              <div class="text-sm text-gray-500">Nominal capacity</div>
            </template>
          </Column>

          <Column field="commissioning_date" header="Commissioned">
            <template #body="{ data }">
              <div v-if="data.commissioning_date">
                {{ formatDate(data.commissioning_date) }}
              </div>
              <div v-else class="text-gray-400">Not yet</div>
            </template>
          </Column>

          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>

          <Column field="total_cost" header="Total Cost">
            <template #body="{ data }">
              {{ formatCurrency(data.total_cost) }}
            </template>
          </Column>

          <Column header="Actions" :exportable="false">
            <template #body="{ data }">
              <Button
                icon="pi pi-eye"
                severity="info"
                text
                rounded
                @click="viewPlant(data.id)"
                v-tooltip.top="'View Details'"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import PageHeader from '@/components/layout/PageHeader.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'

const router = useRouter()
const plantStore = useSolarPlantStore()

const filters = ref({
  search: '',
  status: '',
})

const statusOptions = [
  { label: 'All Statuses', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Funded', value: 'funded' },
  { label: 'Operational', value: 'operational' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]

const totalPower = computed(() => {
  return plantStore.plants.reduce((sum, plant) => sum + (parseFloat(plant.nominal_power) || 0), 0)
})

const operationalCount = computed(() => {
  return plantStore.plants.filter((p) => p.status === 'operational').length
})

const totalValue = computed(() => {
  return plantStore.plants.reduce((sum, plant) => sum + (parseFloat(plant.total_cost) || 0), 0)
})

let searchTimeout: any = null

onMounted(() => {
  fetchData()
})

async function fetchData() {
  await plantStore.fetchPlants(filters.value)
}

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchData()
  }, 500)
}

function clearFilters() {
  filters.value = {
    search: '',
    status: '',
  }
  fetchData()
}

function viewPlant(id: string) {
  router.push({ name: 'PlantDetail', params: { id } })
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

function formatCurrency(value: number | string): string {
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
  }).format(numValue)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<style scoped>
.my-plants {
  max-width: 1400px;
}
</style>
