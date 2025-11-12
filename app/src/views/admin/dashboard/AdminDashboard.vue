<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-chart-bar"></i>
              Admin Dashboard
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="refreshDashboard"
              v-tooltip.top="'Refresh'"
              :loading="loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle">
            System overview and user statistics
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ dashboardData?.users?.total || 0 }}</span>
            <span class="stat-label">Total Users</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ dashboardData?.users?.verified || 0 }}</span>
            <span class="stat-label">Verified Users</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ dashboardData?.users?.created_today || 0 }}</span>
            <span class="stat-label">New Today</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="View Users"
          icon="pi pi-users"
          severity="primary"
          @click="navigateTo('/admin/users')"
          class="add-user-btn"
        />
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid stats-section px-2">
      <div class="col-12 md:col-6 lg:col-3">
        <Card class="stat-card">
          <template #content>
            <div class="stat-content">
              <div class="stat-info">
                <div class="stat-icon bg-blue-100">
                  <i class="pi pi-users text-blue-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Total Users</span>
                  <span class="stat-value">{{ dashboardData?.users?.total || 0 }}</span>
                  <span class="stat-subtitle">{{ dashboardData?.users?.verified || 0 }} verified</span>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card class="stat-card">
          <template #content>
            <div class="stat-content">
              <div class="stat-info">
                <div class="stat-icon bg-green-100">
                  <i class="pi pi-user-plus text-green-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">New Today</span>
                  <span class="stat-value">{{ dashboardData?.users?.created_today || 0 }}</span>
                  <span class="stat-subtitle">{{ dashboardData?.users?.created_this_week || 0 }} this week</span>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card class="stat-card">
          <template #content>
            <div class="stat-content">
              <div class="stat-info">
                <div class="stat-icon bg-purple-100">
                  <i class="pi pi-shield text-purple-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Admins</span>
                  <span class="stat-value">{{ dashboardData?.roles?.admins || 0 }}</span>
                  <span class="stat-subtitle">System administrators</span>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card class="stat-card">
          <template #content>
            <div class="stat-content">
              <div class="stat-info">
                <div class="stat-icon bg-orange-100">
                  <i class="pi pi-calendar text-orange-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">This Month</span>
                  <span class="stat-value">{{ dashboardData?.users?.created_this_month || 0 }}</span>
                  <span class="stat-subtitle">New registrations</span>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid mb-4">
      <div class="col-12">
        <Card>
          <template #title>
            <span class="text-xl font-semibold">Quick Actions</span>
          </template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-6 lg:col-4">
                <Button
                  label="View Users"
                  icon="pi pi-users"
                  class="w-full"
                  severity="secondary"
                  @click="navigateTo('/admin/users')"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-4">
                <Button
                  label="Settings"
                  icon="pi pi-cog"
                  class="w-full"
                  severity="secondary"
                  disabled
                />
              </div>
              <div class="col-12 md:col-6 lg:col-4">
                <Button
                  label="Refresh Data"
                  icon="pi pi-refresh"
                  class="w-full"
                  severity="secondary"
                  @click="refreshDashboard"
                  :loading="loading"
                />
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { apiClient } from '@/api'

const router = useRouter()
const toast = useToast()

const loading = ref(false)
const dashboardData = ref<any>(null)

onMounted(async () => {
  await loadDashboard()
})

const loadDashboard = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/api/v1/admin/dashboard')
    dashboardData.value = response.data.data
  } catch (error: any) {
    console.error('Failed to load dashboard:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load dashboard data',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const refreshDashboard = async () => {
  await loadDashboard()
  toast.add({
    severity: 'success',
    summary: 'Refreshed',
    detail: 'Dashboard data refreshed',
    life: 2000
  })
}

const navigateTo = (path: string) => {
  router.push(path)
}
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
