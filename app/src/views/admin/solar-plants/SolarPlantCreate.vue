<template>
  <div class="page-container">
    <!-- Modern Page Header with Back Button -->
    <div class="page-header-modern">
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
              Create Solar Plant
            </h1>
          </div>
          <p class="header-subtitle">
            Add a new solar plant to the system
          </p>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="Cancel"
          icon="pi pi-times"
          severity="secondary"
          outlined
          @click="router.back()"
        />
      </div>
    </div>

    <Card>
      <template #content>
        <form @submit.prevent="handleSubmit">
          <!-- Basic Information -->
          <h3 class="mb-3">Basic Information</h3>
          <div class="grid">
            <div class="col-12 md:col-6">
              <label for="title" class="block mb-2">Title *</label>
              <InputText
                id="title"
                v-model="form.title"
                class="w-full"
                :class="{ 'p-invalid': submitted && !form.title }"
                required
              />
              <small v-if="submitted && !form.title" class="p-error">Title is required</small>
            </div>
            <div class="col-12 md:col-6">
              <label for="location" class="block mb-2">Location</label>
              <InputText id="location" v-model="form.location" class="w-full" />
            </div>
            <div class="col-12">
              <label for="description" class="block mb-2">Description</label>
              <Textarea id="description" v-model="form.description" rows="3" class="w-full" />
            </div>
          </div>

          <!-- Address -->
          <h3 class="mb-3 mt-4">Address</h3>
          <div class="grid">
            <div class="col-12 md:col-8">
              <label for="address" class="block mb-2">Street Address</label>
              <InputText id="address" v-model="form.address" class="w-full" />
            </div>
            <div class="col-12 md:col-4">
              <label for="postal_code" class="block mb-2">Postal Code</label>
              <InputText id="postal_code" v-model="form.postal_code" class="w-full" />
            </div>
            <div class="col-12 md:col-8">
              <label for="city" class="block mb-2">City</label>
              <InputText id="city" v-model="form.city" class="w-full" />
            </div>
            <div class="col-12 md:col-4">
              <label for="country" class="block mb-2">Country</label>
              <InputText id="country" v-model="form.country" class="w-full" />
            </div>
          </div>

          <!-- Technical Specifications -->
          <h3 class="mb-3 mt-4">Technical Specifications</h3>
          <div class="grid">
            <div class="col-12 md:col-4">
              <label for="nominal_power" class="block mb-2">Nominal Power (kWp) *</label>
              <InputNumber
                id="nominal_power"
                v-model="form.nominal_power"
                class="w-full"
                :min="0"
                :maxFractionDigits="2"
                :class="{ 'p-invalid': submitted && !form.nominal_power }"
                required
              />
              <small v-if="submitted && !form.nominal_power" class="p-error">
                Nominal power is required
              </small>
            </div>
            <div class="col-12 md:col-4">
              <label for="annual_production" class="block mb-2">Annual Production (kWh)</label>
              <InputNumber
                id="annual_production"
                v-model="form.annual_production"
                class="w-full"
                :min="0"
                :maxFractionDigits="2"
              />
            </div>
            <div class="col-12 md:col-4">
              <label for="consumption" class="block mb-2">Consumption (kWh)</label>
              <InputNumber
                id="consumption"
                v-model="form.consumption"
                class="w-full"
                :min="0"
                :maxFractionDigits="2"
              />
            </div>
            <div class="col-12 md:col-4">
              <label for="peak_power" class="block mb-2">Peak Power (kWp)</label>
              <InputNumber
                id="peak_power"
                v-model="form.peak_power"
                class="w-full"
                :min="0"
                :maxFractionDigits="2"
              />
            </div>
          </div>

          <!-- Financial Information -->
          <h3 class="mb-3 mt-4">Financial Information</h3>
          <div class="grid">
            <div class="col-12 md:col-4">
              <label for="total_cost" class="block mb-2">Total Cost (€) *</label>
              <InputNumber
                id="total_cost"
                v-model="form.total_cost"
                class="w-full"
                mode="currency"
                currency="EUR"
                locale="de-DE"
                :class="{ 'p-invalid': submitted && !form.total_cost }"
                required
              />
              <small v-if="submitted && !form.total_cost" class="p-error">
                Total cost is required
              </small>
            </div>
            <div class="col-12 md:col-4">
              <label for="investment_needed" class="block mb-2">Investment Needed (€)</label>
              <InputNumber
                id="investment_needed"
                v-model="form.investment_needed"
                class="w-full"
                mode="currency"
                currency="EUR"
                locale="de-DE"
              />
            </div>
            <div class="col-12 md:col-4">
              <label for="kwh_price" class="block mb-2">kWh Price (€)</label>
              <InputNumber
                id="kwh_price"
                v-model="form.kwh_price"
                class="w-full"
                :minFractionDigits="4"
                :maxFractionDigits="4"
              />
            </div>
            <div class="col-12 md:col-6">
              <label for="contract_duration_years" class="block mb-2">Contract Duration (years)</label>
              <InputNumber
                id="contract_duration_years"
                v-model="form.contract_duration_years"
                class="w-full"
                :min="1"
                :max="50"
              />
            </div>
            <div class="col-12 md:col-6">
              <label for="interest_rate" class="block mb-2">Interest Rate (%)</label>
              <InputNumber
                id="interest_rate"
                v-model="form.interest_rate"
                class="w-full"
                :min="0"
                :max="100"
                :maxFractionDigits="2"
              />
            </div>
          </div>

          <!-- Ownership & Management -->
          <h3 class="mb-3 mt-4">Ownership & Management</h3>
          <div class="grid">
            <div class="col-12 md:col-6">
              <label for="user_id" class="block mb-2">Plant Owner *</label>
              <Dropdown
                id="user_id"
                v-model="form.user_id"
                :options="customers"
                optionLabel="name"
                optionValue="id"
                placeholder="Select owner"
                class="w-full"
                :filter="true"
                :loading="loadingCustomers"
                :class="{ 'p-invalid': submitted && !form.user_id }"
                required
              />
              <small v-if="submitted && !form.user_id" class="p-error">Owner is required</small>
            </div>
            <div class="col-12 md:col-6">
              <label for="manager_id" class="block mb-2">Assigned Manager</label>
              <Dropdown
                id="manager_id"
                v-model="form.manager_id"
                :options="managers"
                optionLabel="name"
                optionValue="id"
                placeholder="Select manager"
                class="w-full"
                :filter="true"
                :loading="loadingManagers"
              />
            </div>
          </div>

          <!-- Status & Dates -->
          <h3 class="mb-3 mt-4">Status & Dates</h3>
          <div class="grid">
            <div class="col-12 md:col-4">
              <label for="status" class="block mb-2">Status</label>
              <Dropdown
                id="status"
                v-model="form.status"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
              />
            </div>
            <div class="col-12 md:col-4">
              <label for="start_date" class="block mb-2">Start Date</label>
              <Calendar id="start_date" v-model="form.start_date" class="w-full" dateFormat="yy-mm-dd" />
            </div>
            <div class="col-12 md:col-4">
              <label for="operational_date" class="block mb-2">Operational Date</label>
              <Calendar id="operational_date" v-model="form.operational_date" class="w-full" dateFormat="yy-mm-dd" />
            </div>
          </div>

          <!-- Form Actions -->
          <div class="mt-4 flex gap-2">
            <Button
              type="submit"
              label="Create Solar Plant"
              icon="pi pi-check"
              :loading="store.loading"
            />
            <Button
              type="button"
              label="Cancel"
              icon="pi pi-times"
              severity="secondary"
              @click="router.back()"
            />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useAdminStore } from '@/stores/admin'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'

const router = useRouter()
const store = useSolarPlantStore()
const adminStore = useAdminStore()

const form = ref({
  title: '',
  description: '',
  location: '',
  address: '',
  postal_code: '',
  city: '',
  country: 'DE',
  nominal_power: 0,
  annual_production: 0,
  consumption: 0,
  peak_power: 0,
  total_cost: 0,
  investment_needed: 0,
  kwh_price: 0,
  contract_duration_years: 20,
  interest_rate: 0,
  status: 'draft',
  start_date: null,
  operational_date: null,
  user_id: null,
  manager_id: null,
})

const submitted = ref(false)
const customers = ref<any[]>([])
const managers = ref<any[]>([])
const loadingCustomers = ref(false)
const loadingManagers = ref(false)

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Funded', value: 'funded' },
  { label: 'Operational', value: 'operational' },
]

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  loadingCustomers.value = true
  loadingManagers.value = true
  try {
    // Fetch users from admin store
    await adminStore.fetchUsers({ per_page: 100 })
    customers.value = adminStore.users.filter((u: any) => u.role === 'customer' || u.role === 'user')
    managers.value = adminStore.users.filter((u: any) => u.role === 'manager' || u.role === 'admin')
  } finally {
    loadingCustomers.value = false
    loadingManagers.value = false
  }
}

async function handleSubmit() {
  submitted.value = true

  if (!form.value.title || !form.value.nominal_power || !form.value.total_cost || !form.value.user_id) {
    return
  }

  try {
    await store.createPlant(form.value as any)
    router.push({ name: 'AdminSolarPlantList' })
  } catch (error) {
    console.error('Failed to create plant:', error)
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
