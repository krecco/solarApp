/**
 * PrimeVue Plugin Module
 * Main entry point for PrimeVue configuration and utilities
 */

export { 
  getPrimeVueConfig, 
  defaultPrimeVueConfig,
  ToastService,
  ConfirmationService,
  DialogService,
  Tooltip,
  Ripple,
  OverlayBadge,
  StyleClass,
  FocusTrap
} from './config'

export { 
  getLocale, 
  enUS,
  type PrimeVueLocale 
} from './locale'

// Setup function for easy integration
import type { App } from 'vue'
import PrimeVue from 'primevue/config'
import { 
  defaultPrimeVueConfig,
  ToastService,
  ConfirmationService,
  DialogService,
  Tooltip,
  Ripple,
  OverlayBadge,
  StyleClass,
  FocusTrap
} from './config'

/**
 * Setup PrimeVue with all services and directives
 * Call this in your main.ts after creating the Vue app
 * 
 * @param app - Vue application instance
 * @param config - Optional custom configuration
 */
export function setupPrimeVue(app: App, config = defaultPrimeVueConfig): void {
  // Configure PrimeVue
  app.use(PrimeVue, config)
  
  // Register services
  app.use(ToastService)
  app.use(ConfirmationService)
  app.use(DialogService)
  
  // Register global directives
  app.directive('tooltip', Tooltip)
  app.directive('ripple', Ripple)
  app.directive('styleclass', StyleClass)
  app.directive('focustrap', FocusTrap)
  
  // Register global components
  app.component('OverlayBadge', OverlayBadge)
}

export default setupPrimeVue
