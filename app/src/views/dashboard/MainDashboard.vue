<template>
  <div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-600 mt-1">Welcome back{{ user?.name ? `, ${user.name}` : '' }}!</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon bg-blue-100 text-blue-600">
          <i class="pi pi-users text-2xl"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Total Users</p>
          <p class="stat-value">{{ stats.users?.total || 0 }}</p>
          <p class="stat-info">
            <span>{{ stats.users?.verified || 0 }} verified</span>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-green-100 text-green-600">
          <i class="pi pi-user-plus text-2xl"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">New Today</p>
          <p class="stat-value">{{ stats.users?.created_today || 0 }}</p>
          <p class="stat-info">
            <span>{{ stats.users?.created_this_week || 0 }} this week</span>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-purple-100 text-purple-600">
          <i class="pi pi-shield text-2xl"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">Admin Users</p>
          <p class="stat-value">{{ stats.roles?.admins || 0 }}</p>
          <p class="stat-info">
            <span>System administrators</span>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-orange-100 text-orange-600">
          <i class="pi pi-calendar text-2xl"></i>
        </div>
        <div class="stat-content">
          <p class="stat-label">This Month</p>
          <p class="stat-value">{{ stats.users?.created_this_month || 0 }}</p>
          <p class="stat-info">
            <span>New registrations</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
      <div class="actions-grid">
        <router-link
          :to="{ name: 'AdminUserList' }"
          class="action-card"
        >
          <i class="pi pi-users text-3xl text-blue-600"></i>
          <h3 class="text-lg font-semibold mt-3">Manage Users</h3>
          <p class="text-gray-600 text-sm mt-1">View and manage all users</p>
        </router-link>

        <router-link
          :to="{ name: 'AdminUserCreate' }"
          class="action-card"
        >
          <i class="pi pi-user-plus text-3xl text-green-600"></i>
          <h3 class="text-lg font-semibold mt-3">Create User</h3>
          <p class="text-gray-600 text-sm mt-1">Add a new user to the system</p>
        </router-link>

        <div class="action-card cursor-default opacity-75">
          <i class="pi pi-cog text-3xl text-gray-600"></i>
          <h3 class="text-lg font-semibold mt-3">Settings</h3>
          <p class="text-gray-600 text-sm mt-1">Configure system settings</p>
        </div>

        <div class="action-card cursor-default opacity-75">
          <i class="pi pi-chart-bar text-3xl text-gray-600"></i>
          <h3 class="text-lg font-semibold mt-3">Reports</h3>
          <p class="text-gray-600 text-sm mt-1">View analytics and reports</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import { apiClient } from '@/api'

const authStore = useAuthStore()
const toast = useToast()

const user = ref(authStore.user)
const stats = ref<any>({
  users: {
    total: 0,
    verified: 0,
    created_today: 0,
    created_this_week: 0,
    created_this_month: 0
  },
  roles: {
    admins: 0
  }
})

const loading = ref(true)

onMounted(async () => {
  await loadDashboardStats()
})

const loadDashboardStats = async () => {
  try {
    loading.value = true
    const response = await apiClient.get('/api/v1/admin/dashboard')
    stats.value = response.data.data
  } catch (error: any) {
    console.error('Failed to load dashboard stats:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load dashboard statistics',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.dashboard-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.stat-info {
  font-size: 0.875rem;
  color: #9ca3af;
}

.quick-actions {
  margin-top: 3rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.5rem;
}

.action-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.2s, box-shadow 0.2s;
  text-decoration: none;
  color: inherit;
  display: block;
}

.action-card:hover:not(.cursor-default) {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
