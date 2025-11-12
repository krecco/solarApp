/**
 * Vue i18n configuration
 * Using separated language files with lazy loading for better performance
 */

import { createI18n, type I18n } from 'vue-i18n'
import type { MessageSchema, Locale } from '@/locales/types'
import { 
  loadLocaleMessages, 
  detectPreferredLocale, 
  availableLocales,
  localeInfo,
  defaultLocale 
} from '@/locales'

// Create i18n instance with lazy loading
let i18nInstance: I18n<MessageSchema, {}, {}, Locale, false> | null = null

/**
 * Initialize i18n with the preferred locale
 */
export async function setupI18n(): Promise<I18n<MessageSchema, {}, {}, Locale, false>> {
  const locale = detectPreferredLocale()
  
  // Load initial locale messages
  const messages = await loadLocaleMessages(locale)
  
  // Create i18n instance
  i18nInstance = createI18n<MessageSchema, Locale>({
    legacy: false,
    locale,
    fallbackLocale: defaultLocale,
    messages: {
      [locale]: messages
    },
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false,
    // Date & number formatting
    datetimeFormats: {
      en: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          weekday: 'long',
          hour: 'numeric',
          minute: 'numeric'
        }
      },
      es: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          weekday: 'long',
          hour: 'numeric',
          minute: 'numeric'
        }
      },
      fr: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          weekday: 'long',
          hour: 'numeric',
          minute: 'numeric'
        }
      },
      de: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          weekday: 'long',
          hour: 'numeric',
          minute: 'numeric'
        }
      }
    },
    numberFormats: {
      en: {
        currency: {
          style: 'currency',
          currency: 'USD',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      es: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      fr: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      de: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      }
    }
  })
  
  // Set document language
  document.documentElement.lang = locale
  
  return i18nInstance
}

/**
 * Load and set a new locale
 */
export async function setLocale(newLocale: Locale): Promise<void> {
  if (!i18nInstance) {
    console.error('i18n not initialized')
    return
  }
  
  const { global } = i18nInstance
  
  // Check if locale is already loaded
  if (!global.availableLocales.includes(newLocale)) {
    // Load the new locale messages
    const messages = await loadLocaleMessages(newLocale)
    global.setLocaleMessage(newLocale, messages)
  }
  
  // Set the locale
  global.locale.value = newLocale
  
  // Save to localStorage
  localStorage.setItem('locale', newLocale)
  
  // Update document language
  document.documentElement.lang = newLocale
  
  // Update document direction if needed (for RTL languages)
  const info = localeInfo[newLocale]
  if (info) {
    document.documentElement.dir = info.direction
  }
}

/**
 * Get current locale
 */
export function getLocale(): Locale {
  if (!i18nInstance) {
    return defaultLocale
  }
  return i18nInstance.global.locale.value as Locale
}

/**
 * Check if a locale is loaded
 */
export function isLocaleLoaded(locale: Locale): boolean {
  if (!i18nInstance) return false
  return i18nInstance.global.availableLocales.includes(locale)
}

/**
 * Get all available locales with their metadata
 */
export function getAvailableLocales() {
  return availableLocales.map(code => ({
    code,
    ...localeInfo[code]
  }))
}

/**
 * Format a relative time (e.g., "2 hours ago")
 */
export function formatRelativeTime(date: Date | string | number): string {
  const rtf = new Intl.RelativeTimeFormat(getLocale(), { numeric: 'auto' })
  const now = new Date()
  const then = new Date(date)
  const diff = (then.getTime() - now.getTime()) / 1000 // diff in seconds
  
  const units: { unit: Intl.RelativeTimeFormatUnit; seconds: number }[] = [
    { unit: 'year', seconds: 31536000 },
    { unit: 'month', seconds: 2628000 },
    { unit: 'week', seconds: 604800 },
    { unit: 'day', seconds: 86400 },
    { unit: 'hour', seconds: 3600 },
    { unit: 'minute', seconds: 60 },
    { unit: 'second', seconds: 1 }
  ]
  
  for (const { unit, seconds } of units) {
    const interval = Math.floor(Math.abs(diff) / seconds)
    if (interval >= 1) {
      return rtf.format(diff < 0 ? -interval : interval, unit)
    }
  }
  
  return rtf.format(0, 'second')
}

// Export the i18n instance (will be null until setupI18n is called)
export const i18n = new Proxy({} as I18n<MessageSchema, {}, {}, Locale, false>, {
  get(_target, prop) {
    if (!i18nInstance) {
      console.error('i18n not initialized. Call setupI18n() first.')
      return undefined
    }
    return i18nInstance[prop as keyof typeof i18nInstance]
  }
})

// Export helper functions for use in components
export function useI18n() {
  if (!i18nInstance) {
    throw new Error('i18n not initialized. Call setupI18n() first.')
  }
  
  const { global } = i18nInstance
  
  return {
    t: global.t,
    tm: global.tm,
    rt: global.rt,
    te: global.te,
    d: global.d,
    n: global.n,
    locale: global.locale,
    setLocale,
    getLocale,
    availableLocales: getAvailableLocales(),
    formatRelativeTime
  }
}

// Re-export types
export type { MessageSchema, Locale, LocaleInfo } from '@/locales/types'
