<template>
  <div class="social-auth-buttons">
    <!-- Divider -->
    <div v-if="showDivider" class="flex align-items-center my-4">
      <div class="flex-1 surface-border" style="height: 1px"></div>
      <span class="px-3 text-color-secondary font-medium">{{ dividerText }}</span>
      <div class="flex-1 surface-border" style="height: 1px"></div>
    </div>

    <!-- Social Login Buttons -->
    <div class="flex flex-column gap-3">
      <!-- Google Sign In -->
      <Button
        :label="googleButtonLabel"
        icon="pi pi-google"
        severity="secondary"
        outlined
        class="w-full justify-content-center social-button google-button"
        :loading="isLoading && loadingProvider === 'google'"
        :disabled="isLoading"
        @click="handleSocialLogin('google')"
      >
        <template #icon>
          <svg class="w-1rem h-1rem mr-2" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
        </template>
      </Button>

      <!-- Future providers can be added here -->
      <!-- Facebook, GitHub, Apple, etc. -->
    </div>

    <!-- Error message -->
    <InlineMessage v-if="errorMessage" severity="error" class="w-full mt-3">
      {{ errorMessage }}
    </InlineMessage>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import InlineMessage from 'primevue/inlinemessage'

interface Props {
  intent?: 'login' | 'register' | 'link'
  showDivider?: boolean
  dividerText?: string
  redirectUrl?: string
}

const props = withDefaults(defineProps<Props>(), {
  intent: 'login',
  showDivider: true,
  dividerText: 'Or continue with',
  redirectUrl: ''
})

const authStore = useAuthStore()

const isLoading = ref(false)
const loadingProvider = ref<string | null>(null)
const errorMessage = ref('')

const googleButtonLabel = computed(() => {
  if (props.intent === 'register') {
    return 'Sign up with Google'
  } else if (props.intent === 'link') {
    return 'Connect Google Account'
  }
  return 'Sign in with Google'
})

const handleSocialLogin = async (provider: string) => {
  isLoading.value = true
  loadingProvider.value = provider
  errorMessage.value = ''

  try {
    // Generate a random state parameter for CSRF protection
    const state = generateRandomState()
    sessionStorage.setItem(
      `${import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'}oauth_state`,
      state
    )

    // Build OAuth URL
    const params = new URLSearchParams({
      intent: props.intent,
      state: state
    })

    if (props.redirectUrl) {
      params.append('redirect_url', props.redirectUrl)
    }

    // Construct the OAuth initiation URL
    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    const oauthUrl = `${baseUrl}/api/v1/auth/${provider}?${params.toString()}`

    // Redirect to OAuth provider
    window.location.href = oauthUrl
  } catch (error: any) {
    console.error('Social login error:', error)
    errorMessage.value = error.message || 'Failed to initiate social login'
    isLoading.value = false
    loadingProvider.value = null
  }
}

const generateRandomState = (): string => {
  const array = new Uint8Array(32)
  crypto.getRandomValues(array)
  return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('')
}
</script>

<style scoped lang="scss">
.social-auth-buttons {
  .social-button {
    height: 3rem;
    font-weight: 500;
    transition: all 0.2s ease;
    
    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    &.google-button {
      border-color: #E0E0E0;
      
      &:hover {
        border-color: #4285F4;
        background: rgba(66, 133, 244, 0.04);
      }
    }
  }
}

// Ensure SVG icon displays properly
:deep(.p-button-icon-left svg) {
  display: block;
}
</style>
