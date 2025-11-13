<template>
  <div class="page-container">
    <!-- Modern Page Header with Refresh Inline -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-sun"></i>
              Solar Plants
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="fetchData"
              v-tooltip.top="'Refresh'"
              :loading="store.loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle">
            Manage and monitor all solar plant installations
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ store.pagination.total || 0 }}</span>
            <span class="stat-label">Total Plants</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ activeCount }}</span>
            <span class="stat-label">Active</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ totalPower }} kWp</span>
            <span class="stat-label">Total Power</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="New Plant"
          icon="pi pi-plus"
          severity="primary"
          @click="router.push({ name: 'AdminSolarPlantCreate' })"
          v-if="isAdmin || isManager"
          class="add-user-btn"
        />
      </div>
    </div>

    <Card>
      <template #content>
        <!-- Filters -->
        <div class="grid mb-3">
          <div class="col-12 md:col-4">
            <InputText
              v-model="filters.search"
              placeholder="Search plants..."
              class="w-full"
              @input="onSearch"
            />
          </div>
          <div class="col-12 md:col-3">
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
          <div class="col-12 md:col-3">
            <Dropdown
              v-model="filters.sort_by"
              :options="sortOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Sort by"
              class="w-full"
              @change="fetchData"
            />
          </div>
          <div class="col-12 md:col-2">
            <Button
              label="Clear Filters"
              icon="pi pi-filter-slash"
              severity="secondary"
              class="w-full"
              @click="clearFilters"
            />
          </div>
        </div>

        <!-- DataTable -->
        <DataTable
          :value="store.plants"
          :loading="store.loading"
          :paginator="true"
          :rows="filters.per_page"
          :totalRecords="store.pagination.total"
          :lazy="true"
          @page="onPage"
          dataKey="id"
          stripedRows
        >
          <Column field="title" header="Title" sortable>
            <template #body="{ data }">
              <div class="font-semibold">{{ data.title }}</div>
              <div class="text-sm text-gray-500">{{ data.location || 'No location' }}</div>
            </template>
          </Column>
          <Column field="nominal_power" header="Power (kWp)" sortable>
            <template #body="{ data }">
              {{ data.nominal_power }} kWp
            </template>
          </Column>
          <Column field="total_cost" header="Total Cost" sortable>
            <template #body="{ data }">
              {{ formatCurrency(data.total_cost) }}
            </template>
          </Column>
          <Column field="owner.name" header="Owner">
            <template #body="{ data }">
              {{ data.owner?.name || '-' }}
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
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
                v-tooltip.top="'View'"
              />
              <Button
                icon="pi pi-pencil"
                severity="warning"
                text
                rounded
                @click="editPlant(data.id)"
                v-tooltip.top="'Edit'"
                v-if="isAdmin || isManager"
              />
              <Button
                icon="pi pi-trash"
                severity="danger"
                text
                rounded
                @click="confirmDelete(data)"
                v-tooltip.top="'Delete'"
                v-if="isAdmin || isManager"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Delete Confirmation Dialog -->
    <Dialog
      v-model:visible="deleteDialog"
      :style="{ width: '450px' }"
      header="Confirm Delete"
      :modal="true"
    >
      <div class="confirmation-content">
        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
        <span v-if="selectedPlant">
          Are you sure you want to delete <b>{{ selectedPlant.title }}</b>?
        </span>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="deleteDialog = false" />
        <Button label="Delete" icon="pi pi-check" severity="danger" @click="deletePlant" />
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useRole } from '@/composables/useRole'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'

const router = useRouter()
const store = useSolarPlantStore()
const { isAdmin, isManager } = useRole()

const filters = ref({
  search: '',
  status: '',
  sort_by: 'created_at',
  sort_order: 'desc' as 'asc' | 'desc',
  page: 1,
  per_page: 15,
})

// Computed stats for header
const activeCount = computed(() => {
  return (store.plants || []).filter(plant => plant.status === 'active' || plant.status === 'operational').length
})

const totalPower = computed(() => {
  return (store.plants || []).reduce((sum, plant) => sum + (plant.nominal_power || 0), 0).toFixed(0)
})

const deleteDialog = ref(false)
const selectedPlant = ref<any>(null)

const statusOptions = [
  { label: 'All Statuses', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Funded', value: 'funded' },
  { label: 'Operational', value: 'operational' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]

const sortOptions = [
  { label: 'Newest First', value: 'created_at' },
  { label: 'Title', value: 'title' },
  { label: 'Power', value: 'nominal_power' },
  { label: 'Cost', value: 'total_cost' },
]

let searchTimeout: any = null

onMounted(() => {
  fetchData()
})

async function fetchData() {
  await store.fetchPlants(filters.value)
}

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchData()
  }, 500)
}

function onPage(event: any) {
  filters.value.page = event.page + 1
  fetchData()
}

function clearFilters() {
  filters.value = {
    search: '',
    status: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1,
    per_page: 15,
  }
  fetchData()
}

function viewPlant(id: string) {
  router.push({ name: 'AdminSolarPlantDetail', params: { id } })
}

function editPlant(id: string) {
  router.push({ name: 'AdminSolarPlantEdit', params: { id } })
}

function confirmDelete(plant: any) {
  selectedPlant.value = plant
  deleteDialog.value = true
}

async function deletePlant() {
  if (selectedPlant.value) {
    await store.deletePlant(selectedPlant.value.id)
    deleteDialog.value = false
    selectedPlant.value = null
    fetchData()
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
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';

.confirmation-content {
  display: flex;
  align-items: center;
  padding: 1rem;
}
</style>
