<template>
  <div class="auth-form-wrapper animate-fade-in">
    <!-- Logo & Title -->
    <div class="auth-header">
      <div class="auth-logo hover-scale">
        <i class="pi pi-envelope"></i>
      </div>
      <h2 class="auth-title">Verify Your Email</h2>
      <p class="auth-subtitle">We've sent a verification code to your email</p>
    </div>

    <!-- Email Display -->
    <div class="email-display" v-if="userEmail">
      <i class="pi pi-mail"></i>
      <span>{{ userEmail }}</span>
    </div>

    <!-- Verification Form -->
    <form @submit.prevent="handleVerify" class="auth-form">
      <!-- OTP Input -->
      <div class="form-group animate-slide-up delay-100">
        <label class="form-label otp-label">Verification Code</label>
        <div class="otp-input-container">
          <input
            v-for="(digit, index) in otpDigits"
            :key="index"
            :ref="el => otpInputs[index] = el"
            v-model="otpDigits[index]"
            type="text"
            inputmode="numeric"
            maxlength="1"
            class="otp-input"
            :class="{ 
              'has-value': digit !== '',
              'p-invalid': hasError && digit === ''
            }"
            @input="handleOtpInput($event, index)"
            @keydown="handleKeyDown($event, index)"
            @paste="handlePaste"
            :disabled="isLoading"
          />
        </div>
        <small class="form-hint text-center">Enter the 6-digit code sent to your email</small>
        <small v-if="error" class="p-error text-center">{{ error }}</small>
      </div>
      
      <!-- Timer and Resend -->
      <div class="resend-section animate-slide-up delay-200">
        <span v-if="resendCooldown > 0" class="timer-text">
          Resend code in 
          <span class="timer-value">{{ formatTimer(resendCooldown) }}</span>
        </span>
        <Button
          v-else
          type="button"
          label="Resend Code"
          class="p-button-text p-button-sm hover-glow"
          @click="resendCode"
          :loading="isResending"
        />
      </div>
      
      <!-- Success Message -->
      <Message v-if="successMessage" severity="success" :closable="false" class="animate-fade-in">
        {{ successMessage }}
      </Message>
      
      <!-- Error Message -->
      <Message v-if="errorMessage" severity="error" :closable="false" class="animate-fade-in">
        {{ errorMessage }}
      </Message>
      
      <!-- Submit Button -->
      <Button
        type="submit"
        :label="isLoading ? 'Verifying...' : 'Verify Email'"
        :loading="isLoading"
        :disabled="!isOtpComplete"
        class="auth-button hover-lift shadow-hover animate-slide-up delay-400"
        size="large"
      />
    </form>

    <!-- Footer Links -->
    <div class="footer-links-section">
      <p class="auth-footer-text animate-fade-in">
        Changed your mind?
        <router-link to="/auth/login" class="auth-link">Back to Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/api/auth.service'
import Button from 'primevue/button'
import Message from 'primevue/message'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

// OTP inputs
const otpLength = 6
const otpDigits = ref<string[]>(Array(otpLength).fill(''))
const otpInputs = ref<(HTMLInputElement | null)[]>([])

const userEmail = ref('')
const isLoading = ref(false)
const isResending = ref(false)
const hasError = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const error = ref('')
const resendCooldown = ref(0)
let cooldownInterval: number | null = null

// Computed
const isOtpComplete = computed(() => {
  return otpDigits.value.every(digit => digit !== '')
})

// Format timer display
const formatTimer = (seconds: number) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}` : `${secs}s`
}

// Handle OTP input
const handleOtpInput = (event: Event, index: number) => {
  const input = event.target as HTMLInputElement
  const value = input.value
  
  // Only allow digits
  if (!/^\d*$/.test(value)) {
    input.value = otpDigits.value[index]
    return
  }
  
  // Clear error when user starts typing
  if (hasError.value) {
    hasError.value = false
    error.value = ''
    errorMessage.value = ''
  }
  
  // Move to next input if value entered
  if (value && index < otpLength - 1) {
    otpInputs.value[index + 1]?.focus()
  }
  
  // Auto-submit when all digits entered
  if (isOtpComplete.value) {
    handleVerify()
  }
}

// Handle key down events
const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  } else if (event.key === 'ArrowLeft' && index > 0) {
    otpInputs.value[index - 1]?.focus()
  } else if (event.key === 'ArrowRight' && index < otpLength - 1) {
    otpInputs.value[index + 1]?.focus()
  }
}

// Handle paste
const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault()
  const pastedData = event.clipboardData?.getData('text/plain')
  if (pastedData && /^\d{6}$/.test(pastedData)) {
    otpDigits.value = pastedData.split('')
    otpInputs.value[otpLength - 1]?.focus()
    handleVerify()
  }
}

onMounted(() => {
  // Get email from session storage (set during registration)
  userEmail.value = sessionStorage.getItem('verification_email') || ''
  
  // If no email found, check if user is logged in
  if (!userEmail.value && authStore.user) {
    userEmail.value = authStore.user.email
  }
  
  // If still no email, redirect to login
  if (!userEmail.value) {
    toast.add({
      severity: 'warn',
      summary: 'Session Expired',
      detail: 'Please register or login again',
      life: 3000
    })
    router.push('/auth/login')
  }
  
  // Focus first input
  setTimeout(() => {
    otpInputs.value[0]?.focus()
  }, 100)
})

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
  }
})

const handleVerify = async () => {
  if (!isOtpComplete.value) {
    hasError.value = true
    error.value = 'Please enter all 6 digits'
    return
  }
  
  if (!userEmail.value) {
    errorMessage.value = 'Email address is missing. Please try registering again.'
    return
  }
  
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  hasError.value = false
  
  try {
    // Join OTP digits
    const verificationCode = otpDigits.value.join('')
    const response = await authService.verifyEmail(userEmail.value, verificationCode)
    
    successMessage.value = response.message || 'Email verified successfully!'
    
    // If verification response includes token, user is now logged in
    if (response.token && response.user) {
      // Update auth store with logged in user
      authStore.token = response.token
      authStore.user = response.user
      authStore.isAuthenticated = true
      authStore.needsProfileCompletion = response.needs_profile_completion || false
    }
    
    // Clear verification email from session
    sessionStorage.removeItem('verification_email')
    
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Your email has been verified successfully!',
      life: 3000
    })
    
    // After email verification, check where to redirect
    setTimeout(() => {
      // Check if there's a post-verification redirect URL
      const postVerificationRedirect = sessionStorage.getItem('post_verification_redirect')
      if (postVerificationRedirect) {
        sessionStorage.removeItem('post_verification_redirect')
        router.push(postVerificationRedirect)
      } else if (response.token) {
        // User is now logged in after verification
        if (response.needs_profile_completion) {
          router.push('/profile/complete')  // App area, not auth!
        } else {
          router.push('/dashboard')
        }
      } else {
        // Fallback to login if no token (shouldn't happen)
        router.push('/auth/login')
      }
    }, 1500)
  } catch (error: any) {
    errorMessage.value = error.message || 'Invalid or expired verification code'
    hasError.value = true
    otpDigits.value = Array(otpLength).fill('')
    otpInputs.value[0]?.focus()
  } finally {
    isLoading.value = false
  }
}

const resendCode = async () => {
  if (resendCooldown.value > 0) return
  
  if (!userEmail.value) {
    errorMessage.value = 'Email address is missing. Please try registering again.'
    return
  }
  
  isResending.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    await authService.resendVerificationEmail(userEmail.value)
    
    successMessage.value = 'Verification code sent! Please check your email.'
    
    // Start cooldown timer (60 seconds)
    resendCooldown.value = 60
    cooldownInterval = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(cooldownInterval!)
        cooldownInterval = null
      }
    }, 1000)
    
    toast.add({
      severity: 'success',
      summary: 'Code Sent',
      detail: 'A new verification code has been sent to your email',
      life: 3000
    })
    
    // Clear and focus first input
    otpDigits.value = Array(otpLength).fill('')
    otpInputs.value[0]?.focus()
  } catch (error: any) {
    errorMessage.value = error.message || 'Failed to resend verification code'
  } finally {
    isResending.value = false
  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/auth-shared';

.email-display {
  background: var(--surface-50);
  border: 1px solid var(--surface-border);
  border-radius: 8px;
  padding: 0.875rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  color: var(--text-color);
  
  i {
    color: var(--primary-color);
  }
  
  :global(.dark) & {
    background: var(--surface-800);
    border-color: var(--surface-700);
  }
}

// OTP Input Styles
.otp-label {
  text-align: center;
  margin-bottom: 1rem;
  font-weight: 600;
  color: var(--text-color);
}

.otp-input-container {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.otp-input {
  width: 3rem;
  height: 3.5rem;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  border: 2px solid var(--surface-border);
  border-radius: 0.75rem;
  background: var(--surface-0);
  color: var(--text-color);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-family: 'Monaco', 'Courier New', monospace;
  
  &:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-500-rgb), 0.1);
    transform: scale(1.05);
  }
  
  &.has-value {
    background: var(--primary-50);
    border-color: var(--primary-color);
    color: var(--primary-color);
    
    :global(.dark) & {
      background: rgba(var(--primary-500-rgb), 0.1);
    }
  }
  
  &.p-invalid {
    border-color: var(--red-500);
    
    &:focus {
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  
  :global(.dark) & {
    background: var(--surface-900);
    border-color: var(--surface-700);
    
    &:focus {
      background: var(--surface-800);
    }
  }
}

// Timer and Resend Section
.resend-section {
  text-align: center;
  margin: 1.5rem 0;
  
  .timer-text {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    
    .timer-value {
      font-weight: 600;
      color: var(--primary-color);
      margin-left: 0.25rem;
    }
  }
  
  :deep(.p-button-text) {
    font-weight: 600;
    
    &:hover {
      background: var(--primary-50);
      
      :global(.dark) & {
        background: rgba(var(--primary-500-rgb), 0.1);
      }
    }
  }
}

.form-hint {
  display: block;
  margin-top: 0.5rem;
  margin-bottom: 0.25rem;
  font-size: 0.75rem;
  color: var(--text-color-secondary);
  text-align: center;
}

// Animations
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

.animate-slide-up {
  animation: slideUp 0.5s ease-out;
  
  @for $i from 1 through 6 {
    &.delay-#{$i * 100} {
      animation-delay: #{$i * 0.1}s;
      animation-fill-mode: both;
    }
  }
}

// Responsive
@media (max-width: 480px) {
  .otp-input {
    width: 2.5rem;
    height: 3rem;
    font-size: 1.25rem;
  }
  
  .otp-input-container {
    gap: 0.5rem;
  }
}
</style>
