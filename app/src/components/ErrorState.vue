<template>
  <div class="error-state" :class="errorClass">
    <div class="error-state__content">
      <i :class="iconClass" />
      
      <h3 v-if="title" class="error-state__title">{{ title }}</h3>
      
      <div class="error-state__message">
        <slot>{{ message }}</slot>
      </div>
      
      <div v-if="details" class="error-state__details">
        <InlineMessage severity="error" :closable="false">
          <pre>{{ details }}</pre>
        </InlineMessage>
      </div>
      
      <div v-if="showActions" class="error-state__actions">
        <Button
          v-if="showRetry"
          :label="retryLabel"
          :icon="retryIcon"
          severity="primary"
          @click="handleRetry"
        />
        
        <Button
          v-if="showGoBack"
          :label="goBackLabel"
          :icon="goBackIcon"
          severity="secondary"
          outlined
          @click="handleGoBack"
        />
        
        <Button
          v-if="showGoHome"
          :label="goHomeLabel"
          :icon="goHomeIcon"
          severity="secondary"
          text
          @click="handleGoHome"
        />
        
        <slot name="actions" />
      </div>
      
      <div v-if="showSupport" class="error-state__support">
        <p>
          {{ supportText }}
          <a v-if="supportEmail" :href="`mailto:${supportEmail}`">{{ supportEmail }}</a>
        </p>
      </div>
      
      <div v-if="errorCode" class="error-state__code">
        Error Code: <Tag :value="errorCode" severity="danger" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import InlineMessage from 'primevue/inlinemessage';
import Tag from 'primevue/tag';

/**
 * ErrorState Component Props
 */
interface ErrorStateProps {
  type?: '404' | '403' | '401' | '500' | 'network' | 'general';
  title?: string;
  message?: string;
  details?: string;
  icon?: string;
  size?: 'small' | 'normal' | 'large';
  showRetry?: boolean;
  retryLabel?: string;
  retryIcon?: string;
  showGoBack?: boolean;
  goBackLabel?: string;
  goBackIcon?: string;
  showGoHome?: boolean;
  goHomeLabel?: string;
  goHomeIcon?: string;
  showSupport?: boolean;
  supportText?: string;
  supportEmail?: string;
  errorCode?: string;
  fullPage?: boolean;
}

const props = withDefaults(defineProps<ErrorStateProps>(), {
  type: 'general',
  title: '',
  message: '',
  details: '',
  icon: '',
  size: 'normal',
  showRetry: false,
  retryLabel: 'Try Again',
  retryIcon: 'pi pi-refresh',
  showGoBack: false,
  goBackLabel: 'Go Back',
  goBackIcon: 'pi pi-arrow-left',
  showGoHome: false,
  goHomeLabel: 'Go to Home',
  goHomeIcon: 'pi pi-home',
  showSupport: false,
  supportText: 'If this problem persists, please contact support:',
  supportEmail: '',
  errorCode: '',
  fullPage: false
});

const emit = defineEmits<{
  retry: [];
  goBack: [];
  goHome: [];
}>();

const router = useRouter();

/**
 * Get default content based on error type
 */
const getDefaultContent = () => {
  switch (props.type) {
    case '404':
      return {
        icon: 'pi pi-search',
        title: 'Page Not Found',
        message: 'The page you are looking for does not exist or has been moved.'
      };
    case '403':
      return {
        icon: 'pi pi-lock',
        title: 'Access Denied',
        message: 'You do not have permission to access this resource.'
      };
    case '401':
      return {
        icon: 'pi pi-user',
        title: 'Authentication Required',
        message: 'Please log in to access this resource.'
      };
    case '500':
      return {
        icon: 'pi pi-exclamation-triangle',
        title: 'Server Error',
        message: 'An unexpected error occurred on our servers. Please try again later.'
      };
    case 'network':
      return {
        icon: 'pi pi-wifi',
        title: 'Network Error',
        message: 'Unable to connect to the server. Please check your internet connection.'
      };
    default:
      return {
        icon: 'pi pi-times-circle',
        title: 'Error',
        message: 'An unexpected error occurred. Please try again.'
      };
  }
};

const defaultContent = getDefaultContent();

/**
 * Computed icon class
 */
const iconClass = computed(() => {
  return props.icon || defaultContent.icon;
});

/**
 * Computed title
 */
const computedTitle = computed(() => {
  return props.title || defaultContent.title;
});

/**
 * Computed message
 */
const computedMessage = computed(() => {
  return props.message || defaultContent.message;
});

/**
 * Computed error class
 */
const errorClass = computed(() => ({
  'error-state--small': props.size === 'small',
  'error-state--large': props.size === 'large',
  'error-state--full-page': props.fullPage,
  [`error-state--${props.type}`]: true
}));

/**
 * Show actions section
 */
const showActions = computed(() => {
  return props.showRetry || props.showGoBack || props.showGoHome;
});

/**
 * Handle retry
 */
const handleRetry = () => {
  emit('retry');
};

/**
 * Handle go back
 */
const handleGoBack = () => {
  emit('goBack');
  router.back();
};

/**
 * Handle go home
 */
const handleGoHome = () => {
  emit('goHome');
  router.push('/');
};
</script>

<style scoped lang="scss">
.error-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  min-height: 400px;

  &__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 500px;
    gap: 1rem;
  }

  &__title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-color);
  }

  &__message {
    color: var(--text-color-secondary);
    line-height: 1.6;
  }

  &__details {
    width: 100%;
    max-width: 600px;
    margin-top: 1rem;

    pre {
      margin: 0;
      font-size: 0.875rem;
      white-space: pre-wrap;
      word-break: break-word;
    }
  }

  &__actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  &__support {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--surface-border);
    color: var(--text-color-secondary);
    font-size: 0.875rem;

    p {
      margin: 0;
    }

    a {
      color: var(--primary-color);
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  }

  &__code {
    margin-top: 1rem;
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  // Icon styling
  i {
    font-size: 3rem;
    opacity: 0.5;
  }

  // Size variants
  &--small {
    padding: 1rem;
    min-height: 200px;

    i {
      font-size: 2rem;
    }

    .error-state__title {
      font-size: 1.25rem;
    }

    .error-state__message {
      font-size: 0.875rem;
    }
  }

  &--large {
    padding: 3rem;
    min-height: 500px;

    i {
      font-size: 4rem;
    }

    .error-state__title {
      font-size: 2rem;
    }

    .error-state__message {
      font-size: 1.125rem;
    }
  }

  // Full page variant
  &--full-page {
    min-height: 100vh;
    padding: 4rem 2rem;
  }

  // Type-specific colors
  &--404 i {
    color: var(--blue-500);
  }

  &--403 i {
    color: var(--orange-500);
  }

  &--401 i {
    color: var(--yellow-500);
  }

  &--500 i {
    color: var(--red-500);
  }

  &--network i {
    color: var(--purple-500);
  }

  &--general i {
    color: var(--red-500);
  }
}

// Responsive
@media (max-width: 768px) {
  .error-state {
    padding: 1rem;

    &__content {
      max-width: 100%;
    }

    &__actions {
      flex-direction: column;
      width: 100%;

      :deep(.p-button) {
        width: 100%;
      }
    }
  }
}
</style>
