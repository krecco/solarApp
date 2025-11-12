<template>
  <div class="auth-page-bg">
    <!-- Background -->
    <div class="auth-bg-elements">
      <div class="gradient-sphere sphere-1"></div>
      <div class="gradient-sphere sphere-2"></div>
      <div class="animated-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
      </div>
    </div>

    <!-- Container -->
    <div class="auth-container-lg">
      <Card class="auth-glass-card">
        <template #content>
          <!-- Verifying State -->
          <div v-if="verificationState === 'verifying'" class="verification-content">
            <div class="auth-icon-container">
              <ProgressSpinner 
                strokeWidth="4"
                style="width: 80px; height: 80px"
                animationDuration="1s"
              />
            </div>
            <div class="auth-header-center">
              <h2 class="auth-title">{{ $t('auth.verifyingEmail') }}</h2>
              <p class="auth-subtitle">{{ $t('auth.pleaseWait') }}</p>
            </div>
            <div class="auth-loading-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>

          <!-- Success State -->
          <div v-else-if="verificationState === 'success'" class="verification-content auth-state-success">
            <div class="auth-icon-container-success animate-scale-in">
              <i class="pi pi-check-circle"></i>
            </div>
            <div class="auth-header-center">
              <h2 class="auth-title">{{ $t('auth.emailVerified') }}</h2>
              <p class="auth-subtitle">{{ $t('auth.emailVerifiedSuccess') }}</p>
            </div>
            
            <div class="success-details">
              <div class="detail-item">
                <i class="pi pi-envelope"></i>
                <span>{{ verifiedEmail }}</span>
              </div>
              <div class="detail-item">
                <i class="pi pi-calendar"></i>
                <span>{{ $t('auth.verifiedOn') }} {{ formattedDate }}</span>
              </div>
            </div>

            <Button
              :label="$t('auth.continueToLogin')"
              icon="pi pi-arrow-right"
              iconPos="right"
              @click="redirectToLogin"
              class="auth-button-gradient hover-lift"
            />
          </div>

          <!-- Error State -->
          <div v-else-if="verificationState === 'error'" class="verification-content">
            <div class="auth-icon-container-error animate-scale-in">
              <i class="pi pi-times-circle"></i>
            </div>
            <div class="auth-header-center">
              <h2 class="auth-title">{{ $t('auth.verificationFailed') }}</h2>
              <p class="auth-subtitle">{{ errorMessage }}</p>
            </div>

            <div class="error-actions">
              <Button
                :label="$t('auth.resendVerification')"
                icon="pi pi-refresh"
                @click="resendVerification"
                :loading="resending"
                class="auth-button-glass hover-lift"
              />
              <Button
                :label="$t('auth.backToLogin')"
                icon="pi pi-arrow-left"
                @click="redirectToLogin"
                class="auth-button-gradient"
              />
            </div>

            <div v-if="resendSuccess" class="resend-success animate-fade-in">
              <i class="pi pi-check-circle"></i>
              {{ $t('auth.verificationEmailSent') }}
            </div>
          </div>

          <!-- Invalid Token State -->
          <div v-else-if="verificationState === 'invalid'" class="verification-content auth-state-error">
            <div class="auth-icon-container-warning animate-scale-in">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div class="auth-header-center">
              <h2 class="auth-title">{{ $t('auth.invalidVerificationLink') }}</h2>
              <p class="auth-subtitle">{{ $t('auth.invalidLinkMessage') }}</p>
            </div>

            <div class="error-details">
              <div class="reason-item">
                <i class="pi pi-clock"></i>
                <span>{{ $t('auth.linkExpired') }}</span>
              </div>
              <div class="reason-item">
                <i class="pi pi-link"></i>
                <span>{{ $t('auth.linkAlreadyUsed') }}</span>
              </div>
              <div class="reason-item">
                <i class="pi pi-ban"></i>
                <span>{{ $t('auth.linkInvalid') }}</span>
              </div>
            </div>

            <div class="invalid-actions">
              <Button
                :label="$t('auth.requestNewLink')"
                icon="pi pi-send"
                @click="showRequestForm = true"
                class="auth-button-gradient hover-lift"
              />
              <Button
                :label="$t('auth.contactSupport')"
                icon="pi pi-question-circle"
                @click="contactSupport"
                class="auth-button-glass"
              />
            </div>
          </div>

          <!-- Request New Link Form -->
          <Dialog
            v-model:visible="showRequestForm"
            :header="$t('auth.requestVerificationEmail')"
            :modal="true"
            :closable="true"
            :style="{ width: '450px' }"
            class="request-dialog"
          >
            <div class="request-form">
              <div class="form-field">
                <label for="email" class="field-label">
                  <i class="pi pi-envelope"></i>
                  {{ $t('auth.email') }}
                </label>
                <InputText
                  v-model="requestEmail"
                  id="email"
                  type="email"
                  :placeholder="$t('auth.enterEmail')"
                  :class="{ 'p-invalid': emailError }"
                  @blur="validateEmail"
                  class="w-full"
                />
                <small v-if="emailError" class="p-error">{{ emailError }}</small>
              </div>

              <div class="dialog-actions">
                <Button
                  :label="$t('common.cancel')"
                  @click="showRequestForm = false"
                  text
                />
                <Button
                  :label="$t('auth.sendVerificationEmail')"
                  icon="pi pi-send"
                  @click="requestNewVerification"
                  :loading="requesting"
                  :disabled="!isEmailValid"
                />
              </div>
            </div>
          </Dialog>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '@/stores/auth'
import Card from 'primevue/card'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const toast = useToast()
const authStore = useAuthStore()

// Verification states: 'verifying' | 'success' | 'error' | 'invalid'
const verificationState = ref<string>('verifying')
const errorMessage = ref('')
const verifiedEmail = ref('')
const resending = ref(false)
const resendSuccess = ref(false)
const showRequestForm = ref(false)
const requestEmail = ref('')
const emailError = ref('')
const requesting = ref(false)

// Get verification token from URL
const verificationToken = computed(() => route.query.token as string)

// Format current date
const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

// Email validation
const isEmailValid = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(requestEmail.value) && !emailError.value
})

// Validate email field
const validateEmail = () => {
  emailError.value = ''
  
  if (!requestEmail.value) {
    emailError.value = t('auth.emailRequired')
  } else if (!isEmailValid.value) {
    emailError.value = t('auth.invalidEmail')
  }
}

// Verify email with token
const verifyEmail = async () => {
  if (!verificationToken.value) {
    verificationState.value = 'invalid'
    return
  }

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // In real app, call API:
    // const response = await authStore.verifyEmail(verificationToken.value)
    
    // Simulate different outcomes for demo
    const random = Math.random()
    
    if (random > 0.3) {
      // Success
      verificationState.value = 'success'
      verifiedEmail.value = 'user@example.com' // Would come from API response
      
      toast.add({
        severity: 'success',
        summary: t('common.success'),
        detail: t('auth.emailVerifiedSuccessMessage'),
        life: 5000
      })
    } else if (random > 0.1) {
      // Error - expired or used token
      verificationState.value = 'error'
      errorMessage.value = t('auth.verificationTokenExpired')
    } else {
      // Invalid token
      verificationState.value = 'invalid'
    }
  } catch (error: any) {
    verificationState.value = 'error'
    errorMessage.value = error.message || t('auth.verificationError')
    
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: errorMessage.value,
      life: 5000
    })
  }
}

// Resend verification email
const resendVerification = async () => {
  resending.value = true
  resendSuccess.value = false

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In real app, call API:
    // await authStore.resendVerification(verificationToken.value)
    
    resendSuccess.value = true
    
    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('auth.verificationEmailSentMessage'),
      life: 5000
    })
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: error.message || t('auth.resendVerificationFailed'),
      life: 5000
    })
  } finally {
    resending.value = false
  }
}

// Request new verification email
const requestNewVerification = async () => {
  if (!validateEmail()) return

  requesting.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In real app, call API:
    // await authStore.requestVerificationEmail(requestEmail.value)
    
    showRequestForm.value = false
    
    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('auth.verificationEmailSentTo', { email: requestEmail.value }),
      life: 5000
    })
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: error.message || t('auth.requestVerificationFailed'),
      life: 5000
    })
  } finally {
    requesting.value = false
  }
}

// Redirect to login
const redirectToLogin = () => {
  router.push('/auth/login')
}

// Contact support
const contactSupport = () => {
  // In real app, open support modal or redirect to support page
  toast.add({
    severity: 'info',
    summary: t('common.info'),
    detail: t('auth.supportContactInfo'),
    life: 5000
  })
}

// Start verification on mount
onMounted(() => {
  verifyEmail()
})
</script>

<style lang="scss" scoped>
.verification-content {
  text-align: center;
}

// Error and success actions
.error-actions,
.invalid-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 2rem;
}

// Resend success message
.resend-success {
  margin-top: 1.5rem;
  padding: 1rem;
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 8px;
  color: #86efac;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;

  i {
    font-size: 1rem;
  }
}

// Request Dialog Styles
:deep(.request-dialog) {
  .p-dialog-header {
    background: linear-gradient(135deg, var(--primary-400) 0%, var(--primary-600) 100%);
    color: white;
    border-radius: 12px 12px 0 0;
  }

  .p-dialog-content {
    padding: 2rem;
  }

  .request-form {
    .form-field {
      margin-bottom: 1.5rem;

      .field-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-color);
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;

        i {
          font-size: 0.85rem;
          opacity: 0.7;
        }
      }

      :deep(.p-inputtext) {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        transition: all 0.3s ease;

        &:hover {
          border-color: var(--primary-color);
        }

        &:focus {
          border-color: var(--primary-color);
          box-shadow: 0 0 0 3px rgba(var(--primary-500), 0.1);
        }

        &.p-invalid {
          border-color: #ef4444;
        }
      }

      .p-error {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
      }
    }

    .dialog-actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 2rem;
    }
  }
}

// Responsive
@media (max-width: 520px) {
  .error-actions,
  .invalid-actions {
    flex-direction: column;
  }
}
</style>
