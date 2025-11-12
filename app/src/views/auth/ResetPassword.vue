<template>
  <div class="auth-page-bg">
    <!-- Background -->
    <div class="auth-bg-elements">
      <div class="gradient-sphere sphere-1"></div>
      <div class="gradient-sphere sphere-2"></div>
      <div class="animated-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
    </div>

    <!-- Container -->
    <div class="auth-container">
      <Card class="auth-glass-card">
        <template #header>
          <div class="auth-header-center">
            <div class="auth-icon-container">
              <i class="pi pi-lock"></i>
            </div>
            <h2 class="auth-title">{{ $t('auth.resetPassword') }}</h2>
            <p class="auth-subtitle">{{ $t('auth.createNewPassword') }}</p>
          </div>
        </template>

        <template #content>
          <form @submit.prevent="handleResetPassword" class="auth-form-enhanced">
            <!-- Password Field -->
            <div class="form-field">
              <label for="password" class="field-label">
                <i class="pi pi-lock"></i>
                {{ $t('auth.newPassword') }}
              </label>
              <Password
                v-model="formData.password"
                :inputClass="{ 'p-invalid': errors.password }"
                inputId="password"
                :placeholder="$t('auth.enterNewPassword')"
                toggleMask
                :feedback="true"
                :strongRegex="'^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%]).{8,}'"
                :mediumRegex="'^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9]))).{6,}'"
                :weakLabel="$t('auth.weak')"
                :mediumLabel="$t('auth.medium')"
                :strongLabel="$t('auth.strong')"
                @blur="validateField('password')"
                class="w-full"
              >
                <template #header>
                  <div class="password-strength-header">{{ $t('auth.passwordStrength') }}</div>
                </template>
                <template #footer>
                  <div class="password-requirements">
                    <p class="requirement-title">{{ $t('auth.passwordRequirements') }}:</p>
                    <ul class="requirement-list">
                      <li :class="{ 'met': hasMinLength }">
                        <i :class="hasMinLength ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                        {{ $t('auth.minCharacters', { count: 8 }) }}
                      </li>
                      <li :class="{ 'met': hasUppercase }">
                        <i :class="hasUppercase ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                        {{ $t('auth.uppercaseLetter') }}
                      </li>
                      <li :class="{ 'met': hasLowercase }">
                        <i :class="hasLowercase ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                        {{ $t('auth.lowercaseLetter') }}
                      </li>
                      <li :class="{ 'met': hasNumber }">
                        <i :class="hasNumber ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                        {{ $t('auth.number') }}
                      </li>
                      <li :class="{ 'met': hasSpecial }">
                        <i :class="hasSpecial ? 'pi pi-check-circle' : 'pi pi-circle'"></i>
                        {{ $t('auth.specialCharacter') }}
                      </li>
                    </ul>
                  </div>
                </template>
              </Password>
              <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-field">
              <label for="confirmPassword" class="field-label">
                <i class="pi pi-lock"></i>
                {{ $t('auth.confirmPassword') }}
              </label>
              <Password
                v-model="formData.confirmPassword"
                :inputClass="{ 'p-invalid': errors.confirmPassword }"
                inputId="confirmPassword"
                :placeholder="$t('auth.confirmNewPassword')"
                toggleMask
                :feedback="false"
                @blur="validateField('confirmPassword')"
                class="w-full"
              />
              <small v-if="errors.confirmPassword" class="p-error">{{ errors.confirmPassword }}</small>
              <small v-if="passwordsMatch && formData.confirmPassword" class="p-success">
                <i class="pi pi-check-circle"></i> {{ $t('auth.passwordsMatch') }}
              </small>
            </div>

            <!-- Submit Button -->
            <Button
              type="submit"
              :label="$t('auth.resetPassword')"
              icon="pi pi-check"
              :loading="loading"
              :disabled="!isFormValid"
              class="auth-button-gradient"
            />

            <!-- Back to Login -->
            <div class="auth-footer-links">
              <router-link to="/auth/login" class="back-link">
                <i class="pi pi-arrow-left"></i>
                {{ $t('auth.backToLogin') }}
              </router-link>
            </div>
          </form>
        </template>
      </Card>

      <!-- Success Animation -->
      <transition name="fade">
        <div v-if="resetSuccess" class="auth-overlay">
          <div class="overlay-content animate-scale-in">
            <div class="auth-icon-container-success">
              <i class="pi pi-check-circle"></i>
            </div>
            <h3>{{ $t('auth.passwordResetSuccess') }}</h3>
            <p>{{ $t('auth.redirectingToLogin') }}</p>
            <ProgressBar mode="indeterminate" class="redirect-progress" />
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '@/stores/auth'
import Card from 'primevue/card'
import Password from 'primevue/password'
import Button from 'primevue/button'
import ProgressBar from 'primevue/progressbar'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const toast = useToast()
const authStore = useAuthStore()

// Form data
const formData = ref({
  password: '',
  confirmPassword: ''
})

// Form validation errors
const errors = ref({
  password: '',
  confirmPassword: ''
})

// Loading state
const loading = ref(false)
const resetSuccess = ref(false)

// Get token from URL query params
const resetToken = computed(() => route.query.token as string)

// Password validation computed properties
const hasMinLength = computed(() => formData.value.password.length >= 8)
const hasUppercase = computed(() => /[A-Z]/.test(formData.value.password))
const hasLowercase = computed(() => /[a-z]/.test(formData.value.password))
const hasNumber = computed(() => /[0-9]/.test(formData.value.password))
const hasSpecial = computed(() => /[!@#$%^&*(),.?":{}|<>]/.test(formData.value.password))

const passwordsMatch = computed(() => 
  formData.value.password && 
  formData.value.confirmPassword && 
  formData.value.password === formData.value.confirmPassword
)

const isPasswordValid = computed(() => 
  hasMinLength.value && 
  hasUppercase.value && 
  hasLowercase.value && 
  hasNumber.value && 
  hasSpecial.value
)

const isFormValid = computed(() => 
  isPasswordValid.value && 
  passwordsMatch.value && 
  !errors.value.password && 
  !errors.value.confirmPassword
)

// Validate individual fields
const validateField = (field: keyof typeof formData.value) => {
  errors.value[field] = ''

  switch (field) {
    case 'password':
      if (!formData.value.password) {
        errors.value.password = t('auth.passwordRequired')
      } else if (!isPasswordValid.value) {
        errors.value.password = t('auth.passwordRequirementsNotMet')
      }
      break
    
    case 'confirmPassword':
      if (!formData.value.confirmPassword) {
        errors.value.confirmPassword = t('auth.confirmPasswordRequired')
      } else if (formData.value.password && !passwordsMatch.value) {
        errors.value.confirmPassword = t('auth.passwordsDoNotMatch')
      }
      break
  }
}

// Validate form
const validateForm = () => {
  validateField('password')
  validateField('confirmPassword')
  return !errors.value.password && !errors.value.confirmPassword
}

// Handle password reset
const handleResetPassword = async () => {
  if (!validateForm()) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('auth.pleaseFixErrors'),
      life: 3000
    })
    return
  }

  if (!resetToken.value) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('auth.invalidResetToken'),
      life: 3000
    })
    return
  }

  loading.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In real app, call API:
    // await authStore.resetPassword(resetToken.value, formData.value.password)
    
    resetSuccess.value = true
    
    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('auth.passwordResetSuccessMessage'),
      life: 3000
    })

    // Redirect to login after 3 seconds
    setTimeout(() => {
      router.push('/auth/login')
    }, 3000)
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: error.message || t('auth.passwordResetFailed'),
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

// Watch for password changes to revalidate confirm password
watch(() => formData.value.password, () => {
  if (formData.value.confirmPassword) {
    validateField('confirmPassword')
  }
})

// Check for valid token on mount
if (!resetToken.value) {
  toast.add({
    severity: 'warn',
    summary: t('common.warning'),
    detail: t('auth.noResetToken'),
    life: 5000
  })
  router.push('/auth/forgot-password')
}
</script>

<style lang="scss" scoped>
// Password strength and requirements styling (keeping custom parts)
.password-requirements {
  padding: 1rem;

  .requirement-title {
    color: white;
    font-weight: 500;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
  }

  .requirement-list {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 0.5rem;
      font-size: 0.85rem;
      transition: all 0.3s ease;

      i {
        font-size: 0.75rem;
        transition: all 0.3s ease;
      }

      &.met {
        color: #86efac;

        i {
          color: #86efac;
        }
      }
    }
  }
}

.password-strength-header {
  color: white;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

// Progress bar styling
.redirect-progress {
  :deep(.p-progressbar) {
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;

    .p-progressbar-value {
      background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }
  }
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

// Responsive
@media (max-width: 480px) {
  .password-requirements {
    padding: 0.75rem;

    .requirement-title {
      font-size: 0.85rem;
    }

    .requirement-list li {
      font-size: 0.8rem;
    }
  }
}
</style>
