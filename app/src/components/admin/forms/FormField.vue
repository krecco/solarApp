<template>
  <div class="form-field" :class="fieldClasses">
    <label v-if="label" :for="fieldId" :class="{ required: required }">
      {{ label }}
    </label>
    
    <div class="field-content">
      <slot :id="fieldId" />
    </div>
    
    <small v-if="help && !error" class="field-help">
      {{ help }}
    </small>
    
    <small v-if="error" class="field-error">
      <i class="pi pi-exclamation-circle"></i>
      {{ error }}
    </small>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label?: string
  fieldId?: string
  required?: boolean
  error?: string
  help?: string
  inline?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  inline: false
})

const fieldClasses = computed(() => ({
  'field-error-state': !!props.error,
  'field-inline': props.inline
}))

const fieldId = computed(() => {
  return props.fieldId || `field-${Math.random().toString(36).substr(2, 9)}`
})
</script>

<style scoped lang="scss">
.form-field {
  margin-bottom: 1.5rem;
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.875rem;
    
    &.required::after {
      content: ' *';
      color: var(--red-500);
    }
  }
  
  .field-content {
    position: relative;
    
    :deep(.p-inputtext),
    :deep(.p-dropdown),
    :deep(.p-calendar),
    :deep(.p-inputnumber),
    :deep(.p-inputtextarea),
    :deep(.p-multiselect),
    :deep(.p-chips) {
      width: 100%;
    }
  }
  
  .field-help {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-color-secondary);
    font-size: 0.75rem;
  }
  
  .field-error {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;
    color: var(--red-500);
    font-size: 0.75rem;
    
    i {
      font-size: 0.75rem;
    }
  }
  
  &.field-error-state {
    .field-content {
      :deep(.p-inputtext),
      :deep(.p-dropdown),
      :deep(.p-calendar),
      :deep(.p-inputnumber),
      :deep(.p-inputtextarea),
      :deep(.p-multiselect),
      :deep(.p-chips) {
        border-color: var(--red-500);
        
        &:focus {
          box-shadow: 0 0 0 0.2rem rgba(var(--red-500-rgb), 0.2);
        }
      }
    }
  }
  
  &.field-inline {
    display: flex;
    align-items: center;
    gap: 1rem;
    
    label {
      margin-bottom: 0;
      min-width: 150px;
      flex-shrink: 0;
    }
    
    .field-content {
      flex: 1;
    }
    
    .field-help,
    .field-error {
      margin-left: calc(150px + 1rem);
    }
  }
}

@media (max-width: 640px) {
  .form-field {
    &.field-inline {
      flex-direction: column;
      align-items: stretch;
      
      label {
        min-width: auto;
        margin-bottom: 0.5rem;
      }
      
      .field-help,
      .field-error {
        margin-left: 0;
      }
    }
  }
}
</style>
