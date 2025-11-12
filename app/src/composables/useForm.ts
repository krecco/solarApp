/**
 * Form Composable
 * Handles form validation, submission, and error management
 */

import { ref, computed, reactive, Ref } from 'vue';
import { validateForm, ValidationSchema, ValidationErrors, getFirstError } from '@/utils/validation';

/**
 * Form State Interface
 */
export interface FormState<T = any> {
  data: T;
  errors: ValidationErrors | null;
  isSubmitting: boolean;
  isValidating: boolean;
  isDirty: boolean;
  isValid: boolean;
  touched: Set<string>;
  submitCount: number;
}

/**
 * Form Options Interface
 */
export interface FormOptions<T = any> {
  initialValues?: T;
  validationSchema?: ValidationSchema;
  validateOnChange?: boolean;
  validateOnBlur?: boolean;
  validateOnSubmit?: boolean;
  onSubmit?: (values: T) => Promise<void> | void;
  onError?: (error: any) => void;
  onSuccess?: (result: any) => void;
  resetOnSubmit?: boolean;
}

/**
 * Field Registration Interface
 */
export interface FieldRegistration {
  name: string;
  ref: Ref<any>;
  validate: () => boolean;
  touch: () => void;
  reset: () => void;
}

/**
 * Form Composable
 */
export function useForm<T extends Record<string, any> = Record<string, any>>(
  options: FormOptions<T> = {}
) {
  const {
    initialValues = {} as T,
    validationSchema,
    validateOnChange = false,
    validateOnBlur = true,
    validateOnSubmit = true,
    onSubmit,
    onError,
    onSuccess,
    resetOnSubmit = false
  } = options;

  /**
   * Form state
   */
  const state = reactive<FormState<T>>({
    data: { ...initialValues },
    errors: null,
    isSubmitting: false,
    isValidating: false,
    isDirty: false,
    isValid: true,
    touched: new Set(),
    submitCount: 0
  });

  /**
   * Registered fields
   */
  const fields = new Map<string, FieldRegistration>();

  /**
   * Initial values snapshot
   */
  const initialSnapshot = { ...initialValues };

  /**
   * Get field value
   */
  const getFieldValue = (name: string): any => {
    const keys = name.split('.');
    let value: any = state.data;
    for (const key of keys) {
      value = value?.[key];
    }
    return value;
  };

  /**
   * Set field value
   */
  const setFieldValue = (name: string, value: any) => {
    const keys = name.split('.');
    const lastKey = keys.pop()!;
    let target: any = state.data;
    
    for (const key of keys) {
      if (!target[key]) {
        target[key] = {};
      }
      target = target[key];
    }
    
    target[lastKey] = value;
    state.isDirty = true;
    
    if (validateOnChange) {
      validateField(name);
    }
  };

  /**
   * Get field error
   */
  const getFieldError = (name: string): string | null => {
    if (!state.errors) return null;
    
    const keys = name.split('.');
    let error: any = state.errors;
    
    for (const key of keys) {
      error = error?.[key];
      if (!error) return null;
    }
    
    return typeof error === 'string' ? error : null;
  };

  /**
   * Set field error
   */
  const setFieldError = (name: string, error: string | null) => {
    if (!state.errors && !error) return;
    
    if (!state.errors) {
      state.errors = {};
    }
    
    const keys = name.split('.');
    const lastKey = keys.pop()!;
    let target: any = state.errors;
    
    for (const key of keys) {
      if (!target[key]) {
        target[key] = {};
      }
      target = target[key];
    }
    
    if (error) {
      target[lastKey] = error;
      state.isValid = false;
    } else {
      delete target[lastKey];
      // Check if form is now valid
      if (!getFirstError(state.errors)) {
        state.errors = null;
        state.isValid = true;
      }
    }
  };

  /**
   * Touch field
   */
  const touchField = (name: string) => {
    state.touched.add(name);
    if (validateOnBlur) {
      validateField(name);
    }
  };

  /**
   * Is field touched
   */
  const isFieldTouched = (name: string): boolean => {
    return state.touched.has(name);
  };

  /**
   * Validate single field
   */
  const validateField = async (name: string): Promise<boolean> => {
    if (!validationSchema) return true;
    
    const fieldSchema = validationSchema[name];
    if (!fieldSchema) return true;
    
    state.isValidating = true;
    
    try {
      const value = getFieldValue(name);
      
      if (Array.isArray(fieldSchema)) {
        // Field-level validation
        for (const validator of fieldSchema) {
          const result = await validator(value);
          if (result !== true) {
            setFieldError(name, result);
            return false;
          }
        }
        setFieldError(name, null);
        return true;
      } else if (typeof fieldSchema === 'object') {
        // Nested object validation
        const nestedErrors = validateForm(value || {}, fieldSchema);
        if (nestedErrors) {
          setFieldError(name, nestedErrors as any);
          return false;
        }
        setFieldError(name, null);
        return true;
      }
      
      return true;
    } finally {
      state.isValidating = false;
    }
  };

  /**
   * Validate all fields
   */
  const validate = async (): Promise<boolean> => {
    if (!validationSchema) return true;
    
    state.isValidating = true;
    
    try {
      const errors = validateForm(state.data, validationSchema);
      state.errors = errors;
      state.isValid = !errors;
      return !errors;
    } finally {
      state.isValidating = false;
    }
  };

  /**
   * Register field
   */
  const registerField = (registration: FieldRegistration) => {
    fields.set(registration.name, registration);
    
    return () => {
      fields.delete(registration.name);
    };
  };

  /**
   * Handle form submission
   */
  const handleSubmit = async (event?: Event) => {
    if (event) {
      event.preventDefault();
    }
    
    state.submitCount++;
    
    // Validate if needed
    if (validateOnSubmit) {
      const isValid = await validate();
      if (!isValid) {
        const firstError = getFirstError(state.errors!);
        if (onError) {
          onError(new Error(firstError || 'Validation failed'));
        }
        return;
      }
    }
    
    state.isSubmitting = true;
    
    try {
      if (onSubmit) {
        const result = await onSubmit(state.data);
        if (onSuccess) {
          onSuccess(result);
        }
        if (resetOnSubmit) {
          reset();
        }
      }
    } catch (error) {
      if (onError) {
        onError(error);
      } else {
        console.error('Form submission error:', error);
      }
    } finally {
      state.isSubmitting = false;
    }
  };

  /**
   * Reset form
   */
  const reset = (values?: Partial<T>) => {
    state.data = { ...(values || initialSnapshot) };
    state.errors = null;
    state.isSubmitting = false;
    state.isValidating = false;
    state.isDirty = false;
    state.isValid = true;
    state.touched.clear();
    state.submitCount = 0;
    
    // Reset all registered fields
    fields.forEach(field => {
      if (field.reset) {
        field.reset();
      }
    });
  };

  /**
   * Set form errors
   */
  const setErrors = (errors: ValidationErrors) => {
    state.errors = errors;
    state.isValid = false;
  };

  /**
   * Clear errors
   */
  const clearErrors = () => {
    state.errors = null;
    state.isValid = true;
  };

  /**
   * Set form values
   */
  const setValues = (values: Partial<T>) => {
    Object.assign(state.data, values);
    state.isDirty = true;
  };

  /**
   * Computed properties
   */
  const hasErrors = computed(() => !!state.errors);
  const errorMessage = computed(() => state.errors ? getFirstError(state.errors) : null);
  const canSubmit = computed(() => !state.isSubmitting && state.isValid && state.isDirty);

  return {
    // State
    values: state.data,
    errors: computed(() => state.errors),
    isSubmitting: computed(() => state.isSubmitting),
    isValidating: computed(() => state.isValidating),
    isDirty: computed(() => state.isDirty),
    isValid: computed(() => state.isValid),
    touched: computed(() => state.touched),
    submitCount: computed(() => state.submitCount),
    
    // Computed
    hasErrors,
    errorMessage,
    canSubmit,
    
    // Methods
    getFieldValue,
    setFieldValue,
    getFieldError,
    setFieldError,
    touchField,
    isFieldTouched,
    validateField,
    validate,
    registerField,
    handleSubmit,
    reset,
    setErrors,
    clearErrors,
    setValues
  };
}

/**
 * Field Composable
 */
export function useField(
  name: string,
  form: ReturnType<typeof useForm>,
  options: {
    defaultValue?: any;
    validator?: (value: any) => string | true;
  } = {}
) {
  const { defaultValue, validator } = options;
  
  const value = computed({
    get: () => form.getFieldValue(name) ?? defaultValue,
    set: (val) => form.setFieldValue(name, val)
  });
  
  const error = computed(() => form.getFieldError(name));
  const touched = computed(() => form.isFieldTouched(name));
  
  const validate = () => {
    if (validator) {
      const result = validator(value.value);
      if (result !== true) {
        form.setFieldError(name, result);
        return false;
      }
    }
    return form.validateField(name);
  };
  
  const touch = () => form.touchField(name);
  
  const reset = () => {
    form.setFieldValue(name, defaultValue);
    form.setFieldError(name, null);
  };
  
  // Register field
  const fieldRef = ref(null);
  const unregister = form.registerField({
    name,
    ref: fieldRef,
    validate,
    touch,
    reset
  });
  
  return {
    value,
    error,
    touched,
    validate,
    touch,
    reset,
    unregister
  };
}

export default {
  useForm,
  useField
};
