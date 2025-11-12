/**
 * Composable for i18n functionality
 * Provides easy access to translation and locale management in Vue components
 */

import { computed, ref } from 'vue'
import { useI18n as vueUseI18n } from 'vue-i18n'
import { setLocale as setAppLocale, getAvailableLocales, formatRelativeTime } from '@/plugins/i18n'
import type { Locale } from '@/locales/types'

/**
 * Enhanced i18n composable with additional features
 */
export function useLocale() {
  const { t, d, n, locale } = vueUseI18n()
  
  const availableLocales = ref(getAvailableLocales())
  const isChangingLocale = ref(false)
  
  // Runtime validation: ensure only intended 4 languages are available
  if (availableLocales.value.length !== 4) {
    console.warn('⚠️ LANGUAGE CONFIGURATION ERROR: Expected exactly 4 languages, got:', availableLocales.value.length)
    console.warn('Available languages:', availableLocales.value.map(l => `${l.code} (${l.name})`))
    console.warn('This might be due to cached data, demo mode, or configuration override.')
  }
  
  /**
   * Current locale info
   */
  const currentLocale = computed(() => {
    return availableLocales.value.find(l => l.code === locale.value)
  })
  
  /**
   * Change locale with loading state
   */
  const changeLocale = async (newLocale: Locale) => {
    if (locale.value === newLocale) return
    
    try {
      isChangingLocale.value = true
      await setAppLocale(newLocale)
    } catch (error) {
      console.error('Failed to change locale:', error)
      throw error
    } finally {
      isChangingLocale.value = false
    }
  }
  
  /**
   * Format currency based on current locale
   */
  const formatCurrency = (amount: number, currency?: string) => {
    const curr = currency || currentLocale.value?.currency || 'USD'
    return new Intl.NumberFormat(locale.value, {
      style: 'currency',
      currency: curr
    }).format(amount)
  }
  
  /**
   * Format percentage
   */
  const formatPercent = (value: number, decimals = 0) => {
    return new Intl.NumberFormat(locale.value, {
      style: 'percent',
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(value / 100)
  }
  
  /**
   * Format large numbers with abbreviations (1.2K, 3.4M, etc.)
   */
  const formatCompactNumber = (num: number) => {
    return new Intl.NumberFormat(locale.value, {
      notation: 'compact',
      maximumFractionDigits: 1
    }).format(num)
  }
  
  /**
   * Get locale-specific date format pattern
   */
  const getDateFormat = () => {
    return currentLocale.value?.dateFormat || 'MM/DD/YYYY'
  }
  
  /**
   * Format date with various options
   */
  const formatDate = (date: Date | string | number, format: 'short' | 'long' | 'custom' = 'short', customOptions?: Intl.DateTimeFormatOptions) => {
    const dateObj = new Date(date)
    
    if (format === 'custom' && customOptions) {
      return new Intl.DateTimeFormat(locale.value, customOptions).format(dateObj)
    }
    
    return d(dateObj, format)
  }
  
  /**
   * Check if current locale is RTL
   */
  const isRTL = computed(() => {
    return currentLocale.value?.direction === 'rtl'
  })
  
  return {
    // Core i18n functions
    t,
    d,
    n,
    
    // Locale management
    locale: computed(() => locale.value as Locale),
    currentLocale,
    availableLocales,
    changeLocale,
    isChangingLocale,
    isRTL,
    
    // Formatting utilities
    formatCurrency,
    formatPercent,
    formatCompactNumber,
    formatDate,
    formatRelativeTime,
    getDateFormat
  }
}

/**
 * Composable for translation with common patterns
 */
export function useTranslation() {
  const { t } = vueUseI18n()
  
  /**
   * Get validation message with parameters
   */
  const validationMessage = (key: string, params?: Record<string, any>) => {
    return t(`validation.${key}`, params)
  }
  
  /**
   * Get success message
   */
  const successMessage = (key: string) => {
    return t(`messages.success.${key}`)
  }
  
  /**
   * Get error message
   */
  const errorMessage = (key: string) => {
    return t(`messages.error.${key}`)
  }
  
  /**
   * Get confirmation message
   */
  const confirmMessage = (key: string) => {
    return t(`messages.confirm.${key}`)
  }
  
  /**
   * Pluralization helper
   */
  const plural = (key: string, count: number, params?: Record<string, any>) => {
    return t(key, { count, ...params })
  }
  
  return {
    t,
    validationMessage,
    successMessage,
    errorMessage,
    confirmMessage,
    plural
  }
}
