<template>
  <div class="tour-step" :class="positionClass">
    <Card class="tour-step-card">
      <template #header>
        <div class="flex align-items-center justify-content-between">
          <h3 class="text-xl font-semibold m-0">{{ step.title }}</h3>
          <Button
            v-if="step.actions?.skip"
            icon="pi pi-times"
            severity="secondary"
            text
            rounded
            @click="$emit('skip')"
          />
        </div>
      </template>
      
      <template #content>
        <p class="m-0 mb-3">{{ step.content }}</p>
        
        <!-- Progress indicator -->
        <div class="flex align-items-center gap-2 mb-3">
          <ProgressBar
            :value="progress.percentage"
            :showValue="false"
            style="height: 4px; flex: 1;"
          />
          <span class="text-sm text-color-secondary">
            {{ progress.current }} / {{ progress.total }}
          </span>
        </div>
      </template>
      
      <template #footer>
        <div class="flex justify-content-between">
          <div>
            <Button
              v-if="step.actions?.prev"
              label="Previous"
              icon="pi pi-chevron-left"
              severity="secondary"
              text
              @click="$emit('prev')"
            />
          </div>
          
          <div class="flex gap-2">
            <Button
              v-if="step.actions?.skip"
              label="Skip Tour"
              severity="secondary"
              text
              @click="$emit('skip')"
            />
            <Button
              v-if="step.actions?.next"
              label="Next"
              icon="pi pi-chevron-right"
              iconPos="right"
              @click="$emit('next')"
            />
            <Button
              v-if="step.actions?.finish"
              label="Finish Tour"
              icon="pi pi-check"
              iconPos="right"
              severity="success"
              @click="$emit('finish')"
            />
          </div>
        </div>
      </template>
    </Card>
    
    <!-- Arrow pointer -->
    <div v-if="step.target" class="tour-step-arrow" :class="arrowClass"></div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import ProgressBar from 'primevue/progressbar'
import type { TourStep } from '@/utils/tourSteps'

interface Props {
  step: TourStep
  progress: {
    current: number
    total: number
    percentage: number
  }
}

const props = defineProps<Props>()

defineEmits<{
  skip: []
  next: []
  prev: []
  finish: []
}>()

const positionClass = computed(() => {
  return `tour-step--${props.step.position || 'bottom'}`
})

const arrowClass = computed(() => {
  return `tour-step-arrow--${props.step.position || 'bottom'}`
})
</script>

<style scoped lang="scss">
.tour-step {
  position: absolute;
  z-index: 1100;
  min-width: 320px;
  max-width: 400px;
  
  &-card {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border: 2px solid var(--primary-color);
    
    :deep(.p-card-header) {
      padding-bottom: 0.75rem;
    }
    
    :deep(.p-card-content) {
      padding-top: 0;
      padding-bottom: 1rem;
    }
  }
  
  // Position variants
  &--center {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  
  &--top {
    bottom: 100%;
    margin-bottom: 1rem;
  }
  
  &--bottom {
    top: 100%;
    margin-top: 1rem;
  }
  
  &--left {
    right: 100%;
    margin-right: 1rem;
  }
  
  &--right {
    left: 100%;
    margin-left: 1rem;
  }
  
  // Arrow pointer
  &-arrow {
    position: absolute;
    width: 0;
    height: 0;
    border-style: solid;
    
    &--top {
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-width: 10px 10px 0 10px;
      border-color: var(--surface-card) transparent transparent transparent;
    }
    
    &--bottom {
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-width: 0 10px 10px 10px;
      border-color: transparent transparent var(--surface-card) transparent;
    }
    
    &--left {
      right: -10px;
      top: 50%;
      transform: translateY(-50%);
      border-width: 10px 0 10px 10px;
      border-color: transparent transparent transparent var(--surface-card);
    }
    
    &--right {
      left: -10px;
      top: 50%;
      transform: translateY(-50%);
      border-width: 10px 10px 10px 0;
      border-color: transparent var(--surface-card) transparent transparent;
    }
  }
}

// Animation
.tour-step {
  animation: tourStepFadeIn 0.3s ease-out;
}

@keyframes tourStepFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
