<template>
  <footer class="app-footer">
    <div class="footer-content">
      <div class="footer-left">
        <span class="footer-text">
          {{ currentYear }} © {{ $t('footer.copyright') }}
        </span>
        <span class="footer-separator">•</span>
        <span class="footer-version">v{{ appVersion }}</span>
      </div>
      
      <div class="footer-center">
        <a
          v-for="link in footerLinks"
          :key="link.label"
          :href="link.href"
          :target="link.external ? '_blank' : '_self'"
          class="footer-link"
        >
          {{ $t(link.label) }}
        </a>
      </div>
      
      <div class="footer-right">
        <Button
          v-for="social in socialLinks"
          :key="social.name"
          v-tooltip.top="$t('social.' + social.key)"
          :icon="social.icon"
          class="footer-social"
          text
          rounded
          size="small"
          @click="openSocial(social.url)"
        />
        
        <Divider layout="vertical" class="footer-divider" />
        
        <div class="footer-status">
          <i class="pi pi-circle-fill status-indicator" :class="statusClass"></i>
          <span class="status-text">{{ $t('footer.' + systemStatus) }}</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const { t } = useI18n()

// State
const systemStatus = ref<'operational' | 'degraded' | 'maintenance'>('operational')
const appVersion = ref('2.0.0')
const currentYear = ref(new Date().getFullYear())

// Footer links
const footerLinks = [
  {
    label: 'footer.privacy',
    href: '/privacy',
    external: false
  },
  {
    label: 'footer.terms',
    href: '/terms',
    external: false
  },
  {
    label: 'footer.documentation',
    href: 'https://docs.example.com',
    external: true
  },
  {
    label: 'footer.support',
    href: '/support',
    external: false
  }
]

// Social links
const socialLinks = [
  {
    name: 'GitHub',
    key: 'github',
    icon: 'pi pi-github',
    url: 'https://github.com'
  },
  {
    name: 'Twitter',
    key: 'twitter',
    icon: 'pi pi-twitter',
    url: 'https://twitter.com'
  },
  {
    name: 'LinkedIn',
    key: 'linkedin',
    icon: 'pi pi-linkedin',
    url: 'https://linkedin.com'
  }
]

// Computed
const statusClass = computed(() => {
  switch (systemStatus.value) {
    case 'operational':
      return 'status-operational'
    case 'degraded':
      return 'status-degraded'
    case 'maintenance':
      return 'status-maintenance'
    default:
      return 'status-operational'
  }
})

// Methods
const openSocial = (url: string) => {
  window.open(url, '_blank')
}

// Check system status (mock implementation)
const checkSystemStatus = async () => {
  // In a real app, this would call an API endpoint
  // For now, we'll just set it to operational
  systemStatus.value = 'operational'
}

// Lifecycle
onMounted(() => {
  checkSystemStatus()
  // Check status every 5 minutes
  const interval = setInterval(checkSystemStatus, 5 * 60 * 1000)
  
  onUnmounted(() => {
    clearInterval(interval)
  })
})
</script>

<style lang="scss" scoped>
.app-footer {
  background: var(--surface-section);
  border-top: 1px solid var(--surface-border);
  padding: 1rem 1.5rem;
  margin-top: auto;
  
  .footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    
    @media (max-width: 768px) {
      flex-direction: column;
      text-align: center;
    }
  }
  
  .footer-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-color-secondary);
    font-size: 0.875rem;
    
    .footer-separator {
      opacity: 0.5;
    }
    
    .footer-version {
      font-family: var(--font-mono);
      opacity: 0.7;
    }
  }
  
  .footer-center {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    
    @media (max-width: 768px) {
      flex-wrap: wrap;
      justify-content: center;
    }
    
    .footer-link {
      color: var(--text-color-secondary);
      text-decoration: none;
      font-size: 0.875rem;
      transition: color 0.2s;
      
      &:hover {
        color: var(--primary-color);
      }
    }
  }
  
  .footer-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    
    .footer-social {
      :deep(.p-button-icon) {
        font-size: 0.875rem;
      }
      
      &:hover {
        color: var(--primary-color);
      }
    }
    
    .footer-divider {
      height: 1rem;
      margin: 0 0.5rem;
      opacity: 0.3;
    }
    
    .footer-status {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      
      .status-indicator {
        font-size: 0.5rem;
        animation: pulse 2s infinite;
        
        &.status-operational {
          color: #10b981;
        }
        
        &.status-degraded {
          color: #f59e0b;
        }
        
        &.status-maintenance {
          color: #ef4444;
        }
      }
      
      .status-text {
        font-size: 0.875rem;
        color: var(--text-color-secondary);
      }
    }
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
