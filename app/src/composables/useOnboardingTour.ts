import { ref, computed } from 'vue'
import { tourSteps, type TourStep, getStepById, getNextStep, getPrevStep } from '@/utils/tourSteps'

// Storage key for tour state
const TOUR_STORAGE_KEY = 'admin_v2_onboarding_tour'

interface TourState {
  completed: boolean
  currentStep: string
  skipped: boolean
  startedAt?: string
  completedAt?: string
}

// Global state
const isActive = ref(false)
const currentStepId = ref('welcome')
const tourState = ref<TourState>({
  completed: false,
  currentStep: 'welcome',
  skipped: false
})

export function useOnboardingTour() {
  // Load state from localStorage
  const loadState = () => {
    try {
      const stored = localStorage.getItem(TOUR_STORAGE_KEY)
      if (stored) {
        const parsed = JSON.parse(stored) as TourState
        tourState.value = parsed
        currentStepId.value = parsed.currentStep || 'welcome'
      }
    } catch (error) {
      console.error('Failed to load tour state:', error)
    }
  }

  // Save state to localStorage
  const saveState = () => {
    try {
      localStorage.setItem(TOUR_STORAGE_KEY, JSON.stringify(tourState.value))
    } catch (error) {
      console.error('Failed to save tour state:', error)
    }
  }

  // Start the tour
  const startTour = () => {
    isActive.value = true
    currentStepId.value = 'welcome'
    tourState.value.startedAt = new Date().toISOString()
    tourState.value.currentStep = 'welcome'
    saveState()
  }

  // Resume the tour from saved position
  const resumeTour = () => {
    loadState()
    if (!tourState.value.completed && !tourState.value.skipped) {
      isActive.value = true
      currentStepId.value = tourState.value.currentStep || 'welcome'
    }
  }

  // Skip the tour
  const skipTour = () => {
    isActive.value = false
    tourState.value.skipped = true
    tourState.value.currentStep = currentStepId.value
    saveState()
  }

  // Complete the tour
  const completeTour = () => {
    isActive.value = false
    tourState.value.completed = true
    tourState.value.completedAt = new Date().toISOString()
    saveState()
  }

  // Go to next step
  const nextStep = () => {
    const next = getNextStep(currentStepId.value)
    if (next) {
      currentStepId.value = next.id
      tourState.value.currentStep = next.id
      saveState()
    }
  }

  // Go to previous step
  const prevStep = () => {
    const prev = getPrevStep(currentStepId.value)
    if (prev) {
      currentStepId.value = prev.id
      tourState.value.currentStep = prev.id
      saveState()
    }
  }

  // Go to specific step
  const goToStep = (stepId: string) => {
    const step = getStepById(stepId)
    if (step) {
      currentStepId.value = step.id
      tourState.value.currentStep = step.id
      saveState()
    }
  }

  // Reset tour (for testing or re-running)
  const resetTour = () => {
    isActive.value = false
    currentStepId.value = 'welcome'
    tourState.value = {
      completed: false,
      currentStep: 'welcome',
      skipped: false
    }
    localStorage.removeItem(TOUR_STORAGE_KEY)
  }

  // Check if tour should auto-start
  const shouldAutoStart = () => {
    loadState()
    return !tourState.value.completed && !tourState.value.skipped
  }

  // Computed properties
  const currentStep = computed(() => getStepById(currentStepId.value))
  
  const progress = computed(() => {
    const currentIndex = tourSteps.findIndex(step => step.id === currentStepId.value)
    return {
      current: currentIndex + 1,
      total: tourSteps.length,
      percentage: Math.round(((currentIndex + 1) / tourSteps.length) * 100)
    }
  })

  const isFirstStep = computed(() => currentStepId.value === tourSteps[0].id)
  const isLastStep = computed(() => currentStepId.value === tourSteps[tourSteps.length - 1].id)

  // Initialize on first use
  loadState()

  return {
    // State
    isActive,
    currentStep,
    currentStepId,
    progress,
    isFirstStep,
    isLastStep,
    tourState: computed(() => tourState.value),
    
    // Actions
    startTour,
    resumeTour,
    skipTour,
    completeTour,
    nextStep,
    prevStep,
    goToStep,
    resetTour,
    shouldAutoStart,
    
    // Data
    tourSteps
  }
}
