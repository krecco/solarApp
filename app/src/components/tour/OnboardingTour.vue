<template>
  <Teleport to="body">
    <div v-if="tour.isActive.value" class="onboarding-tour">
      <!-- Overlay backdrop -->
      <div class="tour-backdrop" @click="handleBackdropClick"></div>
      
      <!-- Spotlight highlight -->
      <div
        v-if="targetElement && currentStep?.target"
        class="tour-spotlight"
        :style="spotlightStyle"
      ></div>
      
      <!-- Tour step -->
      <TourStep
        v-if="currentStep"
        :step="currentStep"
        :progress="tour.progress.value"
        :style="stepStyle"
        @skip="handleSkip"
        @next="tour.nextStep"
        @prev="tour.prevStep"
        @finish="handleFinish"
      />
    </div>
  </Teleport>

  <!-- Tour trigger button (for manual start) -->
  <Button
    v-if="showTrigger && !tour.isActive.value && !tour.tourState.value.completed"
    icon="pi pi-question-circle"
    severity="help"
    rounded
    text
    v-tooltip.left="'Start Tour'"
    class="tour-trigger-button"
    @click="tour.startTour"
  />
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useOnboardingTour } from '@/composables/useOnboardingTour'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import TourStep from './TourStep.vue'

interface Props {
  autoStart?: boolean
  showTrigger?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  autoStart: true,
  showTrigger: true
})

const tour = useOnboardingTour()
const toast = useToast()

// Target element tracking
const targetElement = ref<HTMLElement | null>(null)
const targetRect = ref<DOMRect | null>(null)

// Current step reference
const currentStep = computed(() => tour.currentStep.value)

// Calculate spotlight position and size
const spotlightStyle = computed(() => {
  if (!targetRect.value) return {}
  
  const padding = 8
  return {
    top: `${targetRect.value.top - padding}px`,
    left: `${targetRect.value.left - padding}px`,
    width: `${targetRect.value.width + padding * 2}px`,
    height: `${targetRect.value.height + padding * 2}px`
  }
})

// Calculate step position relative to target
const stepStyle = computed(() => {
  if (!targetElement.value || !targetRect.value || !currentStep.value?.target) {
    return {}
  }
  
  const position = currentStep.value.position || 'bottom'
  const rect = targetRect.value
  const style: any = {}
  
  switch (position) {
    case 'top':
      style.position = 'absolute'
      style.left = `${rect.left + rect.width / 2}px`
      style.bottom = `${window.innerHeight - rect.top + 16}px`
      style.transform = 'translateX(-50%)'
      break
    case 'bottom':
      style.position = 'absolute'
      style.left = `${rect.left + rect.width / 2}px`
      style.top = `${rect.bottom + 16}px`
      style.transform = 'translateX(-50%)'
      break
    case 'left':
      style.position = 'absolute'
      style.right = `${window.innerWidth - rect.left + 16}px`
      style.top = `${rect.top + rect.height / 2}px`
      style.transform = 'translateY(-50%)'
      break
    case 'right':
      style.position = 'absolute'
      style.left = `${rect.right + 16}px`
      style.top = `${rect.top + rect.height / 2}px`
      style.transform = 'translateY(-50%)'
      break
  }
  
  return style
})

// Find and update target element
const updateTargetElement = async () => {
  if (!currentStep.value?.target) {
    targetElement.value = null
    targetRect.value = null
    return
  }
  
  await nextTick()
  
  const element = document.querySelector(currentStep.value.target) as HTMLElement
  if (element) {
    targetElement.value = element
    targetRect.value = element.getBoundingClientRect()
    
    // Scroll element into view if needed
    element.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
      inline: 'center'
    })
    
    // Add highlight class
    element.classList.add('tour-highlight')
  } else {
    targetElement.value = null
    targetRect.value = null
  }
}

// Remove highlight from previous element
const removeHighlight = () => {
  document.querySelectorAll('.tour-highlight').forEach(el => {
    el.classList.remove('tour-highlight')
  })
}

// Handle backdrop click
const handleBackdropClick = () => {
  // Don't close on backdrop click for now
  // Could add option to close or show confirm dialog
}

// Handle skip
const handleSkip = () => {
  tour.skipTour()
  removeHighlight()
  
  toast.add({
    severity: 'info',
    summary: 'Tour Skipped',
    detail: 'You can restart the tour anytime from the help menu',
    life: 3000
  })
}

// Handle finish
const handleFinish = () => {
  tour.completeTour()
  removeHighlight()
  
  toast.add({
    severity: 'success',
    summary: 'Tour Complete!',
    detail: 'Great job! You\'re ready to start using all features',
    life: 5000
  })
}

// Update on window resize
const handleResize = () => {
  if (targetElement.value) {
    targetRect.value = targetElement.value.getBoundingClientRect()
  }
}

// Watch for step changes
watch(() => tour.currentStepId.value, () => {
  removeHighlight()
  updateTargetElement()
})

// Watch for tour activation
watch(() => tour.isActive.value, (isActive) => {
  if (isActive) {
    updateTargetElement()
  } else {
    removeHighlight()
  }
})

// Lifecycle
onMounted(() => {
  window.addEventListener('resize', handleResize)
  
  // Auto-start tour if enabled and not completed
  if (props.autoStart && tour.shouldAutoStart()) {
    setTimeout(() => {
      tour.startTour()
    }, 1500) // Small delay to let page load
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  removeHighlight()
})
</script>

<style scoped lang="scss">
.onboarding-tour {
  position: fixed;
  inset: 0;
  z-index: 1000;
  pointer-events: none;
  
  > * {
    pointer-events: auto;
  }
}

.tour-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  animation: fadeIn 0.3s ease-out;
}

.tour-spotlight {
  position: fixed;
  border-radius: 8px;
  box-shadow: 
    0 0 0 9999px rgba(0, 0, 0, 0.5),
    0 0 20px rgba(var(--primary-500-rgb), 0.5);
  transition: all 0.3s ease-out;
  pointer-events: none;
}

.tour-trigger-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 3rem;
  height: 3rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 999;
  
  &:hover {
    transform: scale(1.1);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

// Global highlight styles
:global(.tour-highlight) {
  position: relative;
  z-index: 1001 !important;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(var(--primary-500-rgb), 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(var(--primary-500-rgb), 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(var(--primary-500-rgb), 0);
  }
}
</style>
