<template>
  <div class="user-create-form">
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

          <div class="form-group">
            <label for="password">{{ $t('admin.users.password') }} <span class="text-red-500">*</span></label>
            <Password
              id="password"
              v-model="form.password"
              :class="{ 'p-invalid': v$.password.$error }"
              class="w-full"
              :inputClass="'w-full'"
              toggleMask
              :feedback="false"
              @blur="v$.password.$touch"
            />
            <small v-if="v$.password.$error" class="text-red-500">
              {{ $t('validation.passwordMin', { min: 8 }) }}
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
            <Dropdown
              id="role"
              v-model="form.role"
              :options="roleOptions"
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
            <div class="field-checkbox">
              <Checkbox
                inputId="email_verified"
                v-model="form.email_verified"
                :binary="true"
              />
              <label for="email_verified" class="ml-2">{{ $t('admin.users.emailVerified') }}</label>
            </div>
            <small>
              {{ $t('admin.users.emailVerifiedHelp') }}
            </small>
          </div>

          <div class="form-group">
            <div class="field-checkbox">
              <Checkbox
                inputId="send_welcome_email"
                v-model="form.send_welcome_email"
                :binary="true"
              />
              <label for="send_welcome_email" class="ml-2">{{ $t('admin.users.sendWelcomeEmail') }}</label>
            </div>
            <small>
              {{ $t('admin.users.sendWelcomeEmailHelp') }}
            </small>
          </div>
        </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength } from '@vuelidate/validators'
import { useAdminStore } from '@/stores/admin'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import Password from 'primevue/password'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'

// Import UX enhancement composables
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { useButtonState } from '@/composables/useButtonState'

// Define emits
const emit = defineEmits<{
  'user-created': [user: any]
  'cancel': []
}>()

const { t } = useI18n()
const adminStore = useAdminStore()
const toast = useToast()
const { watchForm, markClean } = useUnsavedChanges()
const buttonState = useButtonState({
  successDuration: 1500 // Show success checkmark for 1.5s before closing modal
})

// Form state
const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'user',
  email_verified: false,
  send_welcome_email: true
})

// Validation rules
const rules = {
  name: { required },
  email: { required, email },
  password: { required, minLength: minLength(8) },
  role: { required }
}

const v$ = useVuelidate(rules, form)

// Options
const roleOptions = [
  { label: t('admin.users.roleAdmin'), value: 'admin' },
  { label: t('admin.users.roleManager'), value: 'manager' },
  { label: t('admin.users.roleUser'), value: 'user' }
]

// Watch form for unsaved changes
watchForm(form)

// Methods
const handleSubmit = async () => {
  const isValid = await v$.value.$validate()

  if (!isValid) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('validation.fixErrors'),
      life: 3000
    })
    return
  }

  try {
    await buttonState.execute(async () => {
      // Create user
      const userData = {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password,
        role: form.value.role,
        email_verified: form.value.email_verified
      }

      const newUser = await adminStore.createUser(userData)

      // Send welcome email if requested
      if (form.value.send_welcome_email) {
        try {
          await adminStore.sendWelcomeEmail(newUser.id)
          console.log('✅ Welcome email sent to:', newUser.email)
        } catch (emailError) {
          console.warn('⚠️ Failed to send welcome email:', emailError)
          // Don't fail the whole operation if email fails
        }
      }

      // Mark form as clean (no unsaved changes)
      markClean()

      // Emit success event after short delay to show success state
      setTimeout(() => {
        emit('user-created', newUser)
      }, 500)
    })
  } catch (error: any) {
    console.error('Failed to create user:', error)

    // Handle specific error cases
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      if (errors.email) {
        toast.add({
          severity: 'error',
          summary: t('common.error'),
          detail: t('admin.users.emailExists'),
          life: 5000
        })
      } else {
        toast.add({
          severity: 'error',
          summary: t('common.error'),
          detail: t('admin.users.createError'),
          life: 5000
        })
      }
    } else {
      toast.add({
        severity: 'error',
        summary: t('common.error'),
        detail: error.message || t('admin.users.createError'),
        life: 5000
      })
    }
  }
}

// Expose methods for parent component (modal usage)
defineExpose({
  handleSubmit,
  // For backward compatibility, expose isLoading as loading
  loading: buttonState.isLoading,
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
