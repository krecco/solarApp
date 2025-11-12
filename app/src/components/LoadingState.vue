<template>
  <div class="loading-state" :class="loadingClass">
    <div v-if="overlay" class="loading-state__overlay" />
    
    <div class="loading-state__content">
      <i :class="iconClass" />
      
      <div v-if="message || $slots.default" class="loading-state__message">
        <slot>{{ message }}</slot>
      </div>
      
      <ProgressBar
        v-if="showProgress"
        :value="progress"
        :mode="progressMode"
        :showValue="showProgressValue"
        class="loading-state__progress"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import ProgressBar from 'primevue/progressbar';

/**
 * LoadingState Component Props
 */
interface LoadingStateProps {
  message?: string;
  icon?: string;
  size?: 'small' | 'normal' | 'large' | 'xlarge';
  overlay?: boolean;
  fullscreen?: boolean;
  inline?: boolean;
  showProgress?: boolean;
  progress?: number;
  progressMode?: 'determinate' | 'indeterminate';
  showProgressValue?: boolean;
  spinnerType?: 'spinner' | 'dots' | 'bars' | 'circle';
}

const props = withDefaults(defineProps<LoadingStateProps>(), {
  message: 'Loading...',
  icon: '',
  size: 'normal',
  overlay: false,
  fullscreen: false,
  inline: false,
  showProgress: false,
  progress: 0,
  progressMode: 'indeterminate',
  showProgressValue: true,
  spinnerType: 'spinner'
});

/**
 * Computed icon class
 */
const iconClass = computed(() => {
  if (props.icon) return props.icon;
  
  switch (props.spinnerType) {
    case 'dots':
      return 'loading-dots';
    case 'bars':
      return 'loading-bars';
    case 'circle':
      return 'loading-circle';
    default:
      return 'pi pi-spin pi-spinner';
  }
});

/**
 * Computed loading class
 */
const loadingClass = computed(() => ({
  'loading-state--small': props.size === 'small',
  'loading-state--large': props.size === 'large',
  'loading-state--xlarge': props.size === 'xlarge',
  'loading-state--overlay': props.overlay,
  'loading-state--fullscreen': props.fullscreen,
  'loading-state--inline': props.inline
}));
</script>

<style scoped lang="scss">
.loading-state {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  min-height: 200px;

  &__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(2px);
    z-index: 1;
  }

  &__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    z-index: 2;
    position: relative;
  }

  &__message {
    color: var(--text-color-secondary);
    text-align: center;
    max-width: 400px;
  }

  &__progress {
    width: 200px;
  }

  // Icon sizes
  i {
    font-size: 2rem;
    color: var(--primary-color);
  }

  // Size variants
  &--small {
    padding: 1rem;
    min-height: 100px;

    i {
      font-size: 1.5rem;
    }

    .loading-state__message {
      font-size: 0.875rem;
    }

    .loading-state__progress {
      width: 150px;
    }
  }

  &--large {
    padding: 3rem;
    min-height: 300px;

    i {
      font-size: 3rem;
    }

    .loading-state__message {
      font-size: 1.125rem;
    }

    .loading-state__progress {
      width: 250px;
    }
  }

  &--xlarge {
    padding: 4rem;
    min-height: 400px;

    i {
      font-size: 4rem;
    }

    .loading-state__message {
      font-size: 1.25rem;
    }

    .loading-state__progress {
      width: 300px;
    }
  }

  // Overlay variant
  &--overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    min-height: auto;
    z-index: 1000;
  }

  // Fullscreen variant
  &--fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    min-height: 100vh;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.95);
  }

  // Inline variant
  &--inline {
    display: inline-flex;
    padding: 0.5rem 1rem;
    min-height: auto;

    .loading-state__content {
      flex-direction: row;
      gap: 0.5rem;
    }

    i {
      font-size: 1rem;
    }

    .loading-state__message {
      font-size: 0.875rem;
    }
  }
}

// Custom loading animations
@keyframes loadingDots {
  0%, 80%, 100% {
    opacity: 0;
  }
  40% {
    opacity: 1;
  }
}

.loading-dots {
  display: inline-flex;
  gap: 0.25rem;

  &::before,
  &::after,
  & {
    content: '';
    width: 0.5rem;
    height: 0.5rem;
    background: var(--primary-color);
    border-radius: 50%;
    animation: loadingDots 1.4s infinite ease-in-out both;
  }

  &::before {
    animation-delay: -0.32s;
  }

  &::after {
    animation-delay: -0.16s;
  }
}

@keyframes loadingBars {
  0%, 80%, 100% {
    transform: scaleY(1);
  }
  40% {
    transform: scaleY(1.5);
  }
}

.loading-bars {
  display: inline-flex;
  gap: 0.25rem;

  &::before,
  &::after,
  & {
    content: '';
    width: 0.25rem;
    height: 1.5rem;
    background: var(--primary-color);
    animation: loadingBars 1s infinite ease-in-out;
  }

  &::before {
    animation-delay: -0.32s;
  }

  &::after {
    animation-delay: -0.16s;
  }
}

@keyframes loadingCircle {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.loading-circle {
  width: 2rem;
  height: 2rem;
  border: 3px solid var(--surface-border);
  border-top-color: var(--primary-color);
  border-radius: 50%;
  animation: loadingCircle 1s linear infinite;
}
</style>
