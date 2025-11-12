// Theme utilities for PrimeVue
import { usePreset } from '@primeuix/themes'
import LaraGreen from './lara-green'
import Lara from '@primeuix/themes/lara'
import Aura from '@primeuix/themes/aura'
import Material from '@primeuix/themes/material'
import Nora from '@primeuix/themes/nora'

// Available theme presets
export const themes = {
  'lara-green': LaraGreen,
  'lara': Lara,
  'aura': Aura,
  'material': Material,
  'nora': Nora
} as const

export type ThemeName = keyof typeof themes

// Toggle dark mode
export function toggleDarkMode() {
  const element = document.documentElement
  element.classList.toggle('app-dark')
  
  // Save preference to localStorage
  const isDark = element.classList.contains('app-dark')
  localStorage.setItem('theme-dark-mode', isDark ? 'true' : 'false')
  
  return isDark
}

// Initialize dark mode from localStorage
export function initDarkMode() {
  const savedDarkMode = localStorage.getItem('theme-dark-mode')
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  
  // Use saved preference, or system preference as fallback
  const shouldBeDark = savedDarkMode ? savedDarkMode === 'true' : prefersDark
  
  if (shouldBeDark) {
    document.documentElement.classList.add('app-dark')
  }
  
  return shouldBeDark
}

// Change theme preset at runtime
export function changeTheme(themeName: ThemeName) {
  const theme = themes[themeName]
  if (theme) {
    usePreset(theme)
    localStorage.setItem('theme-preset', themeName)
  }
}

// Get current theme name from localStorage
export function getCurrentTheme(): ThemeName {
  const saved = localStorage.getItem('theme-preset') as ThemeName
  return saved && saved in themes ? saved : 'lara-green'
}

// Initialize theme from localStorage
export function initTheme() {
  const themeName = getCurrentTheme()
  if (themeName !== 'lara-green') {
    changeTheme(themeName)
  }
}

// Combined initialization
export function initializeTheme() {
  initDarkMode()
  initTheme()
}