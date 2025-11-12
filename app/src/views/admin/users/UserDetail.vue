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
              @click="navigateBack"
              v-tooltip.top="$t('common.back') || 'Back'"
              class="back-btn"
            />
            <h1 class="header-title">
              <i class="pi pi-user"></i>
              {{ $t('admin.users.userDetail') || 'User Details' }}
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="loadUser"
              v-tooltip.top="$t('common.refresh') || 'Refresh'"
              :loading="loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle" v-if="user">
            {{ user.name }} ({{ user.email }})
          </p>
        </div>
        <div class="header-stats" v-if="user">
          <div class="stat-card-modern">
            <span class="stat-value">
              <i :class="getRoleIcon(user.role || 'user')" class="mr-2"></i>
              {{ capitalizeFirst(user.role || 'user') }}
            </span>
            <span class="stat-label">{{ $t('admin.users.role') || 'Role' }}</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              <i :class="getStatusIcon(user.status || 'active')" class="mr-2"></i>
              {{ capitalizeFirst(user.status || 'active') }}
            </span>
            <span class="stat-label">{{ $t('admin.users.status') || 'Status' }}</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              <i :class="user.emailVerified ? 'pi pi-check-circle' : 'pi pi-times-circle'" class="mr-2"></i>
              {{ user.emailVerified ? 'Verified' : 'Unverified' }}
            </span>
            <span class="stat-label">{{ $t('admin.users.emailStatus') || 'Email Status' }}</span>
          </div>
        </div>
      </div>
      <div class="header-actions" v-if="user">
        <Button
          :label="$t('admin.users.edit') || 'Edit User'"
          icon="pi pi-pencil"
          severity="primary"
          @click="showEditModal = true"
          :disabled="loading"
          class="add-user-btn"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex align-items-center justify-content-center" style="min-height: 400px;">
      <div class="text-center">
        <ProgressSpinner style="width: 50px; height: 50px;" strokeWidth="4" />
        <p class="mt-3 text-color-secondary">{{ $t('common.loading') || 'Loading...' }}</p>
      </div>
    </div>

    <!-- User Details Content -->
    <div v-else-if="user" class="grid">
      <!-- Main Content Area -->
      <div class="col-12">
        <!-- Main Content Grid -->
        <div class="grid">
          <!-- Left Column - Details -->
          <div class="col-12 xl:col-8">
            <div class="grid">
              <!-- Account Information -->
              <div class="col-12 md:col-6">
                <Card class="h-full">
                  <template #title>
                    <span class="flex align-items-center gap-2">
                      <i class="pi pi-info-circle text-primary"></i>
                      {{ $t('admin.users.accountInformation') || 'Account Information' }}
                    </span>
                  </template>
                  <template #content>
                    <div class="flex flex-column gap-3">
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.userId') || 'User ID' }}</span>
                        <Chip :label="'#' + user.id" class="font-mono bg-primary-100 text-primary-800" />
                      </div>
                      <Divider class="my-2" />
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.role') || 'Role' }}</span>
                        <Tag :value="user.role" :severity="getRoleSeverity(user.role)" />
                      </div>
                      <Divider class="my-2" />
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.status') || 'Status' }}</span>
                        <Tag
                          :value="user.status"
                          :severity="getStatusSeverity(user.status)"
                        />
                      </div>
                    </div>
                  </template>
                </Card>
              </div>

              <!-- Security -->
              <div class="col-12 md:col-6">
                <Card class="h-full">
                  <template #title>
                    <span class="flex align-items-center gap-2">
                      <i class="pi pi-shield text-primary"></i>
                      {{ $t('admin.users.security') || 'Security' }}
                    </span>
                  </template>
                  <template #content>
                    <div class="flex flex-column gap-3">
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.emailVerified') || 'Email' }}</span>
                        <Tag
                          :value="user.emailVerified ? ($t('common.verified') || 'Verified') : ($t('common.notVerified') || 'Not Verified')"
                          :severity="user.emailVerified ? 'success' : 'warning'"
                          :icon="user.emailVerified ? 'pi pi-check-circle' : 'pi pi-times-circle'"
                        />
                      </div>
                      <Divider class="my-2" />
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.createdAt') || 'Created' }}</span>
                        <span class="font-semibold text-sm">{{ formatDate(user.createdAt) }}</span>
                      </div>
                      <Divider class="my-2" />
                      <div class="flex justify-content-between align-items-center">
                        <span class="text-color-secondary">{{ $t('admin.users.lastLogin') || 'Last Login' }}</span>
                        <span class="font-semibold text-sm">{{ user.lastLoginAt ? formatDate(user.lastLoginAt) : '-' }}</span>
                      </div>
                    </div>
                  </template>
                </Card>
              </div>
            </div>
          </div>

          <!-- Right Column - Actions & Stats -->
          <div class="col-12 xl:col-4">
            <!-- Quick Actions -->
            <Card class="mb-3">
              <template #title>
                <span class="flex align-items-center gap-2">
                  <i class="pi pi-bolt text-primary"></i>
                  {{ $t('admin.common.quickActions') || 'Quick Actions' }}
                </span>
              </template>
              <template #content>
                <div class="flex flex-column gap-2">
                  <Button
                    :label="$t('admin.users.changeAvatar') || 'Change Avatar'"
                    icon="pi pi-image"
                    severity="secondary"
                    class="w-full"
                    outlined
                    @click="showAvatarModal = true"
                  />
                  <Button
                    :label="$t('admin.users.sendWelcomeEmail') || 'Send Welcome Email'"
                    icon="pi pi-envelope"
                    severity="secondary"
                    class="w-full"
                    outlined
                    @click="sendWelcomeEmailToUser"
                  />
                </div>
              </template>
            </Card>

            <!-- User Stats -->
            <Card>
              <template #title>
                <span class="flex align-items-center gap-2">
                  <i class="pi pi-chart-line text-primary"></i>
                  {{ $t('admin.users.statistics') || 'Statistics' }}
                </span>
              </template>
              <template #content>
                <div class="grid">
                  <div class="col-12">
                    <div class="text-center p-3 border-round surface-50">
                      <i class="pi pi-calendar text-3xl text-primary mb-2" />
                      <div class="text-lg font-bold text-primary">{{ calculateAccountAge(user.createdAt) }}</div>
                      <div class="text-sm text-color-secondary">{{ $t('admin.users.accountAge') || 'Account Age' }}</div>
                    </div>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex align-items-center justify-content-center" style="min-height: 400px;">
      <div class="text-center">
        <i class="pi pi-exclamation-triangle text-6xl text-yellow-500 mb-3" />
        <h3 class="mb-2">{{ $t('common.error') || 'Error' }}</h3>
        <p class="text-color-secondary mb-4">{{ error }}</p>
        <Button
          :label="$t('common.retry') || 'Retry'"
          icon="pi pi-refresh"
          @click="loadUser"
        />
      </div>
    </div>

    <!-- Edit User Modal -->
    <AppModal
      v-model="showEditModal"
      :header="$t('admin.users.editUser')"
      :subtitle="user ? $t('admin.users.editingUser', { name: user.name }) : ''"
      icon="pi pi-user-edit"
      width="50rem"
      height="90vh"
      :fixed-footer="true"
    >
      <UserEditForm
        v-if="user"
        ref="editForm"
        :user="user"
        @user-updated="onUserUpdated"
        @cancel="showEditModal = false"
      />

      <template #footer>
        <div class="flex gap-2 w-full">
          <Button
            :label="$t('common.cancel')"
            severity="secondary"
            outlined
            @click="showEditModal = false"
            class="flex-1"
            size="small"
          />
          <Button
            :label="$t('common.reset')"
            severity="secondary"
            outlined
            @click="editForm?.resetForm()"
            type="button"
            class="flex-1"
            size="small"
          />
          <Button
            :label="editForm?.buttonLabel || $t('common.save')"
            :severity="editForm?.buttonSeverity || 'primary'"
            :loading="editForm?.submitting"
            :icon="editForm?.buttonIcon"
            @click="editForm?.handleSubmit()"
            class="flex-1"
            size="small"
          />
        </div>
      </template>
    </AppModal>

    <!-- Change Avatar Modal -->
    <ChangeAvatarModal
      v-if="user"
      v-model="showAvatarModal"
      :current-avatar-url="user.avatar_url"
      :user-name="user.name"
      :user-id="user.id"
      @avatar-changed="onAvatarChanged"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import { useAdminStore } from '@/stores/admin'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import ProgressSpinner from 'primevue/progressspinner'
import Dialog from 'primevue/dialog'
import Chip from 'primevue/chip'
import AppModal from '@/components/common/AppModal.vue'
// Tooltip is added as a directive in main.ts

// Import form component
import UserEditForm from './components/UserEditForm.vue'
import ChangeAvatarModal from './components/ChangeAvatarModal.vue'

// Removed ActivityTimeline - no API endpoint available

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const toast = useToast()
const adminStore = useAdminStore()

// State
const user = ref<any>(null)
const loading = ref(true)
const error = ref('')
const showEditModal = ref(false)
const showAvatarModal = ref(false)
const editForm = ref<any>(null)

// Methods
const loadUser = async () => {
  loading.value = true
  error.value = ''

  try {
    const userId = parseInt(route.params.id as string)
    user.value = await adminStore.fetchUser(userId)
  } catch (err: any) {
    error.value = err.message || t('admin.users.loadError')
  } finally {
    loading.value = false
  }
}

const navigateBack = () => {
  router.push('/admin/users')
}

// Event handler for modal form
const onUserUpdated = (updatedUser: any) => {
  showEditModal.value = false
  // Update the current user data
  Object.assign(user.value, updatedUser)

  toast.add({
    severity: 'success',
    summary: t('common.success'),
    detail: t('admin.users.userUpdated'),
    life: 3000
  })
}

const sendWelcomeEmailToUser = async () => {
  try {
    await adminStore.sendWelcomeEmail(user.value.id)
    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('admin.users.welcomeEmailSent') || 'Welcome email sent successfully',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('admin.users.welcomeEmailError') || 'Failed to send welcome email',
      life: 3000
    })
  }
}

const onAvatarChanged = async (data: { avatarUrl: string }) => {
  // Update the user's avatar_url immediately for UI feedback
  if (user.value) {
    user.value.avatar_url = data.avatarUrl
  }
  showAvatarModal.value = false

  // Reload user data to ensure consistency
  await loadUser()
}

// Utility functions
const getAvatarColor = (name: string) => {
  const colors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
    '#FFEAA7', '#DDA5E9', '#FD79A8', '#A29BFE'
  ]
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}

const getStatusSeverity = (status: string) => {
  switch (status) {
    case 'active':
      return 'success'
    case 'suspended':
      return 'danger'
    case 'pending':
      return 'warning'
    default:
      return 'secondary'
  }
}

const getRoleSeverity = (role: string) => {
  switch (role) {
    case 'admin':
      return 'danger'
    case 'manager':
      return 'info'
    case 'user':
      return 'success'
    default:
      return 'secondary'
  }
}

const getRoleIcon = (role: string) => {
  switch (role?.toLowerCase()) {
    case 'admin':
      return 'pi pi-shield'
    case 'manager':
      return 'pi pi-briefcase'
    case 'user':
      return 'pi pi-user'
    default:
      return 'pi pi-user'
  }
}

const getStatusIcon = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'pi pi-check-circle'
    case 'suspended':
      return 'pi pi-ban'
    case 'pending':
      return 'pi pi-clock'
    default:
      return 'pi pi-circle'
  }
}

const capitalizeFirst = (str: string) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}

const getActivityIcon = (type: string) => {
  switch (type.toLowerCase()) {
    case 'login':
      return 'pi pi-sign-in'
    case 'profile update':
      return 'pi pi-pencil'
    case '2fa':
      return 'pi pi-shield'
    case 'password':
      return 'pi pi-key'
    default:
      return 'pi pi-info-circle'
  }
}

const getActivitySeverity = (type: string) => {
  switch (type.toLowerCase()) {
    case 'login':
      return 'info'
    case 'profile update':
      return 'success'
    case '2fa':
      return 'success'
    case 'password':
      return 'danger'
    default:
      return 'secondary'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatRelativeTime = (date: Date) => {
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(hours / 24)
  
  if (hours < 1) {
    return t('common.justNow') || 'Just now'
  } else if (hours < 24) {
    return t('common.hoursAgo', { count: hours }) || `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`
  } else if (days < 7) {
    return t('common.daysAgo', { count: days }) || `${days} ${days === 1 ? 'day' : 'days'} ago`
  } else {
    return date.toLocaleDateString()
  }
}

const calculateAccountAge = (createdAt: string) => {
  const created = new Date(createdAt)
  const now = new Date()
  const diff = now.getTime() - created.getTime()
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const months = Math.floor(days / 30)
  const years = Math.floor(days / 365)
  
  if (years > 0) {
    return t('common.yearsAgo', { count: years }) || `${years} ${years === 1 ? 'year' : 'years'} ago`
  } else if (months > 0) {
    return t('common.monthsAgo', { count: months }) || `${months} ${months === 1 ? 'month' : 'months'} ago`
  } else if (days > 0) {
    return t('common.daysAgo', { count: days }) || `${days} ${days === 1 ? 'day' : 'days'} ago`
  } else {
    return t('common.today') || 'Today'
  }
}

// Lifecycle
onMounted(() => {
  loadUser()
})
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';

/* Only essential overrides using PrimeVue variables */
</style>
