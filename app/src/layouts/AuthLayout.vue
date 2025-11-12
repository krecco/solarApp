<template>
  <div class="auth-layout">
    <!-- Background with gradient and patterns -->
    <div class="auth-background">
      <div class="gradient-overlay"></div>
      <div class="pattern-overlay"></div>
      <div class="floating-shapes">
        <div v-for="i in 6" :key="i" :class="`shape shape-${i}`"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="auth-container">
      <!-- Left Panel - Branding & Features -->
      <div class="auth-panel auth-panel-left">
        <div class="brand-content">
          <!-- Logo -->
          <div class="logo-container">
            <div class="logo">
              <i class="pi pi-bolt text-5xl"></i>
            </div>
            <h1 class="brand-name">{{ t('common.appName') }}</h1>
          </div>

          <!-- Feature List -->
          <div class="features-list">
            <h2 class="features-title">{{ t('auth.welcomeTitle') }}</h2>
            <p class="features-subtitle">{{ t('auth.welcomeSubtitle') }}</p>
            
            <div class="feature-items">
              <div class="feature-item" v-for="feature in features" :key="feature.icon">
                <div class="feature-icon">
                  <i :class="`pi pi-${feature.icon}`"></i>
                </div>
                <div class="feature-content">
                  <h3>{{ feature.title }}</h3>
                  <p>{{ feature.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="stats-container">
            <div class="stat-item" v-for="stat in stats" :key="stat.label">
              <div class="stat-value">{{ stat.value }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
          </div>

          <!-- Footer -->
          <div class="panel-footer">
            <p>{{ t('auth.copyright') }}</p>
          </div>
        </div>
      </div>

      <!-- Right Panel - Auth Forms -->
      <div class="auth-panel auth-panel-right">
        <div class="auth-form-container">


          <!-- Form Content (Router View) -->
          <div class="auth-form-content">
            <transition name="fade-slide" mode="out-in">
              <RouterView />
            </transition>
          </div>

          <!-- Language Selector -->
          <div class="auth-footer">
            <Dropdown
              v-model="selectedLanguage"
              :options="languages"
              optionLabel="name"
              optionValue="code"
              placeholder="Select Language"
              class="language-selector"
              @change="changeLanguage"
            >
              <template #value="slotProps">
                <div class="language-item" v-if="slotProps.value">
                  <span :class="`fi fi-${getLanguageFlag(slotProps.value)}`"></span>
                  <span>{{ getLanguageName(slotProps.value) }}</span>
                </div>
              </template>
              <template #option="slotProps">
                <div class="language-item">
                  <span :class="`fi fi-${slotProps.option.flag}`"></span>
                  <span>{{ slotProps.option.name }}</span>
                </div>
              </template>
            </Dropdown>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'

import Dropdown from 'primevue/dropdown'
import { useThemeStore } from '@/stores/theme'
import { useLocale } from '@/composables/useLocale'
import type { Locale } from '@/locales/types'

// Store
const themeStore = useThemeStore()

// Composables
const { t, locale, changeLocale } = useLocale()



// Data
const selectedLanguage = ref(locale.value)

const features = computed(() => [
  {
    icon: 'chart-line',
    title: t('auth.features.analytics.title'),
    description: t('auth.features.analytics.description')
  },
  {
    icon: 'shield',
    title: t('auth.features.security.title'),
    description: t('auth.features.security.description')
  },
  {
    icon: 'bolt',
    title: t('auth.features.performance.title'),
    description: t('auth.features.performance.description')
  },
  {
    icon: 'palette',
    title: t('auth.features.customizable.title'),
    description: t('auth.features.customizable.description')
  }
])

const stats = computed(() => [
  { value: t('auth.statsValues.activeUsers'), label: t('auth.stats.activeUsers') },
  { value: t('auth.statsValues.uptime'), label: t('auth.stats.uptime') },
  { value: t('auth.statsValues.support'), label: t('auth.stats.support') },
  { value: t('auth.statsValues.responseTime'), label: t('auth.stats.responseTime') }
])

const languages = ref([
  { code: 'en', name: 'English', flag: 'gb' },
  { code: 'es', name: 'Español', flag: 'es' },
  { code: 'fr', name: 'Français', flag: 'fr' },
  { code: 'de', name: 'Deutsch', flag: 'de' }
])

// Methods

const changeLanguage = async () => {
  try {
    await changeLocale(selectedLanguage.value as Locale)
  } catch (error) {
    console.error('Failed to change language:', error)
  }
}

const getLanguageName = (code: string) => {
  const lang = languages.value.find(l => l.code === code)
  return lang ? lang.name : code
}

const getLanguageFlag = (code: string) => {
  const lang = languages.value.find(l => l.code === code)
  return lang ? lang.flag : 'gb'
}

// Lifecycle
onMounted(() => {
  // Add animation class to body
  document.body.classList.add('auth-page')
  
  // Initialize particles or other effects
  initBackgroundEffects()
})

onUnmounted(() => {
  // Clean up
  document.body.classList.remove('auth-page')
})

// Background effects
const initBackgroundEffects = () => {
  // You can add particles.js or other background effects here
}
</script>

<style lang="scss" scoped>
@use '@/styles/abstracts' as *;

.auth-layout {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

// Background
.auth-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
  
  .gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, 
      var(--gradient-from, var(--primary-400)) 0%, 
      var(--gradient-via, var(--primary-500)) 50%, 
      var(--gradient-to, var(--primary-600)) 100%);
    opacity: 0.9;
  }
  
  .pattern-overlay {
    position: absolute;
    inset: 0;
    background-image: 
      radial-gradient(circle at 20% 80%, transparent 50%, rgba(255, 255, 255, 0.1) 50.5%),
      radial-gradient(circle at 80% 20%, transparent 50%, rgba(255, 255, 255, 0.1) 50.5%),
      radial-gradient(circle at 40% 40%, transparent 50%, rgba(255, 255, 255, 0.05) 50.5%);
    background-size: 50px 50px, 50px 50px, 100px 100px;
  }
  
  .floating-shapes {
    position: absolute;
    inset: 0;
    overflow: hidden;
    
    .shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 20s infinite ease-in-out;
      
      &.shape-1 {
        width: 80px;
        height: 80px;
        left: 10%;
        top: 20%;
        animation-delay: 0s;
      }
      
      &.shape-2 {
        width: 120px;
        height: 120px;
        right: 10%;
        top: 60%;
        animation-delay: 2s;
      }
      
      &.shape-3 {
        width: 60px;
        height: 60px;
        left: 70%;
        bottom: 20%;
        animation-delay: 4s;
      }
      
      &.shape-4 {
        width: 100px;
        height: 100px;
        left: 30%;
        bottom: 30%;
        animation-delay: 6s;
      }
      
      &.shape-5 {
        width: 150px;
        height: 150px;
        right: 30%;
        top: 30%;
        animation-delay: 8s;
      }
      
      &.shape-6 {
        width: 40px;
        height: 40px;
        left: 50%;
        top: 50%;
        animation-delay: 10s;
      }
    }
  }
}

// Container
.auth-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1400px;
  height: 90vh;
  max-height: 900px;
  margin: 0 auto;
  padding: 2rem;
  display: flex;
  gap: 2rem;
  
  @include breakpoint(md) {
    padding: 3rem;
  }
}

// Panels
.auth-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  
  &-left {
    display: none;
    
    @include breakpoint(lg) {
      display: flex;
    }
    
    .brand-content {
      color: white;
      padding: 3rem;
      
      .logo-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 3rem;
        
        .logo {
          width: 60px;
          height: 60px;
          background: rgba(255, 255, 255, 0.2);
          @include glassmorphism(0.2, 10px);
          border-radius: 1rem;
          @include flex-center;
        }
        
        .brand-name {
          font-size: 2rem;
          font-weight: 700;
          margin: 0;
        }
      }
      
      .features-list {
        margin-bottom: 3rem;
        
        .features-title {
          font-size: 2.5rem;
          font-weight: 700;
          margin-bottom: 0.5rem;
          line-height: 1.2;
        }
        
        .features-subtitle {
          font-size: 1.125rem;
          opacity: 0.9;
          margin-bottom: 2rem;
        }
        
        .feature-items {
          display: flex;
          flex-direction: column;
          gap: 1.5rem;
          
          .feature-item {
            display: flex;
            gap: 1rem;
            
            .feature-icon {
              width: 48px;
              height: 48px;
              background: rgba(255, 255, 255, 0.1);
              @include glassmorphism(0.1, 5px);
              border-radius: 0.75rem;
              @include flex-center;
              flex-shrink: 0;
              
              i {
                font-size: 1.25rem;
              }
            }
            
            .feature-content {
              h3 {
                font-size: 1.125rem;
                font-weight: 600;
                margin: 0 0 0.25rem 0;
              }
              
              p {
                font-size: 0.875rem;
                opacity: 0.8;
                margin: 0;
              }
            }
          }
        }
      }
      
      .stats-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.1);
        @include glassmorphism(0.1, 5px);
        border-radius: 1rem;
        margin-bottom: 2rem;
        
        .stat-item {
          text-align: center;
          
          .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
          }
          
          .stat-label {
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
          }
        }
      }
      
      .panel-footer {
        text-align: center;
        opacity: 0.7;
        font-size: 0.875rem;
      }
    }
  }
  
  &-right {
    .auth-form-container {
      width: 100%;
      max-width: 480px;
      background: var(--surface-card);
      @include glassmorphism(0.95, 20px);
      border-radius: 1.5rem;
      padding: 2rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      
      @include breakpoint(md) {
        padding: 3rem;
      }

      
      .auth-form-content {
        min-height: 400px;
      }
      
      .auth-footer {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        
        .language-selector {
          width: 200px;
          
          .language-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            
            .fi {
              width: 20px;
              height: 15px;
            }
          }
        }
      }
    }
  }
}

// Dark mode styles
:global(.dark) {
  .auth-panel-right {
    .auth-form-container {
      background: var(--surface-card);
      @include glassmorphism-dark(0.95, 20px);
    }
  }
}

// Animations
@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  33% {
    transform: translateY(-20px) rotate(120deg);
  }
  66% {
    transform: translateY(20px) rotate(240deg);
  }
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

// Mobile styles
@media (max-width: 768px) {
  .auth-container {
    height: 100vh;
    max-height: none;
    padding: 1rem;
  }
  
  .auth-panel-right {
    .auth-form-container {
      max-width: 100%;
      height: 100%;
      border-radius: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
  }
}
</style>