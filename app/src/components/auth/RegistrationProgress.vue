<template>
  <div class="registration-progress">
    <div class="flex align-items-center justify-content-center">
      <div 
        v-for="(step, index) in steps"
        :key="step.id"
        class="flex align-items-center"
      >
        <!-- Step indicator -->
        <div class="flex align-items-center">
          <div 
            :class="[
              'step-indicator flex align-items-center justify-content-center',
              getStepClass(index + 1)
            ]"
            @click="handleStepClick(index + 1)"
          >
            <i v-if="isStepCompleted(index + 1)" class="pi pi-check"></i>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="ml-2">
            <p class="mb-0 font-semibold">{{ step.title }}</p>
            <small class="text-color-secondary">{{ step.description }}</small>
          </div>
        </div>
        
        <!-- Connector line -->
        <Divider 
          v-if="index < steps.length - 1"
          layout="horizontal" 
          class="mx-3 flex-1"
          :class="{ 'completed-divider': isStepCompleted(index + 1) }"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Divider from 'primevue/divider'

interface RegistrationStep {
  id: string
  title: string
  description: string
}

interface Props {
  currentStep: number
  completedSteps?: number[]
  allowNavigation?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  currentStep: 1,
  completedSteps: () => [],
  allowNavigation: true
})

const emit = defineEmits<{
  'step-click': [step: number]
}>()

const steps: RegistrationStep[] = [
  {
    id: 'account',
    title: 'Create Account',
    description: 'Basic information'
  },
  {
    id: 'profile',
    title: 'Company Setup',
    description: 'Business details'
  }
]

const isStepCompleted = (step: number): boolean => {
  return props.completedSteps.includes(step) || step < props.currentStep
}

const getStepClass = (step: number): string => {
  if (step === props.currentStep) {
    return 'step-current'
  } else if (isStepCompleted(step)) {
    return 'step-completed'
  } else {
    return 'step-pending'
  }
}

const handleStepClick = (step: number) => {
  if (props.allowNavigation && isStepCompleted(step)) {
    emit('step-click', step)
  }
}
</script>

<style scoped lang="scss">
.registration-progress {
  padding: 1rem 0;
}

.step-indicator {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  font-weight: 600;
  transition: all 0.3s ease;
  
  &.step-current {
    background: var(--primary-color);
    color: white;
    box-shadow: 0 0 0 4px rgba(var(--primary-color-rgb), 0.2);
  }
  
  &.step-completed {
    background: var(--green-500);
    color: white;
    cursor: pointer;
    
    &:hover {
      background: var(--green-600);
    }
  }
  
  &.step-pending {
    background: var(--surface-200);
    color: var(--text-color-secondary);
    cursor: not-allowed;
  }
}

.completed-divider {
  :deep(.p-divider-content) {
    background: var(--green-500);
  }
}
</style>
