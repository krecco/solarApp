/**
 * Validation Utilities
 * Common validation schemas and error message formatting
 */

// Email validation regex
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Password validation regex (min 8 chars, 1 uppercase, 1 lowercase, 1 number)
const PASSWORD_REGEX = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;

// Phone number validation regex (basic international format)
const PHONE_REGEX = /^[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,9}$/;

// URL validation regex
const URL_REGEX = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;

// Username validation regex (alphanumeric with underscore and hyphen, 3-20 chars)
const USERNAME_REGEX = /^[a-zA-Z0-9_-]{3,20}$/;

/**
 * Validation Rules
 */
export const validators = {
  /**
   * Check if value is required (not empty)
   */
  required: (value: any, message = 'This field is required'): string | true => {
    if (value === null || value === undefined || value === '') {
      return message;
    }
    if (Array.isArray(value) && value.length === 0) {
      return message;
    }
    if (typeof value === 'string' && value.trim() === '') {
      return message;
    }
    return true;
  },

  /**
   * Validate email format
   */
  email: (value: string, message = 'Invalid email address'): string | true => {
    if (!value) return true; // Let required validator handle empty values
    return EMAIL_REGEX.test(value) || message;
  },

  /**
   * Validate password strength
   */
  password: (value: string, message = 'Password must be at least 8 characters with uppercase, lowercase, and number'): string | true => {
    if (!value) return true;
    return PASSWORD_REGEX.test(value) || message;
  },

  /**
   * Validate minimum length
   */
  minLength: (min: number) => (value: string, message?: string): string | true => {
    if (!value) return true;
    const defaultMessage = `Must be at least ${min} characters`;
    return value.length >= min || message || defaultMessage;
  },

  /**
   * Validate maximum length
   */
  maxLength: (max: number) => (value: string, message?: string): string | true => {
    if (!value) return true;
    const defaultMessage = `Must be no more than ${max} characters`;
    return value.length <= max || message || defaultMessage;
  },

  /**
   * Validate length range
   */
  lengthBetween: (min: number, max: number) => (value: string, message?: string): string | true => {
    if (!value) return true;
    const defaultMessage = `Must be between ${min} and ${max} characters`;
    return (value.length >= min && value.length <= max) || message || defaultMessage;
  },

  /**
   * Validate minimum value (for numbers)
   */
  minValue: (min: number) => (value: number, message?: string): string | true => {
    if (value === null || value === undefined) return true;
    const defaultMessage = `Must be at least ${min}`;
    return value >= min || message || defaultMessage;
  },

  /**
   * Validate maximum value (for numbers)
   */
  maxValue: (max: number) => (value: number, message?: string): string | true => {
    if (value === null || value === undefined) return true;
    const defaultMessage = `Must be no more than ${max}`;
    return value <= max || message || defaultMessage;
  },

  /**
   * Validate value range (for numbers)
   */
  between: (min: number, max: number) => (value: number, message?: string): string | true => {
    if (value === null || value === undefined) return true;
    const defaultMessage = `Must be between ${min} and ${max}`;
    return (value >= min && value <= max) || message || defaultMessage;
  },

  /**
   * Validate phone number
   */
  phone: (value: string, message = 'Invalid phone number'): string | true => {
    if (!value) return true;
    return PHONE_REGEX.test(value) || message;
  },

  /**
   * Validate URL
   */
  url: (value: string, message = 'Invalid URL'): string | true => {
    if (!value) return true;
    return URL_REGEX.test(value) || message;
  },

  /**
   * Validate username
   */
  username: (value: string, message = 'Username must be 3-20 characters, alphanumeric with underscore and hyphen'): string | true => {
    if (!value) return true;
    return USERNAME_REGEX.test(value) || message;
  },

  /**
   * Validate alphanumeric
   */
  alphanumeric: (value: string, message = 'Must contain only letters and numbers'): string | true => {
    if (!value) return true;
    return /^[a-zA-Z0-9]+$/.test(value) || message;
  },

  /**
   * Validate numeric
   */
  numeric: (value: string, message = 'Must be a number'): string | true => {
    if (!value) return true;
    return /^\d+$/.test(value) || message;
  },

  /**
   * Validate decimal
   */
  decimal: (value: string, message = 'Must be a valid decimal number'): string | true => {
    if (!value) return true;
    return /^\d+(\.\d+)?$/.test(value) || message;
  },

  /**
   * Validate date format (YYYY-MM-DD)
   */
  dateFormat: (value: string, message = 'Invalid date format (YYYY-MM-DD)'): string | true => {
    if (!value) return true;
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    if (!regex.test(value)) return message;
    
    const date = new Date(value);
    return !isNaN(date.getTime()) || message;
  },

  /**
   * Validate date is in future
   */
  futureDate: (value: string | Date, message = 'Date must be in the future'): string | true => {
    if (!value) return true;
    const date = typeof value === 'string' ? new Date(value) : value;
    return date > new Date() || message;
  },

  /**
   * Validate date is in past
   */
  pastDate: (value: string | Date, message = 'Date must be in the past'): string | true => {
    if (!value) return true;
    const date = typeof value === 'string' ? new Date(value) : value;
    return date < new Date() || message;
  },

  /**
   * Validate confirmation match (e.g., password confirmation)
   */
  matches: (otherValue: any, fieldName = 'field') => (value: any, message?: string): string | true => {
    if (!value && !otherValue) return true;
    const defaultMessage = `Must match ${fieldName}`;
    return value === otherValue || message || defaultMessage;
  },

  /**
   * Custom regex validation
   */
  pattern: (regex: RegExp, message = 'Invalid format') => (value: string): string | true => {
    if (!value) return true;
    return regex.test(value) || message;
  },

  /**
   * Validate array minimum length
   */
  minItems: (min: number) => (value: any[], message?: string): string | true => {
    if (!value) return true;
    const defaultMessage = `Must have at least ${min} item${min !== 1 ? 's' : ''}`;
    return value.length >= min || message || defaultMessage;
  },

  /**
   * Validate array maximum length
   */
  maxItems: (max: number) => (value: any[], message?: string): string | true => {
    if (!value) return true;
    const defaultMessage = `Must have no more than ${max} item${max !== 1 ? 's' : ''}`;
    return value.length <= max || message || defaultMessage;
  },

  /**
   * Validate file size (in bytes)
   */
  maxFileSize: (maxBytes: number) => (file: File, message?: string): string | true => {
    if (!file) return true;
    const maxMB = (maxBytes / (1024 * 1024)).toFixed(2);
    const defaultMessage = `File size must be less than ${maxMB}MB`;
    return file.size <= maxBytes || message || defaultMessage;
  },

  /**
   * Validate file type
   */
  fileType: (allowedTypes: string[]) => (file: File, message?: string): string | true => {
    if (!file) return true;
    const defaultMessage = `File type must be one of: ${allowedTypes.join(', ')}`;
    return allowedTypes.includes(file.type) || message || defaultMessage;
  }
};

/**
 * Compose multiple validators
 */
export function composeValidators(...validators: Array<(value: any) => string | true>) {
  return (value: any): string | true => {
    for (const validator of validators) {
      const result = validator(value);
      if (result !== true) {
        return result;
      }
    }
    return true;
  };
}

/**
 * Create async validator
 */
export function asyncValidator(
  validator: (value: any) => Promise<boolean>,
  message = 'Validation failed'
) {
  return async (value: any): Promise<string | true> => {
    try {
      const isValid = await validator(value);
      return isValid || message;
    } catch (error) {
      return message;
    }
  };
}

/**
 * Validate entire form object
 */
export interface ValidationSchema {
  [key: string]: Array<(value: any) => string | true> | ValidationSchema;
}

export interface ValidationErrors {
  [key: string]: string | ValidationErrors;
}

export function validateForm(
  formData: Record<string, any>,
  schema: ValidationSchema
): ValidationErrors | null {
  const errors: ValidationErrors = {};
  let hasErrors = false;

  for (const [field, validators] of Object.entries(schema)) {
    const value = formData[field];

    if (Array.isArray(validators)) {
      // Field-level validation
      for (const validator of validators) {
        const result = validator(value);
        if (result !== true) {
          errors[field] = result;
          hasErrors = true;
          break;
        }
      }
    } else if (typeof validators === 'object') {
      // Nested object validation
      const nestedErrors = validateForm(value || {}, validators);
      if (nestedErrors) {
        errors[field] = nestedErrors;
        hasErrors = true;
      }
    }
  }

  return hasErrors ? errors : null;
}

/**
 * Format error messages for display
 */
export function formatErrorMessage(field: string, error: string): string {
  // Capitalize first letter of field name
  const fieldName = field.charAt(0).toUpperCase() + field.slice(1).replace(/([A-Z])/g, ' $1');
  
  // If error already contains the field name, return as is
  if (error.toLowerCase().includes(field.toLowerCase())) {
    return error;
  }
  
  // Otherwise, prepend field name
  return `${fieldName}: ${error}`;
}

/**
 * Get first error from validation errors object
 */
export function getFirstError(errors: ValidationErrors): string | null {
  for (const [field, error] of Object.entries(errors)) {
    if (typeof error === 'string') {
      return formatErrorMessage(field, error);
    } else if (typeof error === 'object') {
      const nestedError = getFirstError(error);
      if (nestedError) {
        return nestedError;
      }
    }
  }
  return null;
}

/**
 * Common validation schemas
 */
export const commonSchemas = {
  loginForm: {
    email: [validators.required, validators.email],
    password: [validators.required, validators.minLength(8)]
  },

  registrationForm: {
    username: [validators.required, validators.username],
    email: [validators.required, validators.email],
    password: [validators.required, validators.password],
    confirmPassword: [
      validators.required,
      (value: string, form?: any) => validators.matches(form?.password, 'password')(value)
    ],
    terms: [
      (value: boolean) => value === true || 'You must accept the terms and conditions'
    ]
  },

  userForm: {
    firstName: [validators.required, validators.minLength(2), validators.maxLength(50)],
    lastName: [validators.required, validators.minLength(2), validators.maxLength(50)],
    email: [validators.required, validators.email],
    phone: [validators.phone],
    role: [validators.required]
  },

  companyForm: {
    name: [validators.required, validators.minLength(2), validators.maxLength(100)],
    website: [validators.url],
    email: [validators.required, validators.email],
    phone: [validators.phone],
    address: {
      street: [validators.required],
      city: [validators.required],
      state: [validators.required],
      zip: [validators.required, validators.pattern(/^\d{5}(-\d{4})?$/, 'Invalid ZIP code')],
      country: [validators.required]
    }
  },

  passwordChangeForm: {
    currentPassword: [validators.required],
    newPassword: [validators.required, validators.password],
    confirmPassword: [
      validators.required,
      (value: string, form?: any) => validators.matches(form?.newPassword, 'new password')(value)
    ]
  }
};

/**
 * Export all utilities
 */
export default {
  validators,
  composeValidators,
  asyncValidator,
  validateForm,
  formatErrorMessage,
  getFirstError,
  commonSchemas
};
