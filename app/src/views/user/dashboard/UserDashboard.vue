<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-home"></i>
              Welcome Back, {{ authStore.user?.name }}!
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
            Your personal dashboard and activity center
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.notifications }}</span>
            <span class="stat-label">Notifications</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.tasks }}</span>
            <span class="stat-label">Tasks</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ stats.messages }}</span>
            <span class="stat-label">Messages</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="My Investments"
          icon="pi pi-wallet"
          severity="primary"
          @click="router.push({ name: 'MyInvestments' })"
          class="add-user-btn"
        />
      </div>
    </div>

    <!-- Welcome Message -->
    <div class="grid mb-4">
      <div class="col-12">
        <Card>
          <template #content>
            <div class="flex align-items-center gap-3">
              <div class="stat-icon bg-blue-100" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                <i class="pi pi-info-circle text-blue-600 text-3xl"></i>
              </div>
              <div>
                <h3 class="mb-2 mt-0">Welcome to BasicAdmin!</h3>
                <p class="m-0 text-muted">
                  This is your personal dashboard. Here you can manage your profile, view notifications, and access your account settings.
                </p>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid stats-section px-2">
      <div class="col-12 md:col-6 lg:col-3">
        <Card class="stat-card">
          <template #content>
            <div class="stat-content">
              <div class="stat-info">
                <div class="stat-icon bg-blue-100">
                  <i class="pi pi-bell text-blue-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Notifications</span>
                  <span class="stat-value">{{ stats.notifications }}</span>
                  <span class="stat-subtitle">{{ stats.unreadNotifications }} unread</span>
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
                  <i class="pi pi-check-square text-green-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Tasks</span>
                  <span class="stat-value">{{ stats.tasks }}</span>
                  <span class="stat-subtitle">{{ stats.completedTasks }} completed</span>
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
                  <i class="pi pi-envelope text-purple-600 text-3xl"></i>
                </div>
                <div class="stat-details">
                  <span class="stat-label">Messages</span>
                  <span class="stat-value">{{ stats.messages }}</span>
                  <span class="stat-subtitle">{{ stats.unreadMessages }} unread</span>
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
                  <span class="stat-label">Account Age</span>
                  <span class="stat-value">{{ stats.accountAge }}</span>
                  <span class="stat-subtitle">days active</span>
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
                  label="My Investments"
                  icon="pi pi-wallet"
                  class="w-full"
                  severity="secondary"
                  @click="router.push({ name: 'MyInvestments' })"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-3">
                <Button
                  label="My Solar Plants"
                  icon="pi pi-sun"
                  class="w-full"
                  severity="secondary"
                  @click="router.push({ name: 'MyPlants' })"
                />
              </div>
              <div class="col-12 md:col-6 lg:col-3">
                <Button
                  label="Notifications"
                  icon="pi pi-bell"
                  class="w-full"
                  severity="secondary"
                  @click="router.push({ name: 'Notifications' })"
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

    <!-- Account Information and Quick Links -->
    <div class="grid">
      <div class="col-12 lg:col-6">
        <Card>
          <template #title>
            <span class="text-xl font-semibold">Account Information</span>
          </template>
          <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex align-items-center justify-content-between">
                <span class="font-semibold">Name:</span>
                <span>{{ authStore.user?.name }}</span>
              </div>
              <div class="flex align-items-center justify-content-between">
                <span class="font-semibold">Email:</span>
                <span>{{ authStore.user?.email }}</span>
              </div>
              <div class="flex align-items-center justify-content-between">
                <span class="font-semibold">Role:</span>
                <span class="capitalize">{{ authStore.user?.role }}</span>
              </div>
              <div class="flex align-items-center justify-content-between">
                <span class="font-semibold">Email Verified:</span>
                <span>
                  <i :class="authStore.user?.email_verified_at ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-red-500'"></i>
                  {{ authStore.user?.email_verified_at ? 'Yes' : 'No' }}
                </span>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 lg:col-6">
        <Card>
          <template #title>
            <span class="text-xl font-semibold">Quick Links</span>
          </template>
          <template #content>
            <div class="flex flex-column gap-2">
              <Button
                label="My Investments"
                icon="pi pi-wallet"
                severity="secondary"
                class="w-full"
                text
                @click="router.push({ name: 'MyInvestments' })"
              />
              <Button
                label="My Solar Plants"
                icon="pi pi-sun"
                severity="secondary"
                class="w-full"
                text
                @click="router.push({ name: 'MyPlants' })"
              />
              <Button
                label="Create Investment"
                icon="pi pi-plus"
                severity="secondary"
                class="w-full"
                text
                @click="router.push({ name: 'CreateInvestment' })"
              />
              <Button
                label="Notifications"
                icon="pi pi-bell"
                severity="secondary"
                class="w-full"
                text
                @click="router.push({ name: 'Notifications' })"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

const loading = ref(false)

// Calculate account age in days
const accountAge = computed(() => {
  if (!authStore.user?.created_at) return 0
  const created = new Date(authStore.user.created_at)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - created.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
})

// Mock stats data - replace with API calls when backend endpoints are ready
const stats = ref({
  notifications: 3,
  unreadNotifications: 2,
  tasks: 5,
  completedTasks: 3,
  messages: 7,
  unreadMessages: 4,
  accountAge: accountAge.value
})

onMounted(async () => {
  await loadDashboard()
})

const loadDashboard = async () => {
  loading.value = true
  try {
    // Update account age
    stats.value.accountAge = accountAge.value

    // TODO: Replace with actual API call when user endpoints are created
    // const response = await apiClient.get('/api/v1/user/dashboard')
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
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';

.capitalize {
  text-transform: capitalize;
}
</style>
