<template>
  <div class="auth-form-wrapper animate-fade-in">
    <!-- Logo & Title -->
    <div class="auth-header">
      <div class="auth-logo hover-scale">
        <i class="pi pi-shield"></i>
      </div>
      <h2 class="auth-title">{{ $t('auth.verifyTitle') || 'Verify Your Identity' }}</h2>
      <p class="auth-subtitle">
        {{ $t('auth.verifySubtitle') || `Enter the code we sent to ${maskedRecipient}` }}
      </p>
    </div>

    <!-- OTP Verification Form -->
    <form @submit.prevent="handleVerification" class="auth-form">
      <!-- OTP Input -->
      <div class="form-group animate-slide-up delay-100">
        <label class="form-label otp-label">{{ $t('auth.verificationCode') || 'Verification Code' }}</label>
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
            :disabled="loading"
          />
        </div>
        <small v-if="errors.otp" class="p-error text-center">{{ errors.otp }}</small>
      </div>

      <!-- Timer and Resend -->
      <div class="resend-section animate-slide-up delay-200">
        <span v-if="resendTimer > 0" class="timer-text">
          {{ $t('auth.resendCodeIn') || 'Resend code in' }} 
          <span class="timer-value">{{ formatTimer(resendTimer) }}</span>
        </span>
        <Button
          v-else
          type="button"
          :label="$t('auth.resendCode') || 'Resend Code'"
          class="p-button-text p-button-sm hover-glow"
          @click="handleResend"
          :loading="resending"
        />
      </div>



      <!-- Error/Success Message -->
      <Message v-if="message.text" :severity="message.type" :closable="false" class="animate-fade-in">
        {{ message.text }}
      </Message>

      <!-- Verify Button -->
      <Button
        type="submit"
        :label="loading ? ($t('auth.verifying') || 'Verifying...') : ($t('auth.verify') || 'Verify')"
        :loading="loading"
        class="auth-button hover-lift shadow-hover animate-slide-up delay-400"
        size="large"
        :disabled="!isOtpComplete"
      />

      <!-- Alternative Methods -->
      <div class="alternative-methods animate-fade-in delay-500">
        <p class="alternative-text">{{ $t('auth.havingTrouble') || 'Having trouble?' }}</p>
        <router-link to="/auth/login" class="alternative-link hover-glow">
          <i class="pi pi-sign-in"></i>
          {{ $t('auth.usePassword') || 'Use password instead' }}
        </router-link>
      </div>

      <!-- Footer -->
      <p class="auth-footer-text animate-fade-in delay-600">
        {{ $t('auth.didntReceive') || "Didn't receive the code?" }}
        <a href="#" @click.prevent="showHelp" class="hover-glow">
          {{ $t('auth.getHelp') || 'Get help' }}
        </a>
      </p>
    </form>

    <!-- Help Dialog -->
    <Dialog
      v-model:visible="helpVisible"
      :header="$t('auth.helpTitle') || 'Need Help?'"
      :modal="true"
      :closable="true"
      :style="{ width: '90vw', maxWidth: '500px' }"
    >
      <div class="help-content">
        <p>{{ $t('auth.helpText') || 'If you\'re having trouble receiving your verification code:' }}</p>
        <ul>
          <li>{{ $t('auth.helpCheck') || 'Check your spam folder' }}</li>
          <li>{{ $t('auth.helpVerify') || 'Verify your contact information is correct' }}</li>
          <li>{{ $t('auth.helpWait') || 'Wait a few minutes and try resending' }}</li>
          <li>{{ $t('auth.helpContact') || 'Contact support if issues persist' }}</li>
        </ul>
      </div>
      <template #footer>
        <Button 
          :label="$t('common.close') || 'Close'" 
          @click="helpVisible = false" 
          class="p-button-text"
        />
        <Button 
          :label="$t('auth.contactSupport') || 'Contact Support'" 
          @click="contactSupport" 
          icon="pi pi-external-link"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Dialog from 'primevue/dialog'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const toast = useToast()

// Props from route
const method = ref(route.query.method as string || 'email')
const recipient = ref(route.query.recipient as string || '')

// OTP inputs
const otpLength = 6
const otpDigits = ref<string[]>(Array(otpLength).fill(''))
const otpInputs = ref<(HTMLInputElement | null)[]>([])

// State
const loading = ref(false)
const resending = ref(false)
const hasError = ref(false)

const resendTimer = ref(30)
const helpVisible = ref(false)

const message = reactive({
  type: 'error' as 'error' | 'success' | 'info',
  text: ''
})

const errors = reactive({
  otp: ''
})

// Timer interval
let timerInterval: number | undefined

// Computed
const isOtpComplete = computed(() => {
  return otpDigits.value.every(digit => digit !== '')
})

const maskedRecipient = computed(() => {
  if (!recipient.value) return ''
  
  if (method.value === 'email') {
    const [username, domain] = recipient.value.split('@')
    if (username.length <= 3) {
      return `${username}***@${domain}`
    }
    return `${username.slice(0, 3)}***@${domain}`
  } else {
    // Phone number masking
    const cleaned = recipient.value.replace(/\D/g, '')
    if (cleaned.length >= 10) {
      return `***-***-${cleaned.slice(-4)}`
    }
    return recipient.value
  }
})



// Format timer display
const formatTimer = (seconds: number) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// Handle OTP input
const handleOtpInput = (event: Event, index: number) => {
  const input = event.target as HTMLInputElement
  const value = input.value
  
  // Clear any previous errors
  hasError.value = false
  errors.otp = ''
  
  // Only allow digits
  if (!/^\d*$/.test(value)) {
    otpDigits.value[index] = ''
    return
  }
  
  // Move to next input if value is entered
  if (value && index < otpLength - 1) {
    otpInputs.value[index + 1]?.focus()
  }
  
  // Auto-submit if all digits are filled
  if (isOtpComplete.value) {
    handleVerification()
  }
}

// Handle keyboard navigation
const handleKeyDown = (event: KeyboardEvent, index: number) => {
  // Handle backspace
  if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    event.preventDefault()
    otpInputs.value[index - 1]?.focus()
  }
  
  // Handle arrow keys
  if (event.key === 'ArrowLeft' && index > 0) {
    event.preventDefault()
    otpInputs.value[index - 1]?.focus()
  }
  
  if (event.key === 'ArrowRight' && index < otpLength - 1) {
    event.preventDefault()
    otpInputs.value[index + 1]?.focus()
  }
}

// Handle paste
const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault()
  const pastedData = event.clipboardData?.getData('text') || ''
  const digits = pastedData.replace(/\D/g, '').split('')
  
  digits.forEach((digit, index) => {
    if (index < otpLength) {
      otpDigits.value[index] = digit
    }
  })
  
  // Focus on the last filled input or the first empty one
  const lastFilledIndex = otpDigits.value.findLastIndex(d => d !== '')
  if (lastFilledIndex < otpLength - 1) {
    otpInputs.value[lastFilledIndex + 1]?.focus()
  } else {
    otpInputs.value[otpLength - 1]?.focus()
  }
  
  // Auto-submit if complete
  if (isOtpComplete.value) {
    handleVerification()
  }
}

// Handle verification
const handleVerification = async () => {
  if (!isOtpComplete.value) {
    errors.otp = t('auth.otpRequired') || 'Please enter the complete code'
    hasError.value = true
    return
  }
  
  loading.value = true
  message.text = ''
  
  try {
    const otp = otpDigits.value.join('')
    
    await authStore.verifyOtp({
      code: otp,
      method: method.value,
      recipient: recipient.value
    })
    
    toast.add({
      severity: 'success',
      summary: t('auth.verifySuccess') || 'Verification successful!',
      detail: t('auth.welcomeBack') || 'Welcome back',
      life: 3000
    })
    
    // Redirect to dashboard
    router.push('/dashboard')
  } catch (error: any) {
    hasError.value = true
    errors.otp = error.message || t('auth.otpInvalid') || 'Invalid or expired code'
    
    // Clear OTP inputs
    otpDigits.value = Array(otpLength).fill('')
    otpInputs.value[0]?.focus()
    
    // Add shake animation
    const container = document.querySelector('.otp-input-container') as HTMLElement
    if (container) {
      container.classList.add('shake')
      setTimeout(() => container.classList.remove('shake'), 500)
    }
  } finally {
    loading.value = false
  }
}

// Handle resend
const handleResend = async () => {
  resending.value = true
  message.text = ''
  
  try {
    await authStore.requestOtp({
      method: method.value,
      recipient: recipient.value
    })
    
    message.type = 'success'
    message.text = t('auth.otpResent') || 'Code resent successfully!'
    
    // Reset timer
    resendTimer.value = 60
    startTimer()
    
    // Clear and focus first input
    otpDigits.value = Array(otpLength).fill('')
    otpInputs.value[0]?.focus()
  } catch (error: any) {
    message.type = 'error'
    message.text = error.message || t('auth.resendError') || 'Failed to resend code'
  } finally {
    resending.value = false
  }
}

// Start countdown timer
const startTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
  
  timerInterval = window.setInterval(() => {
    resendTimer.value--
    if (resendTimer.value <= 0) {
      clearInterval(timerInterval)
    }
  }, 1000)
}



// Show help dialog
const showHelp = () => {
  helpVisible.value = true
}

// Contact support
const contactSupport = () => {
  window.open('/support', '_blank')
}

// Lifecycle
onMounted(() => {
  // Focus first input
  setTimeout(() => {
    otpInputs.value[0]?.focus()
  }, 300)
  
  // Start timer
  startTimer()
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})

// Watch for route changes
watch(() => route.query, (newQuery) => {
  if (newQuery.method) {
    method.value = newQuery.method as string
  }
  if (newQuery.recipient) {
    recipient.value = newQuery.recipient as string
  }
})
</script>

<style lang="scss" scoped>
@import '@/styles/auth-shared';

// OTP label styles
.otp-label {
  text-align: center !important;
  font-size: 1rem !important;
  font-weight: 600 !important;
  color: var(--text-color) !important;
  margin-bottom: 1rem !important;
  display: block;
}

// OTP input styles
.otp-input-container {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin-bottom: 0.5rem;
  
  &.shake {
    animation: shake 0.5s;
  }
  
  .otp-input {
    width: 3.5rem;
    height: 3.5rem;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    background: var(--surface-0);
    border: 2px solid var(--surface-border);
    border-radius: 0.5rem;
    transition: all 0.2s;
    color: var(--text-color);
    caret-color: var(--primary-color);
    
    &:hover {
      border-color: var(--primary-color);
      background: var(--surface-50);
    }
    
    &:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px var(--primary-color-alpha);
      background: var(--surface-0);
    }
    
    &.has-value {
      background: var(--primary-50);
      border-color: var(--primary-color);
      color: var(--primary-color);
    }
    
    &.p-invalid {
      border-color: #ef4444;
      
      &:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
      }
    }
    
    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    // Dark mode
    :global(.dark) & {
      background: var(--surface-900);
      border-color: var(--surface-700);
      
      &:hover {
        background: var(--surface-800);
      }
      
      &:focus {
        background: var(--surface-900);
      }
      
      &.has-value {
        background: var(--primary-900);
        color: var(--primary-color);
      }
    }
  }
}

// Resend section
.resend-section {
  text-align: center;
  margin: 1.5rem 0;
  
  .timer-text {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    
    .timer-value {
      font-weight: 600;
      color: var(--primary-color);
      font-variant-numeric: tabular-nums;
    }
  }
}

// Alternative methods
.alternative-methods {
  margin-top: 2rem;
  text-align: center;
  
  .alternative-text {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    margin-bottom: 0.75rem;
  }
  
  .alternative-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    
    i {
      font-size: 1rem;
    }
    
    &:hover {
      color: var(--primary-600);
      text-decoration: underline;
    }
  }
}

// Help dialog content
.help-content {
  p {
    margin-bottom: 1rem;
    color: var(--text-color);
  }
  
  ul {
    padding-left: 1.5rem;
    
    li {
      margin-bottom: 0.5rem;
      color: var(--text-color-secondary);
    }
  }
}

// Responsive adjustments
@media (max-width: 480px) {
  .otp-input-container {
    gap: 0.5rem;
    
    .otp-input {
      width: 3rem;
      height: 3rem;
      font-size: 1.25rem;
    }
  }
}
</style>
