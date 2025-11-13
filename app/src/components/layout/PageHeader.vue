<template>
  <div class="page-header">
    <div class="header-content">
      <div class="header-left">
        <!-- Back Button -->
        <Button
          v-if="showBack"
          icon="pi pi-arrow-left"
          severity="secondary"
          text
          rounded
          class="back-button"
          @click="handleBack"
        />
        
        <!-- Icon -->
        <div v-if="icon" class="header-icon">
          <i :class="icon"></i>
        </div>
        
        <!-- Text Content -->
        <div class="header-text">
          <h1 class="page-title">{{ displayTitle }}</h1>
          <p v-if="displaySubtitle" class="page-subtitle">{{ displaySubtitle }}</p>
          
          <!-- Breadcrumbs -->
          <Breadcrumb v-if="breadcrumbs && breadcrumbs.length > 0" :model="breadcrumbs" class="header-breadcrumb">
            <template #item="{ item }">
              <router-link v-if="item.route" :to="item.route" class="breadcrumb-link">
                <i v-if="item.icon" :class="item.icon" class="breadcrumb-icon"></i>
                <span>{{ item.label }}</span>
              </router-link>
              <span v-else class="breadcrumb-current">
                <i v-if="item.icon" :class="item.icon" class="breadcrumb-icon"></i>
                <span>{{ item.label }}</span>
              </span>
            </template>
          </Breadcrumb>
        </div>
      </div>
      
      <!-- Action Button (if provided) -->
      <div v-if="buttonLabel || buttonLabelKey" class="header-actions">
        <Button
          :label="displayButtonLabel"
          :icon="buttonIcon"
          @click="$emit('button-click')"
        />
      </div>
      
      <!-- Actions Slot -->
      <div v-if="$slots.actions" class="header-actions">
        <slot name="actions"></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import Breadcrumb from 'primevue/breadcrumb'

/**
 * Props Interface
 */
interface BreadcrumbItem {
  label: string
  route?: string
  icon?: string
}

interface PageHeaderProps {
  // Direct string props (legacy)
  title?: string
  subtitle?: string
  // i18n key props (new)
  titleKey?: string
  subtitleKey?: string
  // Button props
  buttonLabel?: string
  buttonLabelKey?: string
  buttonIcon?: string
  // Other props
  icon?: string
  showBack?: boolean
  breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<PageHeaderProps>(), {
  title: '',
  subtitle: '',
  titleKey: '',
  subtitleKey: '',
  buttonLabel: '',
  buttonLabelKey: '',
  buttonIcon: '',
  icon: '',
  showBack: false,
  breadcrumbs: () => []
})

const emit = defineEmits<{
  'back': []
  'button-click': []
}>()

const router = useRouter()
const { t } = useI18n()

/**
 * Computed properties for dynamic content
 */
const displayTitle = computed(() => {
  if (props.titleKey) {
    return t(props.titleKey)
  }
  return props.title
})

const displaySubtitle = computed(() => {
  if (props.subtitleKey) {
    return t(props.subtitleKey)
  }
  return props.subtitle
})

const displayButtonLabel = computed(() => {
  if (props.buttonLabelKey) {
    return t(props.buttonLabelKey)
  }
  return props.buttonLabel
})

/**
 * Handle back button click
 */
const handleBack = () => {
  emit('back')
  // If no custom handler, use router.back()
  if (!emit) {
    router.back()
  }
}
</script>

<style scoped lang="scss">
.page-header {
  background: var(--surface-card);
  border: 1px solid var(--surface-border);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  
  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    
    @media (max-width: 768px) {
      flex-direction: column;
      align-items: flex-start;
    }
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    
    .back-button {
      flex-shrink: 0;
    }
    
    .header-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--primary-100), var(--primary-200));
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      
      i {
        font-size: 1.5rem;
        color: var(--primary-700);
      }
    }
    
    .header-text {
      flex: 1;
      
      .page-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-color);
        line-height: 1.2;
        
        @media (max-width: 768px) {
          font-size: 1.5rem;
        }
      }
      
      .page-subtitle {
        margin: 0.25rem 0 0 0;
        color: var(--text-color-secondary);
        font-size: 0.875rem;
      }
      
      .header-breadcrumb {
        margin-top: 0.75rem;
        background: transparent;
        border: none;
        padding: 0;
        
        :deep(.p-breadcrumb-list) {
          padding: 0;
        }
        
        .breadcrumb-link {
          color: var(--primary-color);
          text-decoration: none;
          transition: color 0.2s;
          
          &:hover {
            color: var(--primary-700);
          }
          
          .breadcrumb-icon {
            margin-right: 0.25rem;
            font-size: 0.875rem;
          }
        }
        
        .breadcrumb-current {
          color: var(--text-color-secondary);
          
          .breadcrumb-icon {
            margin-right: 0.25rem;
            font-size: 0.875rem;
          }
        }
      }
    }
  }
  
  .header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
    
    @media (max-width: 768px) {
      width: 100%;
      justify-content: flex-end;
    }
  }
}

// Animation
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.page-header {
  animation: fadeInDown 0.3s ease-out;
}
</style>
