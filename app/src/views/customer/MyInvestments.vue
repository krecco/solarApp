<template>
  <div class="my-investments">
    <PageHeader title="My Investments">
      <template #actions>
        <Button
          label="New Investment"
          icon="pi pi-plus"
          @click="router.push({ name: 'CreateInvestment' })"
        />
      </template>
    </PageHeader>

    <!-- Stats Overview -->
    <div class="grid mb-3">
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="text-sm text-gray-500 mb-1">Total Invested</div>
            <div class="text-2xl font-bold text-primary">
              {{ formatCurrency(investmentStore.totalInvested) }}
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="text-sm text-gray-500 mb-1">Active Investments</div>
            <div class="text-2xl font-bold text-blue-500">
              {{ investmentStore.activeInvestments.length }}
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="text-sm text-gray-500 mb-1">Total Returns</div>
            <div class="text-2xl font-bold text-green-500">
              {{ formatCurrency(investmentStore.totalReturns) }}
            </div>
          </template>
        </Card>
      </div>
      <div class="col-12 md:col-3">
        <Card>
          <template #content>
            <div class="text-sm text-gray-500 mb-1">Expected Returns</div>
            <div class="text-2xl font-bold text-orange-500">
              {{ formatCurrency(investmentStore.expectedTotalReturns) }}
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Investments List -->
    <Card>
      <template #content>
        <DataTable
          :value="investmentStore.investments"
          :loading="investmentStore.loading"
          :paginator="true"
          :rows="15"
          dataKey="id"
          stripedRows
        >
          <Column field="solar_plant.title" header="Solar Plant">
            <template #body="{ data }">
              {{ data.solar_plant?.title || 'N/A' }}
            </template>
          </Column>
          <Column field="amount" header="Investment">
            <template #body="{ data }">
              {{ formatCurrency(data.amount) }}
            </template>
          </Column>
          <Column field="interest_rate" header="Interest Rate">
            <template #body="{ data }">
              {{ data.interest_rate }}%
            </template>
          </Column>
          <Column field="duration_months" header="Duration">
            <template #body="{ data }">
              {{ data.duration_months }} months
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>
          <Column field="verified" header="Verified">
            <template #body="{ data }">
              <i v-if="data.verified" class="pi pi-check text-green-500"></i>
              <i v-else class="pi pi-clock text-orange-500"></i>
            </template>
          </Column>
          <Column header="Actions">
            <template #body="{ data }">
              <Button
                icon="pi pi-eye"
                severity="info"
                text
                rounded
                @click="viewInvestment(data.id)"
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
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useInvestmentStore } from '@/stores/investment'
import PageHeader from '@/components/layout/PageHeader.vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'

const router = useRouter()
const investmentStore = useInvestmentStore()

onMounted(async () => {
  await investmentStore.fetchInvestments()
})

function viewInvestment(id: string) {
  router.push({ name: 'MyInvestmentDetail', params: { id } })
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

function formatCurrency(value: number | string): string {
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
  }).format(numValue)
}
</script>
