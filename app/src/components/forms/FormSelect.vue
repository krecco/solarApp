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
      <Dropdown
        :id="id"
        v-model="value"
        :options="computedOptions"
        :optionLabel="optionLabel"
        :optionValue="optionValue"
        :optionDisabled="optionDisabled"
        :optionGroupLabel="optionGroupLabel"
        :optionGroupChildren="optionGroupChildren"
        :placeholder="placeholder || 'Select an option'"
        :disabled="disabled"
        :loading="loading"
        :filter="filter"
        :filterPlaceholder="filterPlaceholder"
        :filterMatchMode="filterMatchMode"
        :showClear="showClear && !required"
        :editable="editable"
        :class="dropdownClass"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <template v-if="$slots.value" #value="slotProps">
          <slot name="value" v-bind="slotProps" />
        </template>
        <template v-if="$slots.option" #option="slotProps">
          <slot name="option" v-bind="slotProps" />
        </template>
        <template v-if="$slots.optiongroup" #optiongroup="slotProps">
          <slot name="optiongroup" v-bind="slotProps" />
        </template>
        <template v-if="$slots.empty" #empty>
          <slot name="empty" />
        </template>
        <template v-if="$slots.emptyfilter" #emptyfilter>
          <slot name="emptyfilter" />
        </template>
      </Dropdown>
    </template>
  </FormField>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Dropdown from 'primevue/dropdown';
import FormField from './FormField.vue';

/**
 * FormSelect Component Props
 */
interface FormSelectProps {
  modelValue?: any;
  options: any[];
  label?: string;
  fieldId?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  error?: string;
  hint?: string;
  loading?: boolean;
  optionLabel?: string | ((option: any) => string);
  optionValue?: string | ((option: any) => any);
  optionDisabled?: string | ((option: any) => boolean);
  optionGroupLabel?: string;
  optionGroupChildren?: string;
  filter?: boolean;
  filterPlaceholder?: string;
  filterMatchMode?: 'contains' | 'startsWith' | 'endsWith';
  showClear?: boolean;
  editable?: boolean;
  validator?: (value: any) => string | true;
  validateOnBlur?: boolean;
  validateOnChange?: boolean;
  size?: 'small' | 'normal' | 'large';
}

const props = withDefaults(defineProps<FormSelectProps>(), {
  modelValue: null,
  label: '',
  fieldId: undefined,
  placeholder: '',
  required: false,
  disabled: false,
  readonly: false,
  error: '',
  hint: '',
  loading: false,
  optionLabel: 'label',
  optionValue: 'value',
  optionDisabled: 'disabled',
  optionGroupLabel: '',
  optionGroupChildren: 'items',
  filter: false,
  filterPlaceholder: 'Search...',
  filterMatchMode: 'contains',
  showClear: true,
  editable: false,
  validator: undefined,
  validateOnBlur: true,
  validateOnChange: false,
  size: 'normal'
});

const emit = defineEmits<{
  'update:modelValue': [value: any];
  'change': [event: any];
  'blur': [event: FocusEvent];
  'focus': [event: FocusEvent];
  'validation-error': [error: string];
}>();

/**
 * Local state
 */
const value = ref(props.modelValue);
const localError = ref('');

/**
 * Computed error message (props error takes precedence)
 */
const errorMessage = computed(() => props.error || localError.value);

/**
 * Computed options (handle both array and promise)
 */
const computedOptions = computed(() => {
  if (!props.options) return [];
  
  // If options is a simple array of strings/numbers, convert to objects
  if (props.options.length > 0 && typeof props.options[0] !== 'object') {
    return props.options.map(opt => ({
      label: String(opt),
      value: opt
    }));
  }
  
  return props.options;
});

/**
 * Dropdown classes
 */
const dropdownClass = computed(() => ({
  'p-dropdown-sm': props.size === 'small',
  'p-dropdown-lg': props.size === 'large',
  'p-invalid': !!errorMessage.value,
  'w-full': true
}));

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
 * Validate selection
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
  
  // Required validation
  if (props.required && (value.value === null || value.value === undefined || value.value === '')) {
    localError.value = 'This field is required';
    emit('validation-error', localError.value);
    return false;
  }
  
  localError.value = '';
  return true;
};

/**
 * Handle change event
 */
const handleChange = (event: any) => {
  if (props.validateOnChange) {
    validate();
  }
  emit('change', event);
};

/**
 * Handle blur event
 */
const handleBlur = (event: FocusEvent) => {
  if (props.validateOnBlur) {
    validate();
  }
  emit('blur', event);
};

/**
 * Handle focus event
 */
const handleFocus = (event: FocusEvent) => {
  localError.value = ''; // Clear error on focus
  emit('focus', event);
};

/**
 * Expose validate method and value
 */
defineExpose({
  validate,
  value
});
</script>

<style scoped lang="scss">
:deep(.p-dropdown) {
  width: 100%;
}

:deep(.p-dropdown-label) {
  width: 100%;
}
</style>
