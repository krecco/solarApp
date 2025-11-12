<template>
  <div class="empty-state" :class="emptyClass">
    <div class="empty-state__content">
      <div v-if="image" class="empty-state__image">
        <img :src="image" :alt="title" />
      </div>
      <i v-else :class="iconClass" />
      
      <h3 v-if="title" class="empty-state__title">{{ title }}</h3>
      
      <div class="empty-state__message">
        <slot>{{ message }}</slot>
      </div>
      
      <div v-if="showSuggestions && suggestions.length > 0" class="empty-state__suggestions">
        <p>{{ suggestionsTitle }}</p>
        <ul>
          <li v-for="(suggestion, index) in suggestions" :key="index">
            {{ suggestion }}
          </li>
        </ul>
      </div>
      
      <div v-if="showActions || $slots.actions" class="empty-state__actions">
        <Button
          v-if="showPrimaryAction"
          :label="primaryActionLabel"
          :icon="primaryActionIcon"
          :severity="primaryActionSeverity"
          @click="handlePrimaryAction"
        />
        
        <Button
          v-if="showSecondaryAction"
          :label="secondaryActionLabel"
          :icon="secondaryActionIcon"
          severity="secondary"
          outlined
          @click="handleSecondaryAction"
        />
        
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Button from 'primevue/button';

/**
 * EmptyState Component Props
 */
interface EmptyStateProps {
  type?: 'no-data' | 'no-results' | 'no-permissions' | 'no-connection' | 'custom';
  title?: string;
  message?: string;
  icon?: string;
  image?: string;
  size?: 'small' | 'normal' | 'large';
  showSuggestions?: boolean;
  suggestionsTitle?: string;
  suggestions?: string[];
  showPrimaryAction?: boolean;
  primaryActionLabel?: string;
  primaryActionIcon?: string;
  primaryActionSeverity?: 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'danger';
  showSecondaryAction?: boolean;
  secondaryActionLabel?: string;
  secondaryActionIcon?: string;
  compact?: boolean;
}

const props = withDefaults(defineProps<EmptyStateProps>(), {
  type: 'no-data',
  title: '',
  message: '',
  icon: '',
  image: '',
  size: 'normal',
  showSuggestions: false,
  suggestionsTitle: 'Try the following:',
  suggestions: () => [],
  showPrimaryAction: false,
  primaryActionLabel: 'Get Started',
  primaryActionIcon: 'pi pi-plus',
  primaryActionSeverity: 'primary',
  showSecondaryAction: false,
  secondaryActionLabel: 'Learn More',
  secondaryActionIcon: 'pi pi-info-circle',
  compact: false
});

const emit = defineEmits<{
  primaryAction: [];
  secondaryAction: [];
}>();

/**
 * Get default content based on empty type
 */
const getDefaultContent = () => {
  switch (props.type) {
    case 'no-data':
      return {
        icon: 'pi pi-inbox',
        title: 'No Data',
        message: 'There are no items to display at the moment.'
      };
    case 'no-results':
      return {
        icon: 'pi pi-search',
        title: 'No Results Found',
        message: 'Try adjusting your search or filter criteria.'
      };
    case 'no-permissions':
      return {
        icon: 'pi pi-lock',
        title: 'No Access',
        message: 'You don\'t have permission to view this content.'
      };
    case 'no-connection':
      return {
        icon: 'pi pi-wifi',
        title: 'No Connection',
        message: 'Unable to load data. Please check your connection.'
      };
    default:
      return {
        icon: 'pi pi-info-circle',
        title: 'Empty',
        message: 'Nothing to display here.'
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
 * Computed empty class
 */
const emptyClass = computed(() => ({
  'empty-state--small': props.size === 'small',
  'empty-state--large': props.size === 'large',
  'empty-state--compact': props.compact,
  [`empty-state--${props.type}`]: true
}));

/**
 * Show actions section
 */
const showActions = computed(() => {
  return props.showPrimaryAction || props.showSecondaryAction;
});

/**
 * Handle primary action
 */
const handlePrimaryAction = () => {
  emit('primaryAction');
};

/**
 * Handle secondary action
 */
const handleSecondaryAction = () => {
  emit('secondaryAction');
};
</script>

<style scoped lang="scss">
.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 2rem;
  min-height: 400px;

  &__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 500px;
    gap: 1rem;
  }

  &__image {
    width: 200px;
    height: 200px;
    margin-bottom: 1rem;

    img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  }

  &__title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color);
  }

  &__message {
    color: var(--text-color-secondary);
    line-height: 1.6;
  }

  &__suggestions {
    margin-top: 1rem;
    text-align: left;
    color: var(--text-color-secondary);
    font-size: 0.875rem;

    p {
      margin: 0 0 0.5rem 0;
      font-weight: 500;
    }

    ul {
      margin: 0;
      padding-left: 1.25rem;

      li {
        margin-bottom: 0.25rem;

        &:last-child {
          margin-bottom: 0;
        }
      }
    }
  }

  &__actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  // Icon styling
  i {
    font-size: 4rem;
    opacity: 0.3;
    color: var(--text-color-secondary);
  }

  // Size variants
  &--small {
    padding: 2rem 1rem;
    min-height: 250px;

    i {
      font-size: 3rem;
    }

    .empty-state__image {
      width: 120px;
      height: 120px;
    }

    .empty-state__title {
      font-size: 1.125rem;
    }

    .empty-state__message {
      font-size: 0.875rem;
    }
  }

  &--large {
    padding: 4rem 2rem;
    min-height: 500px;

    i {
      font-size: 5rem;
    }

    .empty-state__image {
      width: 280px;
      height: 280px;
    }

    .empty-state__title {
      font-size: 1.5rem;
    }

    .empty-state__message {
      font-size: 1.125rem;
    }
  }

  // Compact variant
  &--compact {
    padding: 1.5rem 1rem;
    min-height: auto;

    .empty-state__content {
      gap: 0.5rem;
    }

    i {
      font-size: 2.5rem;
    }

    .empty-state__image {
      width: 100px;
      height: 100px;
      margin-bottom: 0.5rem;
    }

    .empty-state__title {
      font-size: 1rem;
    }

    .empty-state__message {
      font-size: 0.875rem;
    }

    .empty-state__actions {
      margin-top: 1rem;
    }
  }

  // Type-specific colors
  &--no-data i {
    color: var(--blue-400);
  }

  &--no-results i {
    color: var(--purple-400);
  }

  &--no-permissions i {
    color: var(--orange-400);
  }

  &--no-connection i {
    color: var(--red-400);
  }
}

// Responsive
@media (max-width: 768px) {
  .empty-state {
    padding: 2rem 1rem;

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
