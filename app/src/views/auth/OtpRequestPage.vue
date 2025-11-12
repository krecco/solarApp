<template>
  <div class="auth-form-wrapper animate-fade-in">
    <!-- Logo & Title -->
    <div class="auth-header">
      <div class="auth-logo hover-scale">
        <i class="pi pi-mobile"></i>
      </div>
      <h2 class="auth-title">{{ $t('auth.otpTitle') || 'One-Time Password' }}</h2>
      <p class="auth-subtitle">{{ $t('auth.otpSubtitle') || 'We\'ll send you a code to verify your identity' }}</p>
    </div>

    <!-- OTP Request Form -->
    <form @submit.prevent="handleOtpRequest" class="auth-form">
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
            :placeholder="$t('auth.emailPlaceholder') || 'admin@example.com'"
            :class="{ 'p-invalid': errors.email }"
          />
        </div>
        <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
      </div>



      <!-- Error/Success Message -->
      <Message v-if="message.text" :severity="message.type" :closable="false" class="animate-fade-in">
        {{ message.text }}
      </Message>

      <!-- Submit Button -->
      <Button
        type="submit"
        :label="loading ? ($t('auth.sending') || 'Sending...') : ($t('auth.sendCode') || 'Send Code')"
        :loading="loading"
        class="auth-button hover-lift shadow-hover animate-slide-up delay-200"
        size="large"
        :disabled="cooldown > 0"
      />

      <!-- Cooldown Timer -->
      <p v-if="cooldown > 0" class="cooldown-text animate-fade-in">
        {{ $t('auth.resendIn') || 'Resend code in' }} {{ cooldown }}s
      </p>

      <!-- Footer Links -->
      <p class="auth-footer-text animate-fade-in delay-300">
        {{ $t('auth.backTo') || 'Back to' }}
        <router-link to="/auth/login" class="hover-glow">
          {{ $t('auth.signIn') || 'Sign in' }}
        </router-link>
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
const cooldown = ref(0)
const message = reactive({
  type: 'error' as 'error' | 'success' | 'info',
  text: ''
})
const errors = reactive({
  email: ''
})

// Cooldown timer
let cooldownInterval: number | undefined

// Validation
const validateForm = () => {
  let isValid = true
  errors.email = ''
  message.text = ''
  
  if (!formData.email) {
    errors.email = t('auth.emailRequired') || 'Email is required'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
    errors.email = t('auth.emailInvalid') || 'Please enter a valid email'
    isValid = false
  }
  
  return isValid
}

// Start cooldown timer
const startCooldown = (seconds = 60) => {
  cooldown.value = seconds
  cooldownInterval = window.setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      clearInterval(cooldownInterval)
    }
  }, 1000)
}

// Handle OTP request
const handleOtpRequest = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  message.text = ''
  
  try {
    const response = await authStore.requestOtp({
      method: 'email',
      recipient: formData.email
    })
    
    message.type = 'success'
    message.text = response.message || t('auth.otpSent') || 'Code sent successfully!'
    
    // Start cooldown
    startCooldown()
    
    // Navigate to verification page after 2 seconds
    setTimeout(() => {
      router.push({
        name: 'otp-verify',
        query: {
          method: 'email',
          recipient: formData.email
        }
      })
    }, 2000)
  } catch (error: any) {
    message.type = 'error'
    message.text = error.message || t('auth.otpError') || 'Failed to send code. Please try again.'
    
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

// Cleanup
onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
  }
})
</script>

<style lang="scss" scoped>
@import '@/styles/auth-shared';

// OTP-specific styles
.cooldown-text {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.875rem;
  color: var(--text-color-secondary);
  font-weight: 500;
}
</style>
