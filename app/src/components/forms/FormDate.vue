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
      <Calendar
        :id="id"
        v-model="value"
        :dateFormat="dateFormat"
        :placeholder="placeholder || 'Select a date'"
        :disabled="disabled"
        :readonly="readonly"
        :showIcon="showIcon"
        :showButtonBar="showButtonBar"
        :showTime="showTime"
        :timeOnly="timeOnly"
        :hourFormat="hourFormat"
        :stepHour="stepHour"
        :stepMinute="stepMinute"
        :stepSecond="stepSecond"
        :minDate="minDate"
        :maxDate="maxDate"
        :disabledDates="disabledDates"
        :disabledDays="disabledDays"
        :inline="inline"
        :selectionMode="selectionMode"
        :numberOfMonths="numberOfMonths"
        :view="view"
        :touchUI="touchUI"
        :monthNavigator="monthNavigator"
        :yearNavigator="yearNavigator"
        :yearRange="yearRange"
        :manualInput="manualInput"
        :class="calendarClass"
        @date-select="handleDateSelect"
        @blur="handleBlur"
        @focus="handleFocus"
        @clear-click="handleClear"
      >
        <template v-if="$slots.header" #header>
          <slot name="header" />
        </template>
        <template v-if="$slots.footer" #footer>
          <slot name="footer" />
        </template>
        <template v-if="$slots.date" #date="slotProps">
          <slot name="date" v-bind="slotProps" />
        </template>
      </Calendar>
    </template>
  </FormField>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Calendar from 'primevue/calendar';
import FormField from './FormField.vue';

/**
 * FormDate Component Props
 */
interface FormDateProps {
  modelValue?: Date | Date[] | null;
  label?: string;
  fieldId?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  error?: string;
  hint?: string;
  dateFormat?: string;
  showIcon?: boolean;
  showButtonBar?: boolean;
  showTime?: boolean;
  timeOnly?: boolean;
  hourFormat?: '12' | '24';
  stepHour?: number;
  stepMinute?: number;
  stepSecond?: number;
  minDate?: Date;
  maxDate?: Date;
  disabledDates?: Date[];
  disabledDays?: number[];
  inline?: boolean;
  selectionMode?: 'single' | 'multiple' | 'range';
  numberOfMonths?: number;
  view?: 'date' | 'month' | 'year';
  touchUI?: boolean;
  monthNavigator?: boolean;
  yearNavigator?: boolean;
  yearRange?: string;
  manualInput?: boolean;
  validator?: (value: any) => string | true;
  validateOnBlur?: boolean;
  validateOnSelect?: boolean;
  size?: 'small' | 'normal' | 'large';
}

const props = withDefaults(defineProps<FormDateProps>(), {
  modelValue: null,
  label: '',
  fieldId: undefined,
  placeholder: '',
  required: false,
  disabled: false,
  readonly: false,
  error: '',
  hint: '',
  dateFormat: 'mm/dd/yy',
  showIcon: true,
  showButtonBar: false,
  showTime: false,
  timeOnly: false,
  hourFormat: '24',
  stepHour: 1,
  stepMinute: 1,
  stepSecond: 1,
  minDate: undefined,
  maxDate: undefined,
  disabledDates: undefined,
  disabledDays: undefined,
  inline: false,
  selectionMode: 'single',
  numberOfMonths: 1,
  view: 'date',
  touchUI: false,
  monthNavigator: false,
  yearNavigator: false,
  yearRange: undefined,
  manualInput: true,
  validator: undefined,
  validateOnBlur: true,
  validateOnSelect: false,
  size: 'normal'
});

const emit = defineEmits<{
  'update:modelValue': [value: Date | Date[] | null];
  'date-select': [value: Date | Date[]];
  'blur': [event: FocusEvent];
  'focus': [event: FocusEvent];
  'clear': [];
  'validation-error': [error: string];
}>();

/**
 * Local state
 */
const value = ref<Date | Date[] | null>(props.modelValue);
const localError = ref('');

/**
 * Computed error message (props error takes precedence)
 */
const errorMessage = computed(() => props.error || localError.value);

/**
 * Calendar classes
 */
const calendarClass = computed(() => ({
  'p-calendar-sm': props.size === 'small',
  'p-calendar-lg': props.size === 'large',
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
 * Validate date
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
  if (props.required && !value.value) {
    localError.value = 'This field is required';
    emit('validation-error', localError.value);
    return false;
  }
  
  // Min date validation
  if (props.minDate && value.value) {
    const dateValue = Array.isArray(value.value) ? value.value[0] : value.value;
    if (dateValue < props.minDate) {
      localError.value = `Date must be after ${props.minDate.toLocaleDateString()}`;
      emit('validation-error', localError.value);
      return false;
    }
  }
  
  // Max date validation
  if (props.maxDate && value.value) {
    const dateValue = Array.isArray(value.value) ? value.value[0] : value.value;
    if (dateValue > props.maxDate) {
      localError.value = `Date must be before ${props.maxDate.toLocaleDateString()}`;
      emit('validation-error', localError.value);
      return false;
    }
  }
  
  localError.value = '';
  return true;
};

/**
 * Handle date select event
 */
const handleDateSelect = (date: Date | Date[]) => {
  if (props.validateOnSelect) {
    validate();
  }
  emit('date-select', date);
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
 * Handle clear event
 */
const handleClear = () => {
  value.value = null;
  localError.value = '';
  emit('clear');
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
:deep(.p-calendar) {
  width: 100%;
  
  .p-inputtext {
    width: 100%;
  }
}

:deep(.p-calendar-w-btn) {
  .p-inputtext {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
  
  .p-datepicker-trigger {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
}
</style>
