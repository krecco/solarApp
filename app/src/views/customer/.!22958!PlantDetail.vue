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
