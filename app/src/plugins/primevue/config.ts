/**
 * PrimeVue Configuration
 * Centralized configuration for PrimeVue components and theme
 * 
 * Benefits of this approach:
 * - Separation of concerns (config vs initialization)
 * - Better maintainability and testability
 * - Easier to switch between different configurations
 * - Type-safe configuration with TypeScript
 */

import type { PrimeVueConfiguration } from 'primevue/config'
import LaraGreen from '@/theme/lara-green'
import { getLocale } from './locale'

/**
 * Get PrimeVue configuration
 * Can be customized based on environment or user preferences
 * 
 * @param options - Optional configuration overrides
 * @returns Complete PrimeVue configuration object
 */
export function getPrimeVueConfig(options?: {
  locale?: string
  darkMode?: boolean
  ripple?: boolean
}): PrimeVueConfiguration {
  const { 
    locale = 'en', 
    ripple = true 
  } = options || {}

  return {
    ripple,
    inputStyle: 'outlined',
    theme: {
      preset: LaraGreen,
      options: {
        prefix: 'p',
        darkModeSelector: '.dark',
        cssLayer: {
          name: 'primevue',
          order: 'tailwind-base, primevue, tailwind-utilities'
        }
      }
    },
    locale: getLocale(locale)
  }
}

/**
 * Default PrimeVue configuration
 * Used when no custom options are provided
 */
export const defaultPrimeVueConfig = getPrimeVueConfig()

/**
 * PrimeVue service imports
 * Re-export commonly used services for convenience
 */
export { default as ToastService } from 'primevue/toastservice'
export { default as ConfirmationService } from 'primevue/confirmationservice'
export { default as DialogService } from 'primevue/dialogservice'

/**
 * PrimeVue directive imports
 * Re-export commonly used directives
 */
export { default as Tooltip } from 'primevue/tooltip'
export { default as Ripple } from 'primevue/ripple'
export { default as OverlayBadge } from 'primevue/overlaybadge'
export { default as StyleClass } from 'primevue/styleclass'
export { default as FocusTrap } from 'primevue/focustrap'

export default defaultPrimeVueConfig
