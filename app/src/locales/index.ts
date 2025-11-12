/**
 * Locale exports
 * This file aggregates all language modules for easier import
 */

import type { MessageSchema, Locale, LocaleInfo } from './types'

// Language modules - using dynamic imports for code splitting
export const loadLocaleMessages = async (locale: Locale): Promise<MessageSchema> => {
  switch (locale) {
    case 'en':
      return (await import('./en')).default
    case 'es':
      return (await import('./es')).default
    case 'fr':
      return (await import('./fr')).default
    case 'de':
      return (await import('./de')).default
    default:
      console.warn(`Locale ${locale} not found, falling back to English`)
      return (await import('./en')).default
  }
}

// Locale metadata
export const localeInfo: Record<Locale, LocaleInfo> = {
  en: {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    direction: 'ltr',
    dateFormat: 'MM/DD/YYYY',
    currency: 'USD'
  },
  es: {
    code: 'es',
    name: 'Spanish',
    nativeName: 'Español',
    flag: '🇪🇸',
    direction: 'ltr',
    dateFormat: 'DD/MM/YYYY',
    currency: 'EUR'
  },
  fr: {
    code: 'fr',
    name: 'French',
    nativeName: 'Français',
    flag: '🇫🇷',
    direction: 'ltr',
    dateFormat: 'DD/MM/YYYY',
    currency: 'EUR'
  },
  de: {
    code: 'de',
    name: 'German',
    nativeName: 'Deutsch',
    flag: '🇩🇪',
    direction: 'ltr',
    dateFormat: 'DD.MM.YYYY',
    currency: 'EUR'
  }
}

// Available locales - ONLY these 4 languages should be available
export const ALLOWED_LOCALES: Locale[] = ['en', 'de', 'fr', 'es'] as const

// Available locales - enforce only allowed languages
export const availableLocales = ALLOWED_LOCALES.filter(locale => 
  Object.keys(localeInfo).includes(locale)
) as Locale[]

// Safeguard: ensure we only have the intended 4 languages
if (availableLocales.length !== 4) {
  console.warn('⚠️ Available locales mismatch! Expected 4 languages (en, de, fr, es), got:', availableLocales)
}

// Default locale
export const defaultLocale: Locale = 'en'

// Get browser locale
export const getBrowserLocale = (): Locale => {
  const browserLang = navigator.language.toLowerCase().split('-')[0]
  return availableLocales.includes(browserLang as Locale) 
    ? (browserLang as Locale) 
    : defaultLocale
}

// Get saved locale from localStorage
export const getSavedLocale = (): Locale | null => {
  const saved = localStorage.getItem('locale')
  if (saved && availableLocales.includes(saved as Locale)) {
    return saved as Locale
  }
  return null
}

// Detect preferred locale
export const detectPreferredLocale = (): Locale => {
  // 1. Check localStorage
  const saved = getSavedLocale()
  if (saved) return saved

  // 2. Check environment variable
  const envLocale = import.meta.env.VITE_DEFAULT_LOCALE
  if (envLocale && availableLocales.includes(envLocale as Locale)) {
    return envLocale as Locale
  }

  // 3. Check browser language
  const browserLocale = getBrowserLocale()
  if (browserLocale) return browserLocale

  // 4. Fallback to default
  return defaultLocale
}

// Export types
export type { MessageSchema, Locale, LocaleInfo } from './types'
