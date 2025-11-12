<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-briefcase"></i>
              Manager Dashboard
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
            Manage your team and monitor activities
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.activeUsers }}</span>
            <span class="stat-label">Active Users</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.pendingTasks }}</span>
            <span class="stat-label">Pending Tasks</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.completedToday }}</span>
            <span class="stat-label">Completed Today</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="View Reports"
          icon="pi pi-chart-line"
          severity="primary"
          @click="navigateTo('/manager/reports')"
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
                  <span class="stat-label">Team Members</span>
                  <span class="stat-value">{{ stats.teamMembers }}</span>
                  <span class="stat-subtitle">{{ stats.activeUsers }} active</span>
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
                  <i class="pi pi-check-circle text-green-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Completed</span>
                  <span class="stat-value">{{ stats.completedToday }}</span>
                  <span class="stat-subtitle">{{ stats.completedThisWeek }} this week</span>
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
                  <i class="pi pi-clock text-orange-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Pending</span>
                  <span class="stat-value">{{ stats.pendingTasks }}</span>
                  <span class="stat-subtitle">Awaiting action</span>
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
                  <i class="pi pi-chart-line text-purple-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Performance</span>
                  <span class="stat-value">{{ stats.performance }}%</span>
                  <span class="stat-subtitle">Team efficiency</span>
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
              <div class="col-12 md:col-6 lg:col-3">
                <Button
                  label="View Reports"
                  icon="pi pi-chart-line"
                  class="w-full"
                  severity="secondary"
                  @click="navigateTo('/manager/reports')"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-3">
                <Button
                  label="Team Overview"
                  icon="pi pi-users"
                  class="w-full"
                  severity="secondary"
                  @click="navigateTo('/manager/team')"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-3">
                <Button
                  label="Task Management"
                  icon="pi pi-list"
                  class="w-full"
                  severity="secondary"
                  @click="navigateTo('/manager/tasks')"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-3">
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

    <!-- Recent Activity -->
    <div class="grid">
      <div class="col-12">
        <Card>
          <template #title>
            <span class="text-xl font-semibold">Recent Activity</span>
          </template>
          <template #content>
            <div class="text-center text-muted py-5">
              <i class="pi pi-info-circle text-4xl mb-3"></i>
              <p>Activity tracking coming soon</p>
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
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

const loading = ref(false)

// Mock stats data - replace with API calls when backend endpoints are ready
const stats = ref({
  teamMembers: 12,
  activeUsers: 8,
  pendingTasks: 5,
  completedToday: 3,
  completedThisWeek: 18,
  performance: 87
})

onMounted(async () => {
  await loadDashboard()
})

const loadDashboard = async () => {
  loading.value = true
  try {
    // TODO: Replace with actual API call when manager endpoints are created
    // const response = await apiClient.get('/api/v1/manager/dashboard')
    // stats.value = response.data.data

    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500))
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
  // For now, show a toast that these features are coming soon
  if (path.includes('/manager/')) {
    toast.add({
      severity: 'info',
      summary: 'Coming Soon',
      detail: 'This feature is under development',
      life: 3000
    })
  } else {
    router.push(path)
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
