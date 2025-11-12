<template>
  <div class="auth-form-wrapper animate-fade-in">
    <!-- Logo & Title -->
    <div class="auth-header">
      <div class="auth-logo hover-scale">
        <i class="pi pi-bolt"></i>
      </div>
      <h2 class="auth-title">{{ $t('auth.loginTitle') || 'Welcome Back' }}</h2>
      <p class="auth-subtitle">{{ $t('auth.welcomeBack') || 'Sign in to continue to your dashboard' }}</p>
    </div>

    <!-- Login Form -->
    <form @submit.prevent="handleLogin" class="auth-form">
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
            :placeholder="$t('auth.emailPlaceholder') || 'Enter your email'"
            :class="{ 'p-invalid': errors.email }"
          />
        </div>
        <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
      </div>

      <!-- Password Field -->
      <div class="form-group animate-slide-up delay-200">
        <label for="password" class="form-label">{{ $t('auth.password') || 'Password' }}</label>
        <div class="input-wrapper">
          <i class="pi pi-lock input-icon"></i>
          <Password
            id="password"
            v-model="formData.password"
            required
            class="form-input with-icon"
            :placeholder="$t('auth.passwordPlaceholder') || '••••••••'"
            :feedback="false"
            toggleMask
            :class="{ 'p-invalid': errors.password }"
          />
        </div>
        <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
      </div>

      <!-- Remember Me & Forgot Password -->
      <div class="form-options animate-slide-up delay-300">
        <div class="custom-checkbox">
          <Checkbox
            v-model="formData.rememberMe"
            inputId="rememberMe"
            binary
          />
          <label for="rememberMe" class="checkbox-label">{{ $t('auth.rememberMe') || 'Remember me' }}</label>
        </div>
        <router-link to="/auth/forgot-password" class="forgot-link hover-glow">
          {{ $t('auth.forgotPassword') || 'Forgot password?' }}
        </router-link>
      </div>

      <!-- Error Message -->
      <Message v-if="errorMessage" severity="error" :closable="false" class="animate-fade-in">
        {{ errorMessage }}
      </Message>

      <!-- Login Button -->
      <Button
        type="submit"
        :label="$t('auth.signIn') || 'Sign In'"
        :loading="loading"
        class="auth-button hover-lift shadow-hover animate-slide-up delay-400"
        size="large"
      />

      <!-- Footer Links Section -->
      <div class="footer-links-section">
        <router-link to="/auth/otp-request" class="otp-button animate-fade-in delay-500">
          <i class="pi pi-shield"></i>
          <span>{{ $t('auth.useOtp') || 'Use One-Time Password' }}</span>
        </router-link>
      </div>
    </form>

    <!-- Demo Credentials -->
    <Message v-if="showDemoCredentials" severity="info" class="demo-credentials glass-card-light animate-fade-in delay-700">
      <div class="demo-content">
        <strong>{{ $t('auth.demoCredentials') }}:</strong>
        <div>{{ $t('auth.demoCredentialsEmail') }}: admin@saascentral.com</div>
        <div>{{ $t('auth.demoCredentialsPassword') }}: admin123456</div>
      </div>
    </Message>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
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
  email: 'admin@saascentral.com',
  password: 'admin123456',
  rememberMe: false
})

// State
const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({
  email: '',
  password: ''
})
const showDemoCredentials = ref(true)

// Validation
const validateForm = () => {
  let isValid = true
  errors.email = ''
  errors.password = ''

  if (!formData.email) {
    errors.email = t('auth.emailRequired') || 'Email is required'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
    errors.email = t('auth.emailInvalid') || 'Please enter a valid email'
    isValid = false
  }

  if (!formData.password) {
    errors.password = t('auth.passwordRequired') || 'Password is required'
    isValid = false
  } else if (formData.password.length < 6) {
    errors.password = t('auth.passwordLength') || 'Password must be at least 6 characters'
    isValid = false
  }

  return isValid
}

// Handle login
const handleLogin = async () => {
  errorMessage.value = ''

  if (!validateForm()) {
    return
  }

  loading.value = true

  try {
    await authStore.login({
      email: formData.email,
      password: formData.password,
      rememberMe: formData.rememberMe
    })

    toast.add({
      severity: 'success',
      summary: t('auth.loginSuccess') || 'Welcome back!',
      detail: t('auth.loginSuccessDetail') || 'You have successfully logged in',
      life: 3000
    })

    // Redirect to appropriate dashboard based on role
    const redirectTo = router.currentRoute.value.query.redirect as string
    if (redirectTo) {
      router.push(redirectTo)
    } else if (authStore.hasRole('admin')) {
      // Admin users go to admin dashboard
      router.push('/admin/dashboard')
    } else if (authStore.hasRole('manager')) {
      // Manager users go to manager dashboard
      router.push('/manager/dashboard')
    } else {
      // Regular users go to user dashboard
      router.push('/user/dashboard')
    }
  } catch (error: any) {
    errorMessage.value = error.message || t('auth.loginError') || 'Invalid email or password'

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

// Auto-fill demo credentials for testing
onMounted(() => {
  if (process.env.NODE_ENV === 'development') {
    // Optionally auto-fill for demo
    // formData.email = 'admin@example.com'
    // formData.password = 'Admin123!'
  }
})
</script>

<style lang="scss" scoped>
@import '@/styles/auth-shared';

// Additional login-specific styles
.auth-form {
  &.shake {
    animation: shake 0.5s;
  }
}

.demo-credentials {
  margin-top: 2rem;
  border-radius: 12px;
  overflow: hidden;

  :deep(.p-message-wrapper) {
    padding: 1.25rem;
    background: transparent;
    border: none;
  }

  .demo-content {
    font-size: 0.875rem;
    line-height: 1.6;

    strong {
      display: block;
      margin-bottom: 0.75rem;
      color: var(--text-color);
      font-size: 0.95rem;
    }

    div {
      color: var(--text-color-secondary);
      font-family: 'Monaco', 'Courier New', monospace;
      font-size: 0.813rem;
      margin-bottom: 0.25rem;
    }
  }
}

// Footer Links Section
.footer-links-section {
  margin-top: 2rem;
  text-align: center;

  .otp-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-color-secondary);
    background: transparent;
    border: 1px solid var(--surface-border);
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.3s ease;

    i {
      font-size: 1rem;
    }

    &:hover {
      color: var(--primary-color);
      border-color: var(--primary-color);
      background: rgba(var(--primary-color-rgb), 0.05);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(var(--primary-color-rgb), 0.15);
    }
  }
}

// Responsive adjustments
@media (max-width: 480px) {
  .demo-credentials {
    margin-top: 1.5rem;

    .demo-content {
      font-size: 0.813rem;

      div {
        font-size: 0.75rem;
      }
    }
  }

  .footer-links-section {
    .otp-button {
      padding: 0.5rem 1rem;
      font-size: 0.813rem;
    }
  }
}
</style>
