<template>
  <div class="enhanced-empty-state" :class="{ 'compact': compact }">
    <div class="empty-state-content">
      <!-- Icon with animation -->
      <div class="empty-icon-wrapper">
        <i :class="icon" class="empty-icon"></i>
      </div>

      <!-- Title -->
      <h3 class="empty-title">{{ title }}</h3>

      <!-- Description -->
      <p v-if="description" class="empty-description">
        {{ description }}
      </p>

      <!-- Action buttons -->
      <div v-if="$slots.actions || actionLabel" class="empty-actions">
        <slot name="actions">
          <Button
            v-if="actionLabel"
            :label="actionLabel"
            :icon="actionIcon"
            @click="$emit('action')"
            :severity="actionSeverity"
            class="action-button"
          />
        </slot>
      </div>

      <!-- Secondary actions or help text -->
      <div v-if="$slots.secondary || helpText" class="empty-secondary">
        <slot name="secondary">
          <p v-if="helpText" class="help-text">
            <i class="pi pi-info-circle"></i>
            {{ helpText }}
          </p>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Button from 'primevue/button'

interface Props {
  icon?: string
  title: string
  description?: string
  actionLabel?: string
  actionIcon?: string
  actionSeverity?: 'primary' | 'success' | 'info' | 'warning' | 'danger'
  helpText?: string
  compact?: boolean
}

withDefaults(defineProps<Props>(), {
  icon: 'pi pi-inbox',
  actionSeverity: 'primary',
  compact: false
})

defineEmits(['action'])
</script>

<style lang="scss" scoped>
.enhanced-empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  min-height: 400px;

  &.compact {
    padding: 2rem 1rem;
    min-height: 200px;

    .empty-icon {
      font-size: 3rem !important;
    }

    .empty-title {
      font-size: 1.125rem !important;
    }
  }

  .empty-state-content {
    text-align: center;
    max-width: 500px;

    .empty-icon-wrapper {
      margin-bottom: 1.5rem;
      animation: fadeInDown 0.6s ease-out;

      .empty-icon {
        font-size: 5rem;
        color: var(--text-color-secondary);
        opacity: 0.4;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
        animation: float 3s ease-in-out infinite;
      }
    }

    .empty-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-color);
      margin: 0 0 0.75rem 0;
      animation: fadeIn 0.6s ease-out 0.1s both;
    }

    .empty-description {
      font-size: 1rem;
      color: var(--text-color-secondary);
      margin: 0 0 2rem 0;
      line-height: 1.6;
      animation: fadeIn 0.6s ease-out 0.2s both;
    }

    .empty-actions {
      display: flex;
      gap: 0.75rem;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 1.5rem;
      animation: fadeInUp 0.6s ease-out 0.3s both;

      .action-button {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;

        &:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
      }
    }

    .empty-secondary {
      animation: fadeIn 0.6s ease-out 0.4s both;

      .help-text {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-color-secondary);
        margin: 0;
        padding: 0.75rem 1rem;
        background: var(--surface-100);
        border-radius: 8px;

        i {
          font-size: 1rem;
          color: var(--primary-color);
        }
      }
    }
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.dark .enhanced-empty-state {
  .empty-secondary {
    .help-text {
      background: var(--surface-700);
    }
  }
}
</style>
