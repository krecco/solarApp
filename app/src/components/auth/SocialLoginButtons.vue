<template>
  <div class="flex flex-column gap-2">
    <Button 
      v-for="provider in enabledProviders"
      :key="provider.name"
      @click="handleSocialLogin(provider.name)" 
      :severity="buttonSeverity"
      :outlined="outlined"
      class="w-full"
      :loading="loadingProvider === provider.name"
      :disabled="disabled"
      type="button"
    >
      <i :class="['mr-2', provider.icon]"></i>
      {{ buttonText }} {{ provider.label }}
    </Button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import Button from 'primevue/button'

interface SocialProvider {
  name: string
  label: string
  icon: string
  enabled: boolean
}

interface Props {
  providers?: string[]
  buttonText?: string
  buttonSeverity?: 'secondary' | 'info' | 'success' | 'warning' | 'danger' | 'contrast'
  outlined?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  providers: () => ['google', 'github', 'microsoft'],
  buttonText: 'Continue with',
  buttonSeverity: 'secondary',
  outlined: true,
  disabled: false
})

const emit = defineEmits<{
  'social-login': [provider: string]
}>()

const loadingProvider = ref<string | null>(null)

const allProviders: SocialProvider[] = [
  { name: 'google', label: 'Google', icon: 'pi pi-google', enabled: true },
  { name: 'github', label: 'GitHub', icon: 'pi pi-github', enabled: true },
  { name: 'microsoft', label: 'Microsoft', icon: 'pi pi-microsoft', enabled: true },
  { name: 'facebook', label: 'Facebook', icon: 'pi pi-facebook', enabled: false },
  { name: 'twitter', label: 'Twitter', icon: 'pi pi-twitter', enabled: false },
  { name: 'linkedin', label: 'LinkedIn', icon: 'pi pi-linkedin', enabled: false }
]

const enabledProviders = computed(() => {
  return allProviders.filter(p => 
    props.providers.includes(p.name) && p.enabled
  )
})

const handleSocialLogin = (provider: string) => {
  loadingProvider.value = provider
  emit('social-login', provider)
  
  // Reset loading state after a timeout in case parent doesn't handle it
  setTimeout(() => {
    if (loadingProvider.value === provider) {
      loadingProvider.value = null
    }
  }, 10000)
}

// Expose method to reset loading state
defineExpose({
  resetLoading: () => {
    loadingProvider.value = null
  }
})
</script>
