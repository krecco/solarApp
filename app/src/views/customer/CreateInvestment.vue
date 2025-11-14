<template>
  <div class="create-investment">
    <PageHeader title="Create Investment">
      <template #actions>
        <Button
          label="Cancel"
          icon="pi pi-times"
          severity="secondary"
          @click="handleCancel"
        />
      </template>
    </PageHeader>

    <div class="grid">
      <div class="col-12 lg:col-8">
        <Card>
          <template #title>Investment Details</template>
          <template #content>
            <form @submit.prevent="handleSubmit">
              <!-- Solar Plant Selection -->
              <div class="field mb-4">
                <label for="solar_plant_id" class="block mb-2">
                  Solar Plant <span class="text-red-500">*</span>
                </label>
                <Dropdown
                  id="solar_plant_id"
                  v-model="form.solar_plant_id"
                  :options="availablePlants"
                  optionLabel="title"
                  optionValue="id"
                  placeholder="Select a solar plant"
                  class="w-full"
                  :class="{ 'p-invalid': submitted && !form.solar_plant_id }"
                  :loading="loadingPlants"
                  @change="onPlantChange"
                  :disabled="preselectedPlantId !== null"
                >
                  <template #option="slotProps">
                    <div>
                      <div class="font-semibold">{{ slotProps.option.title }}</div>
                      <div class="text-sm text-gray-500">
                        {{ slotProps.option.nominal_power }} kWp -
                        {{ slotProps.option.expected_roi || 0 }}% ROI -
                        {{ formatCurrency(slotProps.option.minimum_investment || 0) }} min.
                      </div>
                    </div>
                  </template>
                  <template #value="slotProps">
                    <div v-if="slotProps.value">
                      {{ getPlantTitle(slotProps.value) }}
                    </div>
                    <span v-else>{{ slotProps.placeholder }}</span>
                  </template>
                </Dropdown>
                <small v-if="submitted && !form.solar_plant_id" class="p-error">
                  Please select a solar plant
                </small>

                <!-- Plant Info Card -->
                <Card v-if="selectedPlant" class="mt-3 bg-blue-50">
                  <template #content>
                    <div class="grid">
                      <div class="col-12 md:col-4">
                        <div class="text-sm text-gray-600">Minimum Investment</div>
                        <div class="font-semibold">{{ formatCurrency(selectedPlant.minimum_investment || 0) }}</div>
                      </div>
                      <div class="col-12 md:col-4">
                        <div class="text-sm text-gray-600">Expected ROI</div>
                        <div class="font-semibold text-green-600">{{ selectedPlant.expected_roi || 0 }}%</div>
                      </div>
                      <div class="col-12 md:col-4">
                        <div class="text-sm text-gray-600">Investment Period</div>
                        <div class="font-semibold">{{ selectedPlant.investment_period_years || 0 }} years</div>
                      </div>
                    </div>
                  </template>
                </Card>
              </div>

              <!-- Investment Amount -->
              <div class="field mb-4">
                <label for="amount" class="block mb-2">
                  Investment Amount (EUR) <span class="text-red-500">*</span>
                </label>
                <InputNumber
                  id="amount"
                  v-model="form.amount"
                  mode="currency"
                  currency="EUR"
                  locale="de-DE"
                  :min="selectedPlant?.minimum_investment || 0"
                  :step="100"
                  class="w-full"
                  :class="{ 'p-invalid': submitted && (!form.amount || form.amount < (selectedPlant?.minimum_investment || 0)) }"
                  @input="calculateReturns"
                />
                <small v-if="submitted && !form.amount" class="p-error">
                  Please enter investment amount
                </small>
                <small v-else-if="submitted && selectedPlant && form.amount < selectedPlant.minimum_investment" class="p-error">
                  Minimum investment is {{ formatCurrency(selectedPlant.minimum_investment) }}
                </small>
              </div>

              <div class="grid">
                <!-- Duration -->
                <div class="col-12 md:col-6">
                  <div class="field mb-4">
                    <label for="duration_months" class="block mb-2">
                      Duration (Months) <span class="text-red-500">*</span>
                    </label>
                    <InputNumber
                      id="duration_months"
                      v-model="form.duration_months"
                      :min="6"
                      :max="360"
                      :step="6"
                      class="w-full"
                      :class="{ 'p-invalid': submitted && (!form.duration_months || form.duration_months < 6) }"
                      suffix=" months"
                      @input="calculateReturns"
                    />
                    <small class="text-gray-500">
                      {{ Math.floor(form.duration_months / 12) }} years
                      {{ form.duration_months % 12 > 0 ? `and ${form.duration_months % 12} months` : '' }}
                    </small>
                    <small v-if="submitted && (!form.duration_months || form.duration_months < 6)" class="p-error block">
                      Minimum duration is 6 months
                    </small>
                  </div>
                </div>

                <!-- Interest Rate -->
                <div class="col-12 md:col-6">
                  <div class="field mb-4">
                    <label for="interest_rate" class="block mb-2">
                      Interest Rate (%) <span class="text-red-500">*</span>
                    </label>
                    <InputNumber
                      id="interest_rate"
                      v-model="form.interest_rate"
                      :min="0.1"
                      :max="20"
                      :step="0.1"
                      :minFractionDigits="2"
                      :maxFractionDigits="2"
                      class="w-full"
                      :class="{ 'p-invalid': submitted && !form.interest_rate }"
                      suffix="%"
                      @input="calculateReturns"
                    />
                    <small v-if="submitted && !form.interest_rate" class="p-error">
                      Please enter interest rate
                    </small>
                  </div>
                </div>
              </div>

              <!-- Repayment Interval -->
              <div class="field mb-4">
                <label for="repayment_interval" class="block mb-2">
                  Repayment Interval <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-3">
                  <div class="flex align-items-center">
                    <RadioButton
                      id="interval_monthly"
                      v-model="form.repayment_interval"
                      value="monthly"
                      :class="{ 'p-invalid': submitted && !form.repayment_interval }"
                    />
                    <label for="interval_monthly" class="ml-2">Monthly</label>
                  </div>
                  <div class="flex align-items-center">
                    <RadioButton
                      id="interval_quarterly"
                      v-model="form.repayment_interval"
                      value="quarterly"
                      :class="{ 'p-invalid': submitted && !form.repayment_interval }"
                    />
                    <label for="interval_quarterly" class="ml-2">Quarterly</label>
                  </div>
                  <div class="flex align-items-center">
                    <RadioButton
                      id="interval_annually"
                      v-model="form.repayment_interval"
                      value="annually"
                      :class="{ 'p-invalid': submitted && !form.repayment_interval }"
                    />
                    <label for="interval_annually" class="ml-2">Annually</label>
                  </div>
                </div>
                <small v-if="submitted && !form.repayment_interval" class="p-error">
                  Please select repayment interval
                </small>
              </div>

              <!-- Notes -->
              <div class="field mb-4">
                <label for="notes" class="block mb-2">Additional Notes</label>
                <Textarea
                  id="notes"
                  v-model="form.notes"
                  rows="4"
                  class="w-full"
                  placeholder="Any additional information or questions..."
                />
              </div>

              <!-- Submit Button -->
              <div class="flex gap-2 justify-content-end mt-4">
                <Button
                  label="Cancel"
                  severity="secondary"
                  @click="handleCancel"
                  type="button"
                />
                <Button
                  label="Create Investment"
                  icon="pi pi-check"
                  type="submit"
                  :loading="submitting"
                />
              </div>
            </form>
          </template>
        </Card>
      </div>

      <!-- Sidebar - Investment Preview -->
      <div class="col-12 lg:col-4">
        <Card class="sticky" style="top: 1rem">
          <template #title>Investment Preview</template>
          <template #content>
            <div class="mb-4 pb-3 border-bottom-1 border-gray-200">
              <div class="text-sm text-gray-500 mb-1">Investment Amount</div>
              <div class="text-2xl font-bold text-primary">{{ formatCurrency(form.amount) }}</div>
            </div>

            <div class="mb-4 pb-3 border-bottom-1 border-gray-200">
              <div class="text-sm text-gray-500 mb-1">Interest Rate</div>
              <div class="text-xl font-semibold">{{ form.interest_rate }}% p.a.</div>
            </div>

            <div class="mb-4 pb-3 border-bottom-1 border-gray-200">
              <div class="text-sm text-gray-500 mb-1">Duration</div>
              <div class="text-xl font-semibold">
                {{ Math.floor(form.duration_months / 12) }}y
                {{ form.duration_months % 12 }}m
              </div>
            </div>

            <div class="mb-4 pb-3 border-bottom-1 border-gray-200">
              <div class="text-sm text-gray-500 mb-1">Total Interest</div>
              <div class="text-xl font-semibold text-green-600">{{ formatCurrency(calculatedInterest) }}</div>
            </div>

            <div class="mb-4">
              <div class="text-sm text-gray-500 mb-1">Total Return</div>
              <div class="text-2xl font-bold text-green-600">{{ formatCurrency(calculatedTotal) }}</div>
            </div>

            <Message severity="info" :closable="false">
              <div class="text-sm">
                Your investment will be reviewed by our team and you'll receive a confirmation once approved.
              </div>
            </Message>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useInvestmentStore } from '@/stores/investment'
import type { CreateInvestmentDto } from '@/api/investment.service'
import type { SolarPlant } from '@/api/solarPlant.service'
import PageHeader from '@/components/layout/PageHeader.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Textarea from 'primevue/textarea'
import Message from 'primevue/message'

const router = useRouter()
const route = useRoute()
const plantStore = useSolarPlantStore()
const investmentStore = useInvestmentStore()

const form = ref<CreateInvestmentDto>({
  solar_plant_id: '',
  amount: 1000,
  duration_months: 60,
  interest_rate: 5.0,
  repayment_interval: 'monthly',
  notes: '',
})

const submitted = ref(false)
const submitting = ref(false)
const loadingPlants = ref(false)
const availablePlants = ref<SolarPlant[]>([])
const preselectedPlantId = ref<string | null>(null)

const selectedPlant = computed(() => {
  return availablePlants.value.find((p) => p.id === form.value.solar_plant_id)
})

const calculatedInterest = computed(() => {
  const principal = form.value.amount
  const rate = form.value.interest_rate / 100
  const years = form.value.duration_months / 12
  return principal * rate * years
})

const calculatedTotal = computed(() => {
  return form.value.amount + calculatedInterest.value
})

onMounted(async () => {
  await loadAvailablePlants()

  // Check if plant is preselected from query
  if (route.query.plantId) {
    preselectedPlantId.value = route.query.plantId as string
    form.value.solar_plant_id = preselectedPlantId.value
    onPlantChange()
  }
})

async function loadAvailablePlants() {
  loadingPlants.value = true
  try {
    await plantStore.fetchPlants({ status: 'active' })
    availablePlants.value = plantStore.plants
  } catch (error) {
    console.error('Error loading plants:', error)
  } finally {
    loadingPlants.value = false
  }
}

function onPlantChange() {
  if (selectedPlant.value) {
    // Set defaults based on plant
    if (selectedPlant.value.minimum_investment && form.value.amount < selectedPlant.value.minimum_investment) {
      form.value.amount = selectedPlant.value.minimum_investment
    }
    if (selectedPlant.value.expected_roi) {
      form.value.interest_rate = selectedPlant.value.expected_roi
    }
    if (selectedPlant.value.investment_period_years) {
      form.value.duration_months = selectedPlant.value.investment_period_years * 12
    }
    calculateReturns()
  }
}

function calculateReturns() {
  // Trigger reactivity for computed properties
}

function getPlantTitle(plantId: string): string {
  const plant = availablePlants.value.find((p) => p.id === plantId)
  return plant?.title || ''
}

async function handleSubmit() {
  submitted.value = true

  // Validation
  if (
    !form.value.solar_plant_id ||
    !form.value.amount ||
    !form.value.duration_months ||
    !form.value.interest_rate ||
    !form.value.repayment_interval
  ) {
    return
  }

  if (selectedPlant.value && form.value.amount < (selectedPlant.value.minimum_investment || 0)) {
    return
  }

  submitting.value = true
  try {
    await investmentStore.createInvestment(form.value)
    router.push({ name: 'MyInvestments' })
  } catch (error) {
    console.error('Error creating investment:', error)
  } finally {
    submitting.value = false
  }
}

function handleCancel() {
  router.back()
}

function formatCurrency(value: number | string): string {
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
  }).format(numValue)
}
</script>

<style scoped>
.create-investment {
  max-width: 1400px;
}

.field label {
  font-weight: 600;
}

.sticky {
  position: sticky;
}
</style>
