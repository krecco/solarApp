<template>
  <div class="auth-form-wrapper animate-fade-in">
    <!-- Logo & Title -->
    <div class="auth-header">
      <div class="auth-logo hover-scale">
        <i class="pi pi-key"></i>
      </div>
      <h2 class="auth-title">{{ $t('auth.forgotPasswordTitle') || 'Forgot Password?' }}</h2>
      <p class="auth-subtitle">{{ $t('auth.forgotPasswordSubtitleAlt') || "No worries, we'll send you reset instructions" }}</p>
    </div>

    <!-- Forgot Password Form -->
    <form @submit.prevent="handleResetRequest" class="auth-form">
      <!-- Email Field -->
      <div class="form-group animate-slide-up delay-100">
        <label for="email" class="form-label">{{ $t('auth.email') || 'Email' }}</label>
        <div class="input-wrapper">
          <i class="pi pi-envelope input-icon"></i>
          <InputText
            id="email"
            v-model="formData.email"
            type="email"
            required
            class="form-input with-icon"
            :placeholder="$t('auth.enterRegisteredEmail') || 'Enter your registered email'"
            :class="{ 'p-invalid': errors.email }"
            :disabled="isSubmitted"
          />
        </div>
        <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
        <div class="form-hint-text animate-fade-in delay-200">
          {{ $t('auth.forgotPasswordHint') || "We'll send you a link to reset your password" }}
        </div>
      </div>

      <!-- Error Message -->
      <Message v-if="errorMessage" severity="error" :closable="false" class="animate-fade-in">
        {{ errorMessage }}
      </Message>

      <!-- Success Message -->
      <Message v-if="successMessage" severity="success" :closable="false" class="glass-card-light animate-scale-in">
        <div class="success-content">
          <i class="pi pi-check-circle"></i>
          <div>
            <strong>{{ $t('auth.emailSent') || 'Email Sent!' }}</strong>
            <p>{{ successMessage }}</p>
          </div>
        </div>
      </Message>

      <!-- Submit Button -->
      <Button
        v-if="!isSubmitted"
        type="submit"
        :label="$t('auth.sendResetLink') || 'Send Reset Link'"
        :loading="loading"
        class="auth-button hover-lift shadow-hover animate-slide-up delay-300"
        size="large"
        icon="pi pi-send"
      />

      <!-- Resend Button -->
      <div v-else class="resend-section animate-fade-in">
        <Button
          type="button"
          :label="$t('auth.resendEmail') || 'Resend Email'"
          :loading="loading"
          class="auth-button hover-lift shadow-hover"
          size="large"
          icon="pi pi-refresh"
          @click="resendEmail"
          :disabled="resendCooldown > 0"
        />
        <p v-if="resendCooldown > 0" class="resend-timer animate-pulse">
          {{ $t('auth.resendIn') || 'Resend available in' }} {{ resendCooldown }}s
        </p>
      </div>

      <!-- Footer Links -->
      <div class="auth-footer-links animate-fade-in delay-400">
        <router-link to="/auth/login" class="back-link">
          <i class="pi pi-arrow-left"></i>
          {{ $t('auth.backToLogin') || 'Back to login' }}
        </router-link>
      </div>

      <!-- Contact Support -->
      <p class="auth-footer-text animate-fade-in delay-500">
        {{ $t('auth.contactSupport') || 'Contact Support' }}
        <a href="mailto:support@example.com" class="hover-glow">
          support@example.com
        </a>
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const toast = useToast()

// Form data
const formData = reactive({
  email: ''
})

// State
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const isSubmitted = ref(false)
const resendCooldown = ref(0)
const errors = reactive({
  email: ''
})

let resendTimer: NodeJS.Timeout | null = null

// Validation
const validateForm = () => {
  errors.email = ''
  
  if (!formData.email) {
    errors.email = t('auth.emailRequired') || 'Email is required'
    return false
  }
  
  if (!/\S+@\S+\.\S+/.test(formData.email)) {
    errors.email = t('auth.emailInvalid') || 'Please enter a valid email'
    return false
  }
  
  return true
}

// Handle reset request
const handleResetRequest = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    await authStore.requestPasswordReset(formData.email)
    
    successMessage.value = t('auth.resetEmailSent') + ' ' + formData.email + '. ' + (t('auth.checkInbox') || 'Please check your inbox.')
    
    isSubmitted.value = true
    startResendCooldown()
    
    toast.add({
      severity: 'success',
      summary: t('auth.emailSentTitle') || 'Email Sent!',
      detail: t('auth.checkInbox') || 'Please check your email inbox',
      life: 5000
    })
  } catch (error: any) {
    errorMessage.value = error.message || 
      t('auth.resetError') || 'Failed to send reset email. Please try again.'
    
    // Add shake animation to form
    const form = document.querySelector('.auth-form') as HTMLElement
    if (form) {
      form.classList.add('shake')
      setTimeout(() => form.classList.remove('shake'), 500)
    }
  } finally {
    loading.value = false
  }
}

// Resend email
const resendEmail = async () => {
  if (resendCooldown.value > 0) return
  
  loading.value = true
  errorMessage.value = ''
  
  try {
    await authStore.requestPasswordReset(formData.email)
    
    toast.add({
      severity: 'success',
      summary: t('auth.emailResent') || 'Email Resent!',
      detail: t('auth.checkInboxAgain') || 'Please check your email inbox',
      life: 3000
    })
    
    startResendCooldown()
  } catch (error: any) {
    errorMessage.value = error.message || 
      t('auth.resendError') || 'Failed to resend email. Please try again.'
  } finally {
    loading.value = false
  }
}

// Start resend cooldown
const startResendCooldown = () => {
  resendCooldown.value = 60
  
  resendTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(resendTimer!)
      resendTimer = null
    }
  }, 1000)
}

// Cleanup
onUnmounted(() => {
  if (resendTimer) {
    clearInterval(resendTimer)
  }
})
</script>

<style lang="scss" scoped>
@import '@/styles/auth-shared';

// Additional forgot password specific styles
.auth-form {
  &.shake {
    animation: shake 0.5s;
  }
  
  .form-hint-text {
    margin-top: 0.75rem;
    padding: 0.625rem 0.875rem;
    font-size: 0.813rem;
    color: var(--text-color-secondary);
    text-align: center;
    line-height: 1.5;
    background: transparent;
    border: none;
  }
  
  .success-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    
    i {
      font-size: 1.5rem;
      color: #10b981;
      flex-shrink: 0;
      margin-top: 0.125rem;
    }
    
    div {
      flex: 1;
      
      strong {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 1rem;
        color: var(--text-color);
      }
      
      p {
        margin: 0;
        font-size: 0.875rem;
        color: var(--text-color-secondary);
        line-height: 1.5;
      }
    }
  }
  
  .resend-section {
    text-align: center;
    
    .resend-timer {
      margin-top: 1rem;
      font-size: 0.875rem;
      color: var(--text-color-secondary);
      font-weight: 500;
    }
  }
  
  .help-section {
    margin-top: 2rem;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    
    .help-title {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 0.75rem;
    }
    
    .help-text {
      font-size: 0.813rem;
      color: var(--text-color-secondary);
      margin: 0;
      line-height: 1.6;
      
      .support-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        
        &:hover {
          text-decoration: underline;
          color: var(--primary-600);
        }
      }
    }
  }
}

// Dark mode adjustments
.dark {
  .form-hint-text {
    color: var(--text-color-secondary);
  }
}

// Responsive adjustments
@media (max-width: 480px) {
  .auth-form {
    .help-section {
      padding: 1.25rem;
      
      .help-text {
        font-size: 0.75rem;
      }
    }
    
    .form-hint-text {
      font-size: 0.75rem;
    }
    
    .auth-footer-links {
      .back-link {
        padding: 0.75rem 1.25rem;
      }
    }
  }
}
</style>
