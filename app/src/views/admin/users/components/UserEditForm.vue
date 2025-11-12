<template>
  <div class="user-edit-form">
    <!-- Content Area -->
    <div class="modal-form-content">
        
        <!-- Personal Information Section -->
        <div class="form-section">
          <h4>
            <i class="pi pi-user"></i>
            {{ $t('admin.users.personalInformation') }}
          </h4>
          
          <div class="form-group">
            <label for="name">{{ $t('admin.users.fullName') }} <span class="text-red-500">*</span></label>
            <InputText
              id="name"
              v-model="form.name"
              :class="{ 'p-invalid': v$.name.$error }"
              class="w-full"
              @blur="v$.name.$touch"
            />
            <small v-if="v$.name.$error" class="text-red-500">
              {{ $t('validation.required', { field: $t('admin.users.fullName') }) }}
            </small>
          </div>

          <div class="form-group">
            <label for="email">{{ $t('admin.users.email') }} <span class="text-red-500">*</span></label>
            <InputText
              id="email"
              v-model="form.email"
              type="email"
              :class="{ 'p-invalid': v$.email.$error }"
              class="w-full"
              @blur="v$.email.$touch"
            />
            <small v-if="v$.email.$error" class="text-red-500">
              {{ v$.email.required.$invalid
                ? $t('validation.required', { field: $t('admin.users.email') })
                : $t('validation.email')
              }}
            </small>
          </div>
        </div>

        <!-- Account Settings Section -->
        <div class="form-section">
          <h4>
            <i class="pi pi-cog"></i>
            {{ $t('admin.users.accountSettings') }}
          </h4>
          
          <div class="form-group">
            <label for="role">{{ $t('admin.users.role') }} <span class="text-red-500">*</span></label>
            <Select
              id="role"
              v-model="form.role"
              :options="roles"
              optionLabel="label"
              optionValue="value"
              class="w-full"
              :class="{ 'p-invalid': v$.role.$error }"
              @blur="v$.role.$touch"
            />
            <small v-if="v$.role.$error" class="text-red-500">
              {{ $t('validation.required', { field: $t('admin.users.role') }) }}
            </small>
          </div>

          <div class="form-group">
            <label for="status">{{ $t('admin.users.status') }} <span class="text-red-500">*</span></label>
            <Select
              id="status"
              v-model="form.status"
              :options="statuses"
              optionLabel="label"
              optionValue="value"
              class="w-full"
              :class="{ 'p-invalid': v$.status.$error }"
              @blur="v$.status.$touch"
            />
            <small v-if="v$.status.$error" class="text-red-500">
              {{ $t('validation.required', { field: $t('admin.users.status') }) }}
            </small>
          </div>

          <div class="form-group">
            <label for="newPassword">{{ $t('admin.users.newPassword') }}</label>
            <Password
              id="newPassword"
              v-model="form.newPassword"
              :toggleMask="true"
              class="w-full"
              :inputClass="'w-full'"
              :feedback="false"
              :placeholder="$t('admin.users.enterNewPassword')"
            />
            <small class="block mt-1 text-color-secondary text-xs">
              {{ $t('admin.users.leaveBlankToKeepCurrent') }}
            </small>
          </div>
          
          <!-- Security Settings -->
          <Divider class="my-4" />
          <h5 class="text-base font-semibold mb-3 text-color">{{ $t('admin.users.securitySettings') }}</h5>
          
          <div class="field-checkbox mb-3">
            <Checkbox
              inputId="emailVerified"
              v-model="form.emailVerified"
              :binary="true"
            />
            <label for="emailVerified" class="ml-2">{{ $t('admin.users.emailVerified') }}</label>
          </div>
        </div>

        <!-- User Info Section -->
        <div class="form-section">
          <h4>
            <i class="pi pi-info-circle"></i>
            {{ $t('admin.users.userInfo') }}
          </h4>
          
          <div class="info-list">
            <div class="info-row">
              <span class="info-label">{{ $t('admin.users.userId') }}</span>
              <span class="info-value font-mono">{{ user.id }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">{{ $t('admin.users.createdAt') }}</span>
              <span class="info-value">{{ formatDate(user.createdAt) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">{{ $t('admin.users.lastLogin') }}</span>
              <span class="info-value">{{ user.lastLoginAt ? formatDate(user.lastLoginAt) : '-' }}</span>
            </div>
          </div>
        </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useVuelidate } from '@vuelidate/core'
import { required, email } from '@vuelidate/validators'
import { useAdminStore } from '@/stores/admin'
import { useToast } from 'primevue/usetoast'

import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
import Divider from 'primevue/divider'

// Import UX enhancement composables
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { useButtonState } from '@/composables/useButtonState'

interface UserForm {
  name: string
  email: string
  role: string
  status: string
  newPassword: string
  emailVerified: boolean
}

interface User extends UserForm {
  id: number
  createdAt: string
  lastLoginAt?: string
}

// Props and emits
const props = defineProps<{
  user: User
}>()

const emit = defineEmits<{
  'user-updated': [user: any]
  'cancel': []
}>()

const { t } = useI18n()
const adminStore = useAdminStore()
const toast = useToast()
const { watchForm, markClean } = useUnsavedChanges()
const buttonState = useButtonState({
  successDuration: 1500 // Show success checkmark for 1.5s before closing modal
})

const form = reactive<UserForm>({
  name: '',
  email: '',
  role: 'user',
  status: 'active',
  newPassword: '',
  emailVerified: true
})

const rules = {
  name: { required },
  email: { required, email },
  role: { required },
  status: { required }
}

const v$ = useVuelidate(rules, form)

const roles = [
  { label: t('admin.users.roleAdmin'), value: 'admin' },
  { label: t('admin.users.roleManager'), value: 'manager' },
  { label: t('admin.users.roleUser'), value: 'user' }
]

const statuses = [
  { label: t('admin.users.statusActive'), value: 'active' },
  { label: t('admin.users.statusSuspended'), value: 'suspended' },
  { label: t('admin.users.statusPending'), value: 'pending' }
]

onMounted(() => {
  // Initialize form with user data
  populateForm()

  // Watch form for unsaved changes after initial population
  watchForm(form)
})

const populateForm = () => {
  Object.assign(form, {
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
    newPassword: '',
    emailVerified: props.user.emailVerified
  })
}

const handleSubmit = async () => {
  const isFormValid = await v$.value.$validate()
  if (!isFormValid) return

  try {
    await buttonState.execute(async () => {
      // Prepare update data in API format
      const updateData: any = {
        name: form.name,
        email: form.email,
        role: form.role,
      }

      // Only include password if it was changed
      if (form.newPassword) {
        updateData.password = form.newPassword
      }

      // Handle email verification status
      if (form.emailVerified && !props.user?.emailVerified) {
        updateData.email_verified_at = new Date().toISOString()
      } else if (!form.emailVerified && props.user?.emailVerified) {
        updateData.email_verified_at = null
      }

      // Call API to update user
      const updatedUser = await adminStore.updateUser(props.user.id, updateData)

      // Mark form as clean (no unsaved changes)
      markClean()

      // Emit success event after short delay to show success state
      setTimeout(() => {
        emit('user-updated', updatedUser)
      }, 500)
    })
  } catch (err: any) {
    // Handle specific error cases
    if (err?.response?.status === 422) {
      const errors = err.response.data.errors
      if (errors?.email) {
        toast.add({
          severity: 'error',
          summary: t('common.error'),
          detail: t('admin.users.emailAlreadyExists'),
          life: 5000
        })
      } else {
        toast.add({
          severity: 'error',
          summary: t('common.error'),
          detail: err.response.data.message || t('admin.users.updateFailed'),
          life: 5000
        })
      }
    } else if (err?.response?.status === 400 && err?.response?.data?.error?.includes('role')) {
      toast.add({
        severity: 'error',
        summary: t('common.error'),
        detail: err.response.data.error,
        life: 7000
      })
    } else {
      toast.add({
        severity: 'error',
        summary: t('common.error'),
        detail: t('admin.users.updateFailed'),
        life: 5000
      })
    }
    console.error('Error updating user:', err)
  }
}

const resetForm = () => {
  populateForm()
  v$.value.$reset()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Expose methods for parent component
defineExpose({
  handleSubmit,
  resetForm,
  // For backward compatibility, expose isLoading as submitting
  submitting: buttonState.isLoading,
  // Expose button state for enhanced UI
  buttonState: buttonState.state,
  buttonLabel: buttonState.label,
  buttonIcon: buttonState.icon,
  buttonSeverity: buttonState.severity
})
</script>

<style scoped lang="scss">
/* Use PrimeFlex utilities and existing PrimeVue styling only */
.field-checkbox {
  display: flex;
  align-items: center;
  
  label {
    cursor: pointer;
    margin-bottom: 0;
  }
}
</style>
