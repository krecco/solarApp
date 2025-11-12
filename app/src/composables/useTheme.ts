// Composable for theme management in Vue components
import { ref, computed, onMounted } from 'vue'
import { 
  toggleDarkMode as toggle, 
  changeTheme as change,
  getCurrentTheme,
  initializeTheme,
  type ThemeName 
} from '../theme/utils'

export function useTheme() {
  const isDarkMode = ref(false)
  const currentTheme = ref<ThemeName>('lara-green')
  
  // Toggle dark mode
  const toggleDarkMode = () => {
    isDarkMode.value = toggle()
  }
  
  // Change theme
  const changeTheme = (themeName: ThemeName) => {
    change(themeName)
    currentTheme.value = themeName
  }
  
  // Check if dark mode is active
  const checkDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('app-dark')
  }
  
  // Available themes
  const availableThemes = computed(() => [
    { name: 'lara-green', label: 'Lara Green' },
    { name: 'lara', label: 'Lara Default' },
    { name: 'aura', label: 'Aura' },
    { name: 'material', label: 'Material' },
    { name: 'nora', label: 'Nora' }
  ])
  
  // Initialize on mount
  onMounted(() => {
    initializeTheme()
    checkDarkMode()
    currentTheme.value = getCurrentTheme()
  })
  
  return {
    isDarkMode,
    currentTheme,
    toggleDarkMode,
    changeTheme,
    availableThemes
  }
}