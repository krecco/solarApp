<template>
  <div class="page-container">
    <!-- Modern Page Header with Refresh Inline -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <h1 class="header-title">
              <i class="pi pi-users"></i>
              {{ $t('admin.users.title') }}
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="loadUsers"
              v-tooltip.top="$t('common.refresh')"
              :loading="loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle">
            {{ $t('admin.users.subtitle') }}
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">{{ userStats.total }}</span>
            <span class="stat-label">{{ $t('admin.users.total') }}</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ userStats.verified }}</span>
            <span class="stat-label">{{ $t('admin.users.verified') }}</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">{{ userStats.newToday }}</span>
            <span class="stat-label">{{ $t('admin.users.newToday') }}</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          :label="$t('admin.users.addUser')"
          icon="pi pi-plus"
          severity="primary"
          @click="showCreateModal = true"
          class="add-user-btn"
        />
        <Button
          icon="pi pi-download"
          severity="secondary"
          outlined
          @click="exportUsers"
          v-tooltip.top="'Export Users'"
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
                :placeholder="$t('admin.users.searchPlaceholder')"
                class="w-full"
                @input="debounceSearch"
              />
            </IconField>
          </div>
          
          <div class="filter-item">
            <MultiSelect
              v-model="filters.roles"
              :options="roleOptions"
              optionLabel="label"
              optionValue="value"
              :placeholder="$t('admin.users.filterByRole')"
              :maxSelectedLabels="1"
              class="w-full"
              display="chip"
              @change="loadUsers"
            >
              <template #value="slotProps">
                <span v-if="!slotProps.value || slotProps.value.length === 0" class="filter-placeholder">
                  <i class="pi pi-users"></i> {{ $t('admin.users.role') }}
                </span>
                <span v-else class="filter-value">
                  <i class="pi pi-users"></i> {{ slotProps.value.length }} {{ $t('common.selected') }}
                </span>
              </template>
            </MultiSelect>
          </div>
        </div>

        <!-- Active Filter Pills -->
        <div v-if="hasActiveFilters" class="active-filter-pills">
          <Chip
            v-for="role in filters.roles"
            :key="`role-${role}`"
            removable
            @remove="removeRoleFilter(role)"
            class="filter-pill role-pill"
          >
            <span class="pill-content">
              <i class="pi pi-users pill-icon"></i>
              {{ roleOptions.find(r => r.value === role)?.label || role }}
            </span>
          </Chip>

          <Chip
            removable
            @remove="clearFilters"
            class="clear-filter-chip"
          >
            <span class="pill-content">
              <i class="pi pi-filter-slash pill-icon"></i>
              {{ $t('admin.users.clearAllFilters') }}
            </span>
          </Chip>
        </div>
      </template>
    </Card>

    <!-- Data Table using modern wrapper from utilities -->
    <Card class="modern-table-wrapper">
      <template #content>
        <DataTable
          :value="adminStore.users"
          :loading="adminStore.loading"
          :paginator="true"
          :rows="pagination.perPage"
          :totalRecords="pagination.total"
          :lazy="true"
          @page="onPage"
          @sort="onSort"
          :rowsPerPageOptions="[10, 15, 25, 50]"
          paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :currentPageReportTemplate="$t('common.pagination.showing')"
          responsiveLayout="scroll"
          :rowHover="true"
        >
          <template #loading>
            <div class="table-loading-spinner">
              <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
              <p>Loading users...</p>
            </div>
          </template>

          <template #empty>
            <EnhancedEmptyState
              v-if="!adminStore.loading"
              icon="pi pi-users"
              :title="hasActiveFilters ? $t('admin.users.noUsersFound') : $t('admin.users.noUsers')"
              :description="hasActiveFilters ? $t('admin.users.tryAdjustingFilters') : $t('admin.users.getStartedByAdding')"
              :actionLabel="hasActiveFilters ? undefined : $t('admin.users.addUser')"
              actionIcon="pi pi-plus"
              @action="showCreateModal = true"
              :helpText="hasActiveFilters ? undefined : $t('admin.users.userManagementTip')"
              compact
            />
          </template>

          <!-- Name Column -->
          <Column field="name" :header="$t('admin.users.name')" :sortable="true" style="min-width: 14rem">
            <template #body="slotProps">
              <div class="table-cell">
                <Avatar
                  v-if="slotProps.data.avatar_url"
                  :image="slotProps.data.avatar_url"
                  shape="circle"
                  size="normal"
                />
                <div
                  v-else
                  class="avatar-cell"
                  :style="{ backgroundColor: getAvatarColor(slotProps.data.name) }"
                >
                  {{ getInitials(slotProps.data.name) }}
                </div>
                <div class="info-cell">
                  <span class="primary-text">{{ slotProps.data.name }}</span>
                  <span class="secondary-text">{{ slotProps.data.email }}</span>
                </div>
              </div>
            </template>
          </Column>

          <!-- Role Column -->
          <Column field="role" :header="$t('admin.users.role')" :sortable="false" style="min-width: 10rem">
            <template #body="slotProps">
              <div class="role-badge-outlined" :class="`role-${slotProps.data.role?.toLowerCase() || 'user'}`">
                <i :class="getRoleIcon(slotProps.data.role || 'user')"></i>
                <span>{{ slotProps.data.role || 'user' }}</span>
              </div>
            </template>
          </Column>

          <!-- Status Column -->
          <Column
            field="email_verified_at"
            header="Status"
            :sortable="true"
            style="min-width: 8rem"
          >
            <template #body="slotProps">
              <Badge
                v-if="slotProps.data.email_verified_at"
                severity="success"
                class="status-badge"
                v-tooltip.top="getVerifiedTooltip(slotProps.data.email_verified_at)"
              >
                <i class="pi pi-check-circle mr-1"></i>
                Verified
              </Badge>
              <Badge
                v-else
                severity="warning"
                class="status-badge"
              >
                <i class="pi pi-exclamation-circle mr-1"></i>
                Unverified
              </Badge>
            </template>
          </Column>

          <!-- Last Login Column - Hidden on mobile -->
          <Column
            field="last_login_at"
            header="Last Login"
            :sortable="true"
            headerClass="table-hide-mobile"
            bodyClass="table-hide-mobile"
            style="min-width: 12rem"
          >
            <template #body="slotProps">
              <div class="date-cell">
                <i class="pi pi-calendar date-icon"></i>
                <span v-if="slotProps.data.last_login_at">
                  {{ formatDate(slotProps.data.last_login_at) }}
                </span>
                <span v-else class="text-muted">Never</span>
              </div>
            </template>
          </Column>
          
          <!-- Actions Column -->
          <Column :header="$t('common.actions')" style="width: 8rem">
            <template #body="slotProps">
              <div class="action-buttons-cell">
                <Button 
                  icon="pi pi-eye" 
                  severity="info"
                  text
                  rounded
                  size="small"
                  @click="viewUser(slotProps.data)"
                  v-tooltip.top="$t('common.view')"
                  class="action-btn view-btn"
                />
                <Button 
                  icon="pi pi-pencil" 
                  severity="warning"
                  text
                  rounded
                  size="small"
                  @click="editUser(slotProps.data)"
                  v-tooltip.top="$t('common.edit')"
                  class="action-btn edit-btn"
                />
                <Button 
                  icon="pi pi-trash" 
                  severity="danger"
                  text
                  rounded
                  size="small"
                  @click="confirmDelete(slotProps.data)"
                  v-tooltip.top="$t('common.delete')"
                  class="action-btn delete-btn"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Confirm Delete Dialog -->
    <Dialog 
      v-model:visible="deleteDialog.visible" 
      :header="$t('common.confirm')" 
      :modal="true"
      :style="{ width: '450px' }"
    >
      <div class="flex align-items-center gap-3">
        <i class="pi pi-exclamation-triangle text-5xl text-warning"></i>
        <span>{{ $t('admin.users.confirmDelete', { name: deleteDialog.user?.name }) }}</span>
      </div>
      
      <template #footer>
        <Button 
          :label="$t('common.cancel')" 
          severity="secondary" 
          text
          @click="deleteDialog.visible = false"
        />
        <Button 
          :label="$t('common.delete')" 
          severity="danger" 
          @click="deleteUser"
        />
      </template>
    </Dialog>

    <!-- Create User Modal -->
    <AppModal
      v-model="showCreateModal"
      :header="$t('admin.users.createUser')"
      :subtitle="$t('admin.users.addNewUser')"
      icon="pi pi-user-plus"
      width="50rem"
      height="90vh"
      :fixed-footer="true"
    >
      <UserCreateForm 
        v-if="showCreateModal"
        ref="createForm"
        @user-created="onUserCreated"
        @cancel="showCreateModal = false"
      />
      
      <template #footer>
        <div class="flex gap-2 w-full">
          <Button
            :label="$t('common.cancel')"
            severity="secondary"
            outlined
            @click="showCreateModal = false"
            class="flex-1"
            size="small"
          />
          <Button
            :label="createForm?.buttonLabel || $t('common.save')"
            :severity="createForm?.buttonSeverity || 'primary'"
            :loading="createForm?.loading"
            :icon="createForm?.buttonIcon"
            @click="createForm?.handleSubmit()"
            class="flex-1"
            size="small"
          />
        </div>
      </template>
    </AppModal>

    <!-- Edit User Modal -->
    <AppModal
      v-model="showEditModal"
      :header="$t('admin.users.editUser')"
      :subtitle="selectedUser ? $t('admin.users.editingUser', { name: selectedUser.name }) : ''"
      icon="pi pi-user-edit"
      width="50rem"
      height="90vh"
      :fixed-footer="true"
    >
      <UserEditForm 
        v-if="selectedUser"
        ref="editForm"
        :user="selectedUser"
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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import { useAdminStore } from '@/stores/admin'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dropdown from 'primevue/dropdown'
import Badge from 'primevue/badge';
import MultiSelect from 'primevue/multiselect'
import Tag from 'primevue/tag'
import Chip from 'primevue/chip'
import Avatar from 'primevue/avatar'
import Dialog from 'primevue/dialog'
import AppModal from '@/components/common/AppModal.vue'

import { debounce } from 'lodash-es'

// Import form components
import UserCreateForm from './components/UserCreateForm.vue'
import UserEditForm from './components/UserEditForm.vue'

// Import UX enhancement components
import TableSkeleton from '@/components/common/TableSkeleton.vue'
import EnhancedEmptyState from '@/components/common/EnhancedEmptyState.vue'

const router = useRouter()
const { t } = useI18n()
const toast = useToast()
const adminStore = useAdminStore()

// State
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedUser = ref<any>(null)
const createForm = ref<any>(null)
const editForm = ref<any>(null)
const filters = reactive({
  search: '',
  roles: [] as string[]
})

const pagination = reactive({
  page: 1,
  perPage: 15,
  total: 0
})

const sorting = reactive({
  sortBy: 'last_login_at',
  sortDirection: 'desc' as 'asc' | 'desc'
})

const deleteDialog = reactive({
  visible: false,
  user: null as any
})

// Options
const roleOptions = ref([
  { label: 'Admin', value: 'admin' },
  { label: 'Manager', value: 'manager' },
  { label: 'User', value: 'user' }
])

// Computed properties
const hasActiveFilters = computed(() => {
  return filters.search || filters.roles.length > 0
})

// Compute user statistics from the loaded user list
const userStats = computed(() => {
  const total = pagination.total
  const verified = adminStore.users.filter(u => u.emailVerified).length

  // Count users created today
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const newToday = adminStore.users.filter(u => {
    const createdDate = new Date(u.created_at)
    createdDate.setHours(0, 0, 0, 0)
    return createdDate.getTime() === today.getTime()
  }).length

  return { total, verified, newToday }
})

// Methods
const loadUsers = async () => {
  const filterData: any = {
    page: pagination.page,
    per_page: pagination.perPage,
    search: filters.search || undefined,
    roles: filters.roles.length > 0 ? filters.roles : undefined,
    sort_by: sorting.sortBy || undefined,
    sort_direction: sorting.sortDirection || undefined
  }

  const response = await adminStore.fetchUsers(filterData)

  if (response?.meta) {
    pagination.total = response.meta.total
    pagination.page = response.meta.current_page
    pagination.perPage = response.meta.per_page
  }
}

const debounceSearch = debounce(() => {
  pagination.page = 1
  loadUsers()
}, 300)

const onPage = (event: any) => {
  pagination.page = event.page + 1
  pagination.perPage = event.rows
  loadUsers()
}

const onSort = (event: any) => {
  // PrimeVue DataTable passes sortField and sortOrder (1 for asc, -1 for desc)
  sorting.sortBy = event.sortField
  sorting.sortDirection = event.sortOrder === 1 ? 'asc' : 'desc'
  pagination.page = 1 // Reset to first page when sorting
  loadUsers()
}

const clearFilters = () => {
  filters.search = ''
  filters.roles = []
  pagination.page = 1
  loadUsers()
}

const removeRoleFilter = (role: string) => {
  filters.roles = filters.roles.filter(r => r !== role)
  loadUsers()
}

const viewUser = (user: any) => {
  router.push(`/admin/users/${user.id}`)
}

const editUser = (user: any) => {
  // Just open the modal - DON'T TOUCH ANYTHING ELSE
  selectedUser.value = user
  showEditModal.value = true
}

const confirmDelete = (user: any) => {
  deleteDialog.user = user
  deleteDialog.visible = true
}

// Event handlers for sidebar forms
const onUserCreated = (user: any) => {
  showCreateModal.value = false
  toast.add({
    severity: 'success',
    summary: t('common.success'),
    detail: t('admin.users.userCreated', { name: user.name }),
    life: 3000
  })
  // DON'T call loadUsers() here either
}

const onUserUpdated = (user: any) => {
  showEditModal.value = false
  selectedUser.value = null
  toast.add({
    severity: 'success',
    summary: t('common.success'),
    detail: t('admin.users.userUpdated'),
    life: 3000
  })
  // DON'T call loadUsers() here - it causes the search issue
}

const deleteUser = async () => {
  try {
    await adminStore.deleteUser(deleteDialog.user.id)
    deleteDialog.visible = false
    deleteDialog.user = null
    
    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('admin.users.userDeleted'),
      life: 3000
    })
    
    loadUsers()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('admin.users.deleteError'),
      life: 3000
    })
  }
}

const exportUsers = async () => {
  try {
    // Create CSV content from current users
    const headers = ['ID', 'Name', 'Email', 'Role', 'Email Verified', 'Created At']
    const csvRows = [headers.join(',')]

    adminStore.users.forEach(user => {
      const row = [
        user.id,
        `"${user.name}"`,
        user.email,
        user.role,
        user.emailVerified ? 'Yes' : 'No',
        new Date(user.created_at).toLocaleDateString()
      ]
      csvRows.push(row.join(','))
    })

    const csvContent = csvRows.join('\n')
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)

    link.setAttribute('href', url)
    link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('admin.users.exportSuccess') || 'Users exported successfully',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('admin.users.exportError') || 'Failed to export users',
      life: 3000
    })
  }
}

// Utility functions
const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

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

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getVerifiedTooltip = (verifiedAt: string) => {
  const date = new Date(verifiedAt)
  return `Email verified at: ${date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })}`
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
