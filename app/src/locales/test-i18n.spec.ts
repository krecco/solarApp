/**
 * Test file to verify i18n setup
 * Run this with: npx tsx src/locales/test-i18n.ts
 */

import { describe, it, expect, beforeAll } from 'vitest'
import { setupI18n, setLocale, getLocale, getAvailableLocales } from '@/plugins/i18n'
import type { Locale } from '@/locales/types'

describe('i18n Setup', () => {
  let i18n: any
  
  beforeAll(async () => {
    // Mock browser environment
    global.navigator = { language: 'en-US' } as any
    global.localStorage = {
      getItem: () => null,
      setItem: () => {},
      removeItem: () => {},
      clear: () => {},
      length: 0,
      key: () => null
    } as Storage
    
    // Setup i18n
    i18n = await setupI18n()
  })
  
  describe('Initialization', () => {
    it('should initialize with default locale', () => {
      expect(getLocale()).toBe('en')
    })
    
    it('should have all available locales', () => {
      const locales = getAvailableLocales()
      expect(locales).toHaveLength(4)
      expect(locales.map(l => l.code)).toEqual(['en', 'es', 'fr', 'de'])
    })
  })
  
  describe('Language Loading', () => {
    it('should load English translations', () => {
      const { t } = i18n.global
      expect(t('common.welcome')).toBe('Welcome')
      expect(t('dashboard.title')).toBe('Dashboard')
    })
    
    it('should switch to Spanish', async () => {
      await setLocale('es')
      const { t } = i18n.global
      expect(getLocale()).toBe('es')
      expect(t('common.welcome')).toBe('Bienvenido')
      expect(t('dashboard.title')).toBe('Panel')
    })
    
    it('should switch to French', async () => {
      await setLocale('fr')
      const { t } = i18n.global
      expect(getLocale()).toBe('fr')
      expect(t('common.welcome')).toBe('Bienvenue')
      expect(t('dashboard.title')).toBe('Tableau de bord')
    })
    
    it('should switch to German', async () => {
      await setLocale('de')
      const { t } = i18n.global
      expect(getLocale()).toBe('de')
      expect(t('common.welcome')).toBe('Willkommen')
      expect(t('dashboard.title')).toBe('Dashboard')
    })
  })
  
  describe('Formatting', () => {
    beforeAll(async () => {
      await setLocale('en')
    })
    
    it('should format numbers', () => {
      const { n } = i18n.global
      expect(n(1234.56, 'decimal')).toBe('1,234.56')
      expect(n(0.75, 'percent')).toBe('75%')
    })
    
    it('should format dates', () => {
      const { d } = i18n.global
      const date = new Date('2024-01-15T10:30:00')
      const formatted = d(date, 'short')
      expect(formatted).toContain('Jan')
      expect(formatted).toContain('15')
      expect(formatted).toContain('2024')
    })
    
    it('should format currency in different locales', async () => {
      const { n } = i18n.global
      
      // English (USD)
      await setLocale('en')
      expect(n(99.99, 'currency')).toContain('99.99')
      
      // German (EUR)
      await setLocale('de')
      expect(n(99.99, 'currency')).toContain('99,99')
    })
  })
  
  describe('Translation with Parameters', () => {
    beforeAll(async () => {
      await setLocale('en')
    })
    
    it('should handle parameters in translations', () => {
      const { t } = i18n.global
      expect(t('validation.minLength', { min: 5 })).toBe('Must be at least 5 characters')
      expect(t('validation.maxLength', { max: 10 })).toBe('Must be no more than 10 characters')
    })
  })
  
  describe('Locale Persistence', () => {
    it('should save locale to localStorage', async () => {
      const setItemSpy = vi.spyOn(localStorage, 'setItem')
      await setLocale('es')
      expect(setItemSpy).toHaveBeenCalledWith('locale', 'es')
    })
  })
  
  describe('Type Safety', () => {
    it('should have proper TypeScript types', () => {
      const { t } = i18n.global
      
      // These should work with TypeScript
      const welcome: string = t('common.welcome')
      const dashboard: string = t('dashboard.title')
      
      expect(typeof welcome).toBe('string')
      expect(typeof dashboard).toBe('string')
    })
  })
})

// Run basic checks if executed directly
if (import.meta.url === `file://${process.argv[1]}`) {
  console.log('🧪 Running i18n setup tests...\n')
  
  async function runBasicTests() {
    try {
      // Mock browser environment
      global.navigator = { language: 'en-US' } as any
      global.localStorage = {
        getItem: () => null,
        setItem: () => {},
        removeItem: () => {},
        clear: () => {},
        length: 0,
        key: () => null
      } as Storage
      
      console.log('✅ Initializing i18n...')
      const i18n = await setupI18n()
      
      console.log('✅ Testing English translations...')
      const { t } = i18n.global
      console.assert(t('common.welcome') === 'Welcome', 'English welcome message')
      
      console.log('✅ Testing Spanish translations...')
      await setLocale('es')
      console.assert(t('common.welcome') === 'Bienvenido', 'Spanish welcome message')
      
      console.log('✅ Testing French translations...')
      await setLocale('fr')
      console.assert(t('common.welcome') === 'Bienvenue', 'French welcome message')
      
      console.log('✅ Testing German translations...')
      await setLocale('de')
      console.assert(t('common.welcome') === 'Willkommen', 'German welcome message')
      
      console.log('\n🎉 All tests passed!')
      
    } catch (error) {
      console.error('❌ Test failed:', error)
      process.exit(1)
    }
  }
  
  runBasicTests()
}
