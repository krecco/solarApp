<template>
  <form @submit.prevent="handleSubmit" class="admin-form">
    <div v-if="showHeader" class="form-header">
      <h2 v-if="title" class="form-title">{{ title }}</h2>
      <p v-if="subtitle" class="form-subtitle">{{ subtitle }}</p>
    </div>
    
    <div class="form-content">
      <slot />
    </div>
    
    <div v-if="showActions" class="form-actions">
      <slot name="actions">
        <Button
          v-if="showCancel"
          :label="cancelLabel"
          severity="secondary"
          outlined
          @click="handleCancel"
          :disabled="loading"
        />
        <Button
          v-if="showSubmit"
          :label="submitLabel"
          :severity="submitSeverity"
          type="submit"
          :loading="loading"
          :disabled="disabled || loading"
        />
      </slot>
    </div>
  </form>
</template>

<script setup lang="ts">
import Button from 'primevue/button'

interface Props {
  title?: string
  subtitle?: string
  showHeader?: boolean
  showActions?: boolean
  showCancel?: boolean
  showSubmit?: boolean
  cancelLabel?: string
  submitLabel?: string
  submitSeverity?: 'primary' | 'success' | 'info' | 'warning' | 'danger'
  loading?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showHeader: true,
  showActions: true,
  showCancel: true,
  showSubmit: true,
  cancelLabel: 'Cancel',
  submitLabel: 'Save',
  submitSeverity: 'primary',
  loading: false,
  disabled: false
})

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

const handleSubmit = () => {
  if (!props.loading && !props.disabled) {
    emit('submit')
  }
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<style scoped lang="scss">
.admin-form {
  .form-header {
    margin-bottom: 2rem;
    
    .form-title {
      margin: 0 0 0.5rem 0;
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-color);
    }
    
    .form-subtitle {
      margin: 0;
      color: var(--text-color-secondary);
    }
  }
  
  .form-content {
    margin-bottom: 2rem;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--surface-border);
  }
}

// Form field utilities
:deep(.field) {
  margin-bottom: 1.5rem;
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
    
    &.required::after {
      content: ' *';
      color: var(--red-500);
    }
  }
  
  .p-inputtext,
  .p-dropdown,
  .p-calendar,
  .p-inputnumber,
  .p-inputtextarea {
    width: 100%;
  }
  
  small {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-color-secondary);
  }
  
  .p-error {
    display: block;
    margin-top: 0.25rem;
    color: var(--red-500);
    font-size: 0.875rem;
  }
}

:deep(.field-group) {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

:deep(.field-checkbox) {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  
  .p-checkbox {
    flex-shrink: 0;
  }
  
  label {
    margin: 0;
    cursor: pointer;
  }
}

@media (max-width: 640px) {
  .admin-form {
    .form-actions {
      flex-direction: column-reverse;
      
      button {
        width: 100%;
      }
    }
  }
  
  :deep(.field-group) {
    grid-template-columns: 1fr;
  }
}
</style>
