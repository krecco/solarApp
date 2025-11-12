<template>
  <FormField
    :label="label"
    :field-id="fieldId"
    :required="required"
    :error="errorMessage"
    :hint="hint"
    :disabled="disabled"
    :readonly="readonly"
  >
    <template #default="{ id }">
      <InputText
        :id="id"
        v-model="value"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClass"
        :maxlength="maxlength"
        :autocomplete="autocomplete"
        @blur="handleBlur"
        @focus="handleFocus"
        @input="handleInput"
      />
      <i v-if="icon" :class="iconClass" />
    </template>
  </FormField>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import InputText from 'primevue/inputtext';
import FormField from './FormField.vue';

/**
 * FormInput Component Props
 */
interface FormInputProps {
  modelValue?: string | number;
  label?: string;
  fieldId?: string;
  type?: 'text' | 'email' | 'password' | 'tel' | 'url' | 'number' | 'search';
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  error?: string;
  hint?: string;
  icon?: string;
  iconPosition?: 'left' | 'right';
  maxlength?: number;
  autocomplete?: string;
  validator?: (value: any) => string | true;
  validateOnBlur?: boolean;
  validateOnInput?: boolean;
  size?: 'small' | 'normal' | 'large';
}

const props = withDefaults(defineProps<FormInputProps>(), {
  modelValue: '',
  label: '',
  fieldId: undefined,
  type: 'text',
  placeholder: '',
  required: false,
  disabled: false,
  readonly: false,
  error: '',
  hint: '',
  icon: '',
  iconPosition: 'left',
  maxlength: undefined,
  autocomplete: 'off',
  validator: undefined,
  validateOnBlur: true,
  validateOnInput: false,
  size: 'normal'
});

const emit = defineEmits<{
  'update:modelValue': [value: string | number];
  'blur': [event: FocusEvent];
  'focus': [event: FocusEvent];
  'input': [event: Event];
  'validation-error': [error: string];
}>();

/**
 * Local state
 */
const value = ref(props.modelValue);
const localError = ref('');
const isFocused = ref(false);

/**
 * Computed error message (props error takes precedence)
 */
const errorMessage = computed(() => props.error || localError.value);

/**
 * Input classes
 */
const inputClass = computed(() => ({
  'p-inputtext-sm': props.size === 'small',
  'p-inputtext-lg': props.size === 'large',
  'p-invalid': !!errorMessage.value,
  'p-input-icon-left': props.icon && props.iconPosition === 'left',
  'p-input-icon-right': props.icon && props.iconPosition === 'right'
}));

/**
 * Icon classes
 */
const iconClass = computed(() => {
  const classes = ['form-input__icon', props.icon];
  if (props.iconPosition === 'left') {
    classes.push('form-input__icon--left');
  } else {
    classes.push('form-input__icon--right');
  }
  return classes;
});

/**
 * Watch for external value changes
 */
watch(() => props.modelValue, (newValue) => {
  value.value = newValue;
});

/**
 * Watch for internal value changes
 */
watch(value, (newValue) => {
  emit('update:modelValue', newValue);
});

/**
 * Validate input
 */
const validate = () => {
  if (props.validator) {
    const result = props.validator(value.value);
    if (result !== true) {
      localError.value = result;
      emit('validation-error', result);
      return false;
    }
  }
  localError.value = '';
  return true;
};

/**
 * Handle blur event
 */
const handleBlur = (event: FocusEvent) => {
  isFocused.value = false;
  if (props.validateOnBlur) {
    validate();
  }
  emit('blur', event);
};

/**
 * Handle focus event
 */
const handleFocus = (event: FocusEvent) => {
  isFocused.value = true;
  localError.value = ''; // Clear error on focus
  emit('focus', event);
};

/**
 * Handle input event
 */
const handleInput = (event: Event) => {
  if (props.validateOnInput) {
    validate();
  }
  emit('input', event);
};

/**
 * Expose validate method
 */
defineExpose({
  validate,
  value
});
</script>

<style scoped lang="scss">
.form-input {
  &__icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-color-secondary);
    pointer-events: none;

    &--left {
      left: 0.75rem;
    }

    &--right {
      right: 0.75rem;
    }
  }
}

// Adjust padding for icons
:deep(.p-input-icon-left .p-inputtext) {
  padding-left: 2.5rem;
}

:deep(.p-input-icon-right .p-inputtext) {
  padding-right: 2.5rem;
}

:deep(.p-inputtext) {
  width: 100%;
}
</style>
