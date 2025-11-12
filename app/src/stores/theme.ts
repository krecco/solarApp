import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'

export const useThemeStore = defineStore('theme', () => {
  // State - FORCED TO LIGHT MODE ONLY
  const themeMode = ref<'light' | 'dark' | 'auto'>('light')
  const primaryColor = ref('green')
  const _isDarkMode = ref(false)
  const currentTheme = ref('lara-green')
  const density = ref<'compact' | 'normal' | 'comfortable'>('normal')
  const isRTL = ref(false)
  
  // Computed properties for compatibility
  const isDark = computed(() => _isDarkMode.value)
  const isDarkMode = computed(() => _isDarkMode.value)
  
  // Initialize from localStorage
  const initTheme = () => {
    // FORCE LIGHT MODE ONLY - ignore saved mode
    themeMode.value = 'light'

    const savedColor = localStorage.getItem('primaryColor')
    const savedTheme = localStorage.getItem('currentTheme')
    const savedDensity = localStorage.getItem('density') as 'compact' | 'normal' | 'comfortable'
    const savedRTL = localStorage.getItem('isRTL')
    
    if (savedColor) {
      primaryColor.value = savedColor
    }
    
    if (savedTheme) {
      currentTheme.value = savedTheme
    }
    
    if (savedDensity) {
      density.value = savedDensity
    }
    
    if (savedRTL) {
      isRTL.value = savedRTL === 'true'
    }
    
    applyThemeMode()
    applyPrimaryColor()
  }
  
  // Apply theme mode
  const applyThemeMode = () => {
    const root = document.documentElement
    
    // Remove existing theme classes
    root.classList.remove('app-light', 'app-dark', 'dark')
    
    if (themeMode.value === 'auto') {
      // Check system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      _isDarkMode.value = prefersDark
      if (prefersDark) {
        root.classList.add('app-dark', 'dark')
      } else {
        root.classList.add('app-light')
      }
    } else {
      _isDarkMode.value = themeMode.value === 'dark'
      if (themeMode.value === 'dark') {
        root.classList.add('app-dark', 'dark')
      } else {
        root.classList.add('app-light')
      }
    }
    
    // Update PrimeVue theme mode
    updatePrimeVueTheme()
  }
  
  // Update PrimeVue specific theme settings
  const updatePrimeVueTheme = () => {
    const root = document.documentElement
    
    if (_isDarkMode.value) {
      // Dark mode CSS variables
      root.style.setProperty('--surface-ground', '#0f0f0f')
      root.style.setProperty('--surface-section', '#18181b')
      root.style.setProperty('--surface-card', '#18181b')
      root.style.setProperty('--surface-overlay', '#27272a')
      root.style.setProperty('--surface-border', '#3f3f46')
      root.style.setProperty('--surface-hover', '#3f3f46')
      root.style.setProperty('--text-color', '#f1f5f9')
      root.style.setProperty('--text-color-secondary', '#94a3b8')
      root.style.setProperty('--primary-color-alpha', 'rgba(16, 185, 129, 0.2)')
      
      // Surface colors for dark mode
      root.style.setProperty('--surface-0', '#09090b')
      root.style.setProperty('--surface-50', '#18181b')
      root.style.setProperty('--surface-100', '#27272a')
      root.style.setProperty('--surface-200', '#3f3f46')
      root.style.setProperty('--surface-300', '#52525b')
      root.style.setProperty('--surface-400', '#71717a')
      root.style.setProperty('--surface-500', '#a1a1aa')
      root.style.setProperty('--surface-600', '#d4d4d8')
      root.style.setProperty('--surface-700', '#e4e4e7')
      root.style.setProperty('--surface-800', '#f4f4f5')
      root.style.setProperty('--surface-900', '#fafafa')
      root.style.setProperty('--surface-950', '#ffffff')
      
      // Additional dark mode specific variables
      root.style.setProperty('--mask-bg', 'rgba(0, 0, 0, 0.6)')
      root.style.setProperty('--highlight-bg', 'rgba(16, 185, 129, 0.16)')
      root.style.setProperty('--highlight-text-color', '#10b981')
      root.style.setProperty('--focus-ring', '0 0 0 0.2rem rgba(16, 185, 129, 0.25)')
    } else {
      // Light mode CSS variables
      root.style.setProperty('--surface-ground', '#f8f9fa')
      root.style.setProperty('--surface-section', '#ffffff')
      root.style.setProperty('--surface-card', '#ffffff')
      root.style.setProperty('--surface-overlay', '#ffffff')
      root.style.setProperty('--surface-border', '#dee2e6')
      root.style.setProperty('--surface-hover', '#f3f4f6')
      root.style.setProperty('--text-color', '#1f2937')
      root.style.setProperty('--text-color-secondary', '#6b7280')
      root.style.setProperty('--primary-color-alpha', 'rgba(16, 185, 129, 0.1)')
      
      // Surface colors for light mode
      root.style.setProperty('--surface-0', '#ffffff')
      root.style.setProperty('--surface-50', '#fafafa')
      root.style.setProperty('--surface-100', '#f4f4f5')
      root.style.setProperty('--surface-200', '#e4e4e7')
      root.style.setProperty('--surface-300', '#d4d4d8')
      root.style.setProperty('--surface-400', '#a1a1aa')
      root.style.setProperty('--surface-500', '#71717a')
      root.style.setProperty('--surface-600', '#52525b')
      root.style.setProperty('--surface-700', '#3f3f46')
      root.style.setProperty('--surface-800', '#27272a')
      root.style.setProperty('--surface-900', '#18181b')
      root.style.setProperty('--surface-950', '#09090b')
      
      // Additional light mode specific variables
      root.style.setProperty('--mask-bg', 'rgba(0, 0, 0, 0.4)')
      root.style.setProperty('--highlight-bg', 'rgba(16, 185, 129, 0.12)')
      root.style.setProperty('--highlight-text-color', '#059669')
      root.style.setProperty('--focus-ring', '0 0 0 0.2rem rgba(16, 185, 129, 0.25)')
    }
  }
  
  // Apply primary color
  const applyPrimaryColor = () => {
    console.log('🎨 applyPrimaryColor called with:', primaryColor.value)
    const root = document.documentElement

    // Remove existing color classes
    const colorClasses = [
      'theme-blue', 'theme-green', 'theme-purple', 'theme-red',
      'theme-orange', 'theme-teal', 'theme-pink', 'theme-indigo'
    ]
    colorClasses.forEach(cls => root.classList.remove(cls))

    // Add new color class
    root.classList.add(`theme-${primaryColor.value}`)
    console.log('✅ Applied theme class:', `theme-${primaryColor.value}`)
    
    // Color palette definitions
    const colorPalettes = {
      green: {
        50: '#ecfdf5',
        100: '#d1fae5',
        200: '#a7f3d0',
        300: '#6ee7b7',
        400: '#34d399',
        500: '#10b981',
        600: '#059669',
        700: '#047857',
        800: '#065f46',
        900: '#064e3b',
        950: '#022c22'
      },
      blue: {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a',
        950: '#172554'
      },
      purple: {
        50: '#faf5ff',
        100: '#f3e8ff',
        200: '#e9d5ff',
        300: '#d8b4fe',
        400: '#c084fc',
        500: '#a855f7',
        600: '#9333ea',
        700: '#7e22ce',
        800: '#6b21a8',
        900: '#581c87',
        950: '#3b0764'
      },
      red: {
        50: '#fef2f2',
        100: '#fee2e2',
        200: '#fecaca',
        300: '#fca5a5',
        400: '#f87171',
        500: '#ef4444',
        600: '#dc2626',
        700: '#b91c1c',
        800: '#991b1b',
        900: '#7f1d1d',
        950: '#450a0a'
      },
      orange: {
        50: '#fff7ed',
        100: '#ffedd5',
        200: '#fed7aa',
        300: '#fdba74',
        400: '#fb923c',
        500: '#f97316',
        600: '#ea580c',
        700: '#c2410c',
        800: '#9a3412',
        900: '#7c2d12',
        950: '#431407'
      },
      teal: {
        50: '#f0fdfa',
        100: '#ccfbf1',
        200: '#99f6e4',
        300: '#5eead4',
        400: '#2dd4bf',
        500: '#14b8a6',
        600: '#0d9488',
        700: '#0f766e',
        800: '#115e59',
        900: '#134e4a',
        950: '#042f2e'
      },
      pink: {
        50: '#fdf2f8',
        100: '#fce7f3',
        200: '#fbcfe8',
        300: '#f9a8d4',
        400: '#f472b6',
        500: '#ec4899',
        600: '#db2777',
        700: '#be185d',
        800: '#9d174d',
        900: '#831843',
        950: '#500724'
      },
      indigo: {
        50: '#eef2ff',
        100: '#e0e7ff',
        200: '#c7d2fe',
        300: '#a5b4fc',
        400: '#818cf8',
        500: '#6366f1',
        600: '#4f46e5',
        700: '#4338ca',
        800: '#3730a3',
        900: '#312e81',
        950: '#1e1b4b'
      }
    }
    
    const palette = colorPalettes[primaryColor.value as keyof typeof colorPalettes]
    if (palette) {
      // Set primary color shades
      Object.entries(palette).forEach(([shade, value]) => {
        root.style.setProperty(`--primary-${shade}`, value)
      })
      
      // Set main primary color
      root.style.setProperty('--primary-color', palette['500'])
      root.style.setProperty('--primary-color-text', '#ffffff')
      root.style.setProperty('--primary-color-dark', palette['700'])
      
      // Set gradient colors for dynamic theming
      root.style.setProperty('--gradient-from', palette['400'])
      root.style.setProperty('--gradient-via', palette['500'])
      root.style.setProperty('--gradient-to', palette['600'])
      
      // Update primary color alpha and RGB values
      const rgb = hexToRgb(palette['500'])
      if (rgb) {
        root.style.setProperty('--primary-500-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`)
        root.style.setProperty('--primary-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`) // For Avatar and other components
        root.style.setProperty('--primary-color-alpha', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)`)
        root.style.setProperty('--focus-ring', `0 0 0 0.2rem rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.25)`)
        console.log('✅ CSS Variables updated:', {
          '--primary-color': palette['500'],
          '--primary-color-rgb': `${rgb.r}, ${rgb.g}, ${rgb.b}`,
          '--primary-500': palette['500']
        })
      }
    } else {
      console.warn('⚠️ Color palette not found for:', primaryColor.value)
    }
  }
  
  // Helper function to convert hex to RGB
  const hexToRgb = (hex: string) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : null
  }
  
  // Actions - FORCE LIGHT MODE ONLY
  const setThemeMode = async (mode: 'light' | 'dark' | 'auto') => {
    // Always force light mode, ignore requested mode
    themeMode.value = 'light'
    localStorage.setItem('themeMode', 'light')
    applyThemeMode()

    // Sync with backend if user is authenticated
    try {
      const authStore = useAuthStore()
      if (authStore.isAuthenticated) {
        await authStore.updateUserPreferences({ theme: 'light' })
      }
    } catch (error) {
      console.error('Failed to sync theme with backend:', error)
    }
  }
  
  const setPrimaryColor = async (color: string) => {
    primaryColor.value = color
    localStorage.setItem('primaryColor', color)
    applyPrimaryColor()

    // Sync with backend if user is authenticated
    try {
      const authStore = useAuthStore()
      if (authStore.isAuthenticated) {
        await authStore.updateUserPreferences({ primaryColor: color })
      }
    } catch (error) {
      console.error('Failed to sync primary color with backend:', error)
    }
  }
  
  const setCurrentTheme = (theme: string) => {
    currentTheme.value = theme
    localStorage.setItem('currentTheme', theme)
  }
  
  const setDensity = (d: 'compact' | 'normal' | 'comfortable') => {
    density.value = d
    localStorage.setItem('density', d)
  }
  
  const toggleDarkMode = () => {
    // DISABLED - Always stay in light mode
    setThemeMode('light')
  }
  
  const toggleRTL = () => {
    isRTL.value = !isRTL.value
    localStorage.setItem('isRTL', String(isRTL.value))
  }

  // Sync theme from user preferences (called when preferences are loaded)
  const syncFromPreferences = (preferences: { theme?: string; primaryColor?: string }) => {
    console.log('🔄 syncFromPreferences called with:', preferences)

    if (preferences.theme) {
      // Currently forcing light mode, but store the preference for future use
      themeMode.value = 'light'
      localStorage.setItem('themeMode', 'light')
      applyThemeMode()
      console.log('✅ Theme mode applied:', themeMode.value)
    }

    if (preferences.primaryColor) {
      console.log('🎨 Setting primaryColor from preferences:', preferences.primaryColor)
      primaryColor.value = preferences.primaryColor
      localStorage.setItem('primaryColor', preferences.primaryColor)
      applyPrimaryColor()
      console.log('✅ Primary color applied:', primaryColor.value)
    }
  }
  
  // Watch for system theme changes when in auto mode
  if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      if (themeMode.value === 'auto') {
        applyThemeMode()
      }
    })
  }
  
  // Initialize on creation
  initTheme()
  
  return {
    // State
    themeMode,
    primaryColor,
    isDark,
    isDarkMode,
    currentTheme,
    density,
    isRTL,
    
    // Actions
    setThemeMode,
    setPrimaryColor,
    setCurrentTheme,
    setDensity,
    toggleDarkMode,
    toggleRTL,
    initTheme,
    syncFromPreferences
  }
})