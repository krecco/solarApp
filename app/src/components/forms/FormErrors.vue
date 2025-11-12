<template>
  <div v-if="errors && Object.keys(errors).length > 0" class="form-errors">
    <div class="form-errors__header">
      <i class="pi pi-exclamation-triangle"></i>
      <span>{{ title }}</span>
    </div>
    <ul class="form-errors__list">
      <li v-for="(error, field) in flattenedErrors" :key="field" class="form-errors__item">
        <strong>{{ formatFieldName(field) }}:</strong> {{ error }}
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ValidationErrors } from '@/utils/validation';

/**
 * FormErrors Component Props
 */
interface FormErrorsProps {
  errors?: ValidationErrors | null;
  title?: string;
}

const props = withDefaults(defineProps<FormErrorsProps>(), {
  errors: null,
  title: 'Please correct the following errors:'
});

/**
 * Flatten nested errors object
 */
const flattenedErrors = computed(() => {
  if (!props.errors) return {};
  
  const flattened: Record<string, string> = {};
  
  const flatten = (obj: any, prefix = '') => {
    for (const [key, value] of Object.entries(obj)) {
      const fieldKey = prefix ? `${prefix}.${key}` : key;
      
      if (typeof value === 'string') {
        flattened[fieldKey] = value;
      } else if (typeof value === 'object' && value !== null) {
        flatten(value, fieldKey);
      }
    }
  };
  
  flatten(props.errors);
  return flattened;
});

/**
 * Format field name for display
 */
const formatFieldName = (field: string): string => {
  return field
    .split('.')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
    .replace(/([A-Z])/g, ' $1')
    .trim();
};
</script>

<style scoped lang="scss">
.form-errors {
  background: var(--red-50);
  border: 1px solid var(--red-200);
  border-radius: var(--border-radius);
  padding: 1rem;
  margin-bottom: 1.5rem;

  &__header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: var(--red-700);

    i {
      font-size: 1.25rem;
    }
  }

  &__list {
    margin: 0;
    padding-left: 1.75rem;
    list-style: disc;
  }

  &__item {
    color: var(--red-600);
    margin-bottom: 0.25rem;

    strong {
      color: var(--red-700);
    }

    &:last-child {
      margin-bottom: 0;
    }
  }
}
</style>
