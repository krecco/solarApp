<template>
  <div class="page-container">
    <!-- Modern Page Header with Refresh Inline -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-wallet"></i>
              Investments
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
            Manage and monitor all investment activities
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ store.pagination.total || 0 }}</span>
            <span class="stat-label">Total Investments</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ formatCurrency(totalInvested) }}</span>
            <span class="stat-label">Total Amount</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ activeCount }}</span>
            <span class="stat-label">Active</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="Export Report"
          icon="pi pi-download"
          severity="secondary"
          outlined
          @click="exportReport"
        />
        <Button
          label="Statistics"
          icon="pi pi-chart-bar"
          severity="primary"
          @click="showStatistics = true"
          class="add-user-btn"
        />
      </div>
    </div>

    <!-- Filters Section -->
    <Card class="filters-card mb-4">
      <template #content>
        <div class="filters-grid">
          <div class="filter-item search-item">
            <IconField>
              <InputIcon>
                <i class="pi pi-search" />
              </InputIcon>
              <InputText
                v-model="filters.search"
                placeholder="Search investments..."
                class="w-full"
                @input="onSearch"
              />
            </IconField>
          </div>

          <div class="filter-item">
            <Dropdown
              v-model="filters.status"
              :options="statusOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Filter by Status"
              class="w-full"
              @change="fetchData"
            >
              <template #value="slotProps">
                <span v-if="!slotProps.value" class="filter-placeholder">
                  <i class="pi pi-filter"></i> Status
                </span>
                <span v-else class="filter-value">
                  <i class="pi pi-filter"></i> {{ statusOptions.find(s => s.value === slotProps.value)?.label }}
                </span>
              </template>
            </Dropdown>
          </div>

          <div class="filter-item">
            <Dropdown
              v-model="filters.verified"
              :options="verifiedOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Verification Status"
              class="w-full"
              @change="fetchData"
            >
              <template #value="slotProps">
                <span v-if="!slotProps.value" class="filter-placeholder">
                  <i class="pi pi-check-circle"></i> Verification
                </span>
                <span v-else class="filter-value">
                  <i class="pi pi-check-circle"></i> {{ verifiedOptions.find(v => v.value === slotProps.value)?.label }}
                </span>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Active Filter Pills -->
        <div v-if="hasActiveFilters" class="active-filter-pills">
          <Chip
            v-if="filters.status"
            removable
            @remove="filters.status = ''; fetchData()"
            class="filter-pill"
          >
            <span class="pill-content">
              <i class="pi pi-filter pill-icon"></i>
              {{ statusOptions.find(s => s.value === filters.status)?.label || filters.status }}
            </span>
          </Chip>

          <Chip
            v-if="filters.verified"
            removable
            @remove="filters.verified = ''; fetchData()"
            class="filter-pill"
          >
            <span class="pill-content">
              <i class="pi pi-check-circle pill-icon"></i>
              {{ verifiedOptions.find(v => v.value === filters.verified)?.label }}
            </span>
          </Chip>

          <Chip
            removable
            @remove="clearFilters"
            class="clear-filter-chip"
          >
            <span class="pill-content">
              <i class="pi pi-filter-slash pill-icon"></i>
              Clear All Filters
            </span>
          </Chip>
        </div>
      </template>
    </Card>

    <!-- Data Table using modern wrapper from utilities -->
    <Card class="modern-table-wrapper">
      <template #content>
        <DataTable
          :value="store.investments"
          :loading="store.loading"
          :paginator="true"
          :rows="filters.per_page"
          :totalRecords="store.pagination.total"
          :lazy="true"
          @page="onPage"
          :rowsPerPageOptions="[10, 15, 25, 50]"
          paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} investments"
          responsiveLayout="scroll"
          :rowHover="true"
          dataKey="id"
          stripedRows
          :rowClass="rowClass"
        >
          <template #loading>
            <div class="table-loading-spinner">
              <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
              <p>Loading investments...</p>
            </div>
          </template>

          <template #empty>
            <EnhancedEmptyState
              v-if="!store.loading"
              icon="pi pi-wallet"
              :title="hasActiveFilters ? 'No investments found' : 'No investments yet'"
              :description="hasActiveFilters ? 'Try adjusting your filters' : 'Investments will appear here once created'"
              :helpText="hasActiveFilters ? undefined : 'Monitor and manage all investment activities'"
              compact
            />
          </template>
          <Column field="id" header="ID" style="width: 100px">
            <template #body="{ data }">
              <span class="font-mono text-sm">{{ data.id.substring(0, 8) }}</span>
            </template>
          </Column>

          <Column field="user.name" header="Investor">
            <template #body="{ data }">
              <div v-if="data.user">
                <div class="font-semibold">{{ data.user.name }}</div>
                <div class="text-sm text-gray-500">{{ data.user.email }}</div>
              </div>
              <span v-else class="text-gray-400">N/A</span>
            </template>
          </Column>

          <Column field="solar_plant.title" header="Solar Plant">
            <template #body="{ data }">
              <div v-if="data.solar_plant">
                <div class="font-semibold">{{ data.solar_plant.title }}</div>
                <div class="text-sm text-gray-500">
                  <i class="pi pi-bolt"></i>
                  {{ data.solar_plant.nominal_power }} kWp
                </div>
              </div>
              <span v-else class="text-gray-400">N/A</span>
            </template>
          </Column>

          <Column field="amount" header="Amount" sortable>
            <template #body="{ data }">
              <div class="font-semibold">{{ formatCurrency(data.amount) }}</div>
              <div class="text-sm text-gray-500">{{ data.interest_rate }}% interest</div>
            </template>
          </Column>

          <Column field="duration_months" header="Duration">
            <template #body="{ data }">
              {{ data.duration_months }} months
            </template>
          </Column>

          <Column field="verified" header="Verified">
            <template #body="{ data }">
              <Tag
                v-if="data.verified"
                value="Yes"
                severity="success"
                icon="pi pi-check"
              />
              <Tag
                v-else
                value="No"
                severity="warning"
                icon="pi pi-clock"
              />
            </template>
          </Column>

          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>

          <Column field="created_at" header="Created" sortable>
            <template #body="{ data }">
              {{ formatDate(data.created_at) }}
            </template>
          </Column>

          <Column header="Actions" style="width: 10rem">
            <template #body="{ data }">
              <div class="action-buttons-cell">
                <Button
                  icon="pi pi-eye"
                  severity="info"
                  text
                  rounded
                  size="small"
                  @click="viewInvestment(data.id)"
                  v-tooltip.top="'View'"
                  class="action-btn view-btn"
                />
                <Button
                  icon="pi pi-check-circle"
                  severity="success"
                  text
                  rounded
                  size="small"
                  @click="confirmVerify(data)"
                  v-tooltip.top="'Verify'"
                  v-if="!data.verified && (isAdmin || isManager)"
                  class="action-btn verify-btn"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  text
                  rounded
                  size="small"
                  @click="confirmDelete(data)"
                  v-tooltip.top="'Delete'"
                  v-if="isAdmin"
                  class="action-btn delete-btn"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Verify Confirmation Dialog -->
    <Dialog
      v-model:visible="verifyDialog"
      :style="{ width: '450px' }"
      header="Verify Investment"
      :modal="true"
    >
      <div class="confirmation-content">
        <i class="pi pi-check-circle mr-3" style="font-size: 2rem; color: var(--green-500)" />
        <span v-if="selectedInvestment">
          Are you sure you want to verify this investment of
          <b>{{ formatCurrency(selectedInvestment.amount) }}</b>
          from <b>{{ selectedInvestment.user?.name }}</b>?
        </span>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="verifyDialog = false" />
        <Button
          label="Verify"
          icon="pi pi-check"
          severity="success"
          @click="verifyInvestment"
          :loading="verifying"
        />
      </template>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog
      v-model:visible="deleteDialog"
      :style="{ width: '450px' }"
      header="Confirm Delete"
      :modal="true"
    >
      <div class="confirmation-content">
        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem; color: var(--orange-500)" />
        <span v-if="selectedInvestment">
          Are you sure you want to delete this investment? This action cannot be undone.
        </span>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="deleteDialog = false" />
        <Button
          label="Delete"
          icon="pi pi-check"
          severity="danger"
          @click="deleteInvestment"
        />
      </template>
    </Dialog>

    <!-- Statistics Dialog -->
    <Dialog
      v-model:visible="showStatistics"
      :style="{ width: '600px' }"
      header="Investment Statistics"
      :modal="true"
    >
      <div v-if="statistics" class="grid">
        <div class="col-12 md:col-6">
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Total Investments</label>
            <div class="font-bold text-xl">{{ statistics.total_investments || 0 }}</div>
          </div>
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Total Amount Invested</label>
            <div class="font-bold text-xl text-primary">{{ formatCurrency(statistics.total_amount || 0) }}</div>
          </div>
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Average Investment</label>
            <div class="font-bold text-xl">{{ formatCurrency(statistics.average_investment || 0) }}</div>
          </div>
        </div>
        <div class="col-12 md:col-6">
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Active Investments</label>
            <div class="font-bold text-xl text-green-600">{{ statistics.active_investments || 0 }}</div>
          </div>
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Pending Verification</label>
            <div class="font-bold text-xl text-orange-600">{{ statistics.pending_verification || 0 }}</div>
          </div>
          <div class="field mb-3">
            <label class="text-sm text-gray-500">Completed Investments</label>
            <div class="font-bold text-xl text-blue-600">{{ statistics.completed_investments || 0 }}</div>
          </div>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useInvestmentStore } from '@/stores/investment'
import { useRole } from '@/composables/useRole'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import Chip from 'primevue/chip'

// Import UX enhancement components
import EnhancedEmptyState from '@/components/common/EnhancedEmptyState.vue'

const router = useRouter()
const store = useInvestmentStore()
const { isAdmin, isManager } = useRole()

const filters = ref({
  search: '',
  status: '',
  verified: '',
  sort_by: 'created_at',
  sort_order: 'desc' as 'asc' | 'desc',
  page: 1,
  per_page: 15,
})

const verifyDialog = ref(false)
const deleteDialog = ref(false)
const showStatistics = ref(false)
const selectedInvestment = ref<any>(null)
const verifying = ref(false)
const statistics = ref<any>(null)

const statusOptions = [
  { label: 'All Statuses', value: '' },
  { label: 'Pending', value: 'pending' },
  { label: 'Verified', value: 'verified' },
  { label: 'Active', value: 'active' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]

const verifiedOptions = [
  { label: 'All', value: '' },
  { label: 'Verified', value: 'true' },
  { label: 'Not Verified', value: 'false' },
]

const sortOptions = [
  { label: 'Newest First', value: 'created_at' },
  { label: 'Amount (High to Low)', value: 'amount' },
  { label: 'Status', value: 'status' },
]

const totalInvested = computed(() => {
  return (store.investments || []).reduce((sum, inv) => sum + inv.amount, 0)
})

const pendingCount = computed(() => {
  return (store.investments || []).filter((inv) => !inv.verified).length
})

const activeCount = computed(() => {
  return (store.investments || []).filter((inv) => inv.status === 'active').length
})

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.status || filters.value.verified
})

let searchTimeout: any = null

onMounted(() => {
  fetchData()
  loadStatistics()
})

async function fetchData() {
  const apiFilters: any = { ...filters.value }
  if (apiFilters.verified !== '') {
    apiFilters.verified = apiFilters.verified === 'true'
  } else {
    delete apiFilters.verified
  }
  await store.fetchInvestments(apiFilters)
}

async function loadStatistics() {
  try {
    statistics.value = await store.fetchStatistics()
  } catch (error) {
    console.error('Error loading statistics:', error)
  }
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
    verified: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1,
    per_page: 15,
  }
  fetchData()
}

function viewInvestment(id: string) {
  router.push({ name: 'AdminInvestmentDetail', params: { id } })
}

function confirmVerify(investment: any) {
  selectedInvestment.value = investment
  verifyDialog.value = true
}

async function verifyInvestment() {
  if (!selectedInvestment.value) return

  verifying.value = true
  try {
    await store.verifyInvestment(selectedInvestment.value.id)
    verifyDialog.value = false
    selectedInvestment.value = null
    await fetchData()
    await loadStatistics()
  } catch (error) {
    console.error('Error verifying investment:', error)
  } finally {
    verifying.value = false
  }
}

function confirmDelete(investment: any) {
  selectedInvestment.value = investment
  deleteDialog.value = true
}

async function deleteInvestment() {
  if (!selectedInvestment.value) return

  try {
    await store.deleteInvestment(selectedInvestment.value.id)
    deleteDialog.value = false
    selectedInvestment.value = null
    await fetchData()
    await loadStatistics()
  } catch (error) {
    console.error('Error deleting investment:', error)
  }
}

function exportReport() {
  // TODO: Implement export functionality
  console.log('Export report')
}

function rowClass(data: any) {
  return data.verified ? '' : 'bg-orange-50'
}

function getStatusSeverity(status: string): string {
  const severityMap: Record<string, string> = {
    pending: 'warning',
    verified: 'info',
    active: 'success',
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
  return new Date(date).toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
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
