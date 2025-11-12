<template>
  <div class="form-field" :class="fieldClass">
    <label v-if="label" :for="fieldId" class="form-field__label">
      {{ label }}
      <span v-if="required" class="form-field__required">*</span>
    </label>
    
    <div class="form-field__content">
      <slot :id="fieldId" :error="error" :modelValue="modelValue" />
    </div>
    
    <small v-if="hint && !error" class="form-field__hint">
      {{ hint }}
    </small>
    
    <small v-if="error" class="form-field__error">
      <i class="pi pi-exclamation-circle"></i>
      {{ error }}
    </small>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue';

/**
 * FormField Component Props
 */
interface FormFieldProps {
  label?: string;
  fieldId?: string;
  required?: boolean;
  error?: string;
  hint?: string;
  modelValue?: any;
  disabled?: boolean;
  readonly?: boolean;
}

const props = withDefaults(defineProps<FormFieldProps>(), {
  label: '',
  fieldId: undefined,
  required: false,
  error: '',
  hint: '',
  modelValue: undefined,
  disabled: false,
  readonly: false
});

const slots = useSlots();

/**
 * Generate field ID if not provided
 */
const fieldId = computed(() => {
  if (props.fieldId) return props.fieldId;
  if (props.label) {
    return `field-${props.label.toLowerCase().replace(/\s+/g, '-')}`;
  }
  return `field-${Math.random().toString(36).substr(2, 9)}`;
});

/**
 * Compute field classes
 */
const fieldClass = computed(() => ({
  'form-field--error': !!props.error,
  'form-field--disabled': props.disabled,
  'form-field--readonly': props.readonly,
  'form-field--required': props.required
}));
</script>

<style scoped lang="scss">
.form-field {
  margin-bottom: 1.5rem;

  &__label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.875rem;
  }

  &__required {
    color: var(--red-500);
    margin-left: 0.25rem;
  }

  &__content {
    position: relative;
  }

  &__hint {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-color-secondary);
    font-size: 0.75rem;
  }

  &__error {
    display: block;
    margin-top: 0.25rem;
    color: var(--red-500);
    font-size: 0.75rem;

    i {
      margin-right: 0.25rem;
    }
  }

  &--error {
    :deep(.p-inputtext),
    :deep(.p-dropdown),
    :deep(.p-multiselect),
    :deep(.p-calendar),
    :deep(.p-inputnumber-input),
    :deep(.p-inputtextarea) {
      border-color: var(--red-500);

      &:enabled:focus {
        border-color: var(--red-500);
        box-shadow: 0 0 0 0.2rem rgba(var(--red-500-rgb), 0.2);
      }
    }
  }

  &--disabled {
    opacity: 0.6;
    pointer-events: none;
  }

  &--readonly {
    :deep(.p-inputtext),
    :deep(.p-dropdown),
    :deep(.p-multiselect),
    :deep(.p-calendar),
    :deep(.p-inputnumber-input),
    :deep(.p-inputtextarea) {
      background-color: var(--surface-100);
    }
  }
}
</style>
