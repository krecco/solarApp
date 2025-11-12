<template>
  <div 
    class="progress-card"
    :class="[
      `progress-card--${variant}`,
      `progress-card--${color}`,
      { 'progress-card--clickable': clickable }
    ]"
    @click="handleClick"
  >
    <!-- Background decoration -->
    <div class="progress-card__decoration">
      <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="80" fill="currentColor" fill-opacity="0.05" />
        <circle cx="100" cy="100" r="60" fill="currentColor" fill-opacity="0.05" />
        <circle cx="100" cy="100" r="40" fill="currentColor" fill-opacity="0.05" />
      </svg>
    </div>

    <!-- Header -->
    <div class="progress-card__header">
      <div class="progress-card__icon" v-if="icon">
        <i :class="icon"></i>
      </div>
      <div class="progress-card__header-content">
        <h3 class="progress-card__title">{{ title }}</h3>
        <p class="progress-card__subtitle" v-if="subtitle">{{ subtitle }}</p>
      </div>
      <Dropdown
        v-if="showMenu"
        :options="menuItems"
        optionLabel="label"
        :pt="{ trigger: { class: 'p-button-text p-button-sm' } }"
        @change="handleMenuAction"
      >
        <template #value>
          <Button icon="pi pi-ellipsis-v" text severity="secondary" size="small" />
        </template>
      </Dropdown>
    </div>

    <!-- Progress visualization -->
    <div class="progress-card__visual">
      <!-- Circular progress -->
      <div 
        v-if="variant === 'circular'"
        class="progress-card__circular"
      >
        <svg 
          class="progress-card__circular-svg"
          :width="circularSize"
          :height="circularSize"
          :viewBox="`0 0 ${circularSize} ${circularSize}`"
        >
          <!-- Background circle -->
          <circle
            class="progress-card__circular-bg"
            :cx="circularSize / 2"
            :cy="circularSize / 2"
            :r="radius"
            :stroke-width="strokeWidth"
          />
          
          <!-- Progress circle -->
          <circle
            class="progress-card__circular-progress"
            :cx="circularSize / 2"
            :cy="circularSize / 2"
            :r="radius"
            :stroke-width="strokeWidth"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="progressOffset"
            :style="{ stroke: progressColor }"
          />
          
          <!-- Animated particles -->
          <circle
            v-for="i in 3"
            :key="i"
            class="progress-card__particle"
            :cx="circularSize / 2"
            :cy="circularSize / 2"
            :r="radius"
            :stroke-width="2"
            :stroke-dasharray="`${i * 2} ${circumference}`"
            :style="{ 
              stroke: progressColor,
              animationDelay: `${i * 0.3}s`,
              opacity: 0.3 - (i * 0.1)
            }"
          />
        </svg>
        
        <!-- Center content -->
        <div class="progress-card__circular-content">
          <div class="progress-card__percentage">
            <transition name="counter" mode="out-in">
              <span :key="animatedProgress">{{ animatedProgress }}%</span>
            </transition>
          </div>
          <div class="progress-card__label" v-if="centerLabel">
            {{ centerLabel }}
          </div>
        </div>
      </div>

      <!-- Linear progress -->
      <div 
        v-else-if="variant === 'linear'"
        class="progress-card__linear"
      >
        <div class="progress-card__linear-header">
          <span class="progress-card__value">{{ formattedCurrent }}</span>
          <span class="progress-card__target">{{ formattedTarget }}</span>
        </div>
        <div class="progress-card__linear-track">
          <div 
            class="progress-card__linear-fill"
            :style="{ 
              width: `${progress}%`,
              backgroundColor: progressColor 
            }"
          >
            <span class="progress-card__linear-glow"></span>
          </div>
          <!-- Milestone markers -->
          <div 
            v-for="milestone in milestones"
            :key="milestone.value"
            class="progress-card__milestone"
            :style="{ left: `${(milestone.value / target) * 100}%` }"
            v-tooltip="milestone.label"
          >
            <i class="pi pi-flag-fill"></i>
          </div>
        </div>
        <div class="progress-card__linear-footer">
          <span class="progress-card__percentage-text">{{ progress }}% Complete</span>
          <span class="progress-card__eta" v-if="eta">ETA: {{ eta }}</span>
        </div>
      </div>

      <!-- Segmented progress -->
      <div 
        v-else-if="variant === 'segmented'"
        class="progress-card__segmented"
      >
        <div class="progress-card__segments">
          <div 
            v-for="(segment, index) in segments"
            :key="index"
            class="progress-card__segment"
            :class="{ 'progress-card__segment--completed': segment <= current }"
            :style="{ backgroundColor: segment <= current ? progressColor : undefined }"
          >
            <i v-if="segment <= current" class="pi pi-check"></i>
            <span v-else>{{ segment }}</span>
          </div>
        </div>
        <div class="progress-card__segmented-info">
          <span>{{ current }} / {{ segments.length }} completed</span>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="progress-card__stats" v-if="stats && stats.length > 0">
      <div 
        v-for="stat in stats"
        :key="stat.label"
        class="progress-card__stat"
      >
        <span class="progress-card__stat-label">{{ stat.label }}</span>
        <span class="progress-card__stat-value">{{ stat.value }}</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="progress-card__actions" v-if="showActions">
      <Button
        :label="primaryAction?.label || 'View Details'"
        :icon="primaryAction?.icon || 'pi pi-arrow-right'"
        :severity="primaryAction?.severity || color"
        size="small"
        @click.stop="handlePrimaryAction"
      />
      <Button
        v-if="secondaryAction"
        :label="secondaryAction.label"
        :icon="secondaryAction.icon"
        severity="secondary"
        text
        size="small"
        @click.stop="handleSecondaryAction"
      />
    </div>

    <!-- Trend indicator -->
    <div class="progress-card__trend" v-if="trend">
      <i 
        :class="[
          trend > 0 ? 'pi pi-trending-up text-green-500' : 'pi pi-trending-down text-red-500'
        ]"
      ></i>
      <span :class="trend > 0 ? 'text-green-500' : 'text-red-500'">
        {{ Math.abs(trend) }}%
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'

interface Stat {
  label: string
  value: string | number
}

interface Milestone {
  value: number
  label: string
}

interface Action {
  label: string
  icon?: string
  severity?: string
  command?: () => void
}

interface Props {
  title: string
  subtitle?: string
  icon?: string
  variant?: 'circular' | 'linear' | 'segmented'
  color?: 'primary' | 'success' | 'warning' | 'danger' | 'info'
  current: number
  target: number
  centerLabel?: string
  eta?: string
  trend?: number
  stats?: Stat[]
  milestones?: Milestone[]
  segments?: number[]
  showActions?: boolean
  showMenu?: boolean
  clickable?: boolean
  primaryAction?: Action
  secondaryAction?: Action
  menuItems?: any[]
  animate?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'circular',
  color: 'primary',
  current: 0,
  target: 100,
  showActions: false,
  showMenu: false,
  clickable: false,
  animate: true,
  menuItems: () => []
})

const emit = defineEmits<{
  click: []
  primaryAction: []
  secondaryAction: []
  menuAction: [item: any]
}>()

// Animated progress value
const animatedProgress = ref(0)

// Circular progress calculations
const circularSize = 160
const strokeWidth = 12
const radius = (circularSize - strokeWidth) / 2
const circumference = 2 * Math.PI * radius

// Computed progress percentage
const progress = computed(() => {
  const percentage = (props.current / props.target) * 100
  return Math.min(Math.max(percentage, 0), 100)
})

// Progress offset for circular variant
const progressOffset = computed(() => {
  return circumference - (progress.value / 100) * circumference
})

// Progress color based on color prop
const progressColor = computed(() => {
  const colors: Record<string, string> = {
    primary: '#3B82F6',
    success: '#10B981',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#06B6D4'
  }
  return colors[props.color] || colors.primary
})

// Format current value
const formattedCurrent = computed(() => {
  if (props.current >= 1000000) {
    return `${(props.current / 1000000).toFixed(1)}M`
  } else if (props.current >= 1000) {
    return `${(props.current / 1000).toFixed(1)}K`
  }
  return props.current.toString()
})

// Format target value
const formattedTarget = computed(() => {
  if (props.target >= 1000000) {
    return `${(props.target / 1000000).toFixed(1)}M`
  } else if (props.target >= 1000) {
    return `${(props.target / 1000).toFixed(1)}K`
  }
  return `/ ${props.target}`
})

// Animate progress
const animateProgress = () => {
  if (!props.animate) {
    animatedProgress.value = Math.round(progress.value)
    return
  }
  
  const startValue = animatedProgress.value
  const endValue = Math.round(progress.value)
  const duration = 1500
  const startTime = Date.now()
  
  const updateProgress = () => {
    const now = Date.now()
    const elapsed = now - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Easing function
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)
    
    animatedProgress.value = Math.round(startValue + (endValue - startValue) * easeOutQuart)
    
    if (progress < 1) {
      requestAnimationFrame(updateProgress)
    }
  }
  
  requestAnimationFrame(updateProgress)
}

// Handlers
const handleClick = () => {
  if (props.clickable) {
    emit('click')
  }
}

const handlePrimaryAction = () => {
  props.primaryAction?.command?.()
  emit('primaryAction')
}

const handleSecondaryAction = () => {
  props.secondaryAction?.command?.()
  emit('secondaryAction')
}

const handleMenuAction = (item: any) => {
  item.command?.()
  emit('menuAction', item)
}

// Watch for changes
watch(() => progress.value, () => {
  animateProgress()
})

// Initialize on mount
onMounted(() => {
  animateProgress()
})
</script>

<style scoped lang="scss">
.progress-card {
  @apply relative bg-white dark:bg-gray-800 rounded-2xl p-6 overflow-hidden transition-all duration-300;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  }
  
  &--clickable {
    @apply cursor-pointer;
  }
  
  // Color variants
  &--primary {
    .progress-card__icon { @apply text-blue-500; }
    .progress-card__decoration { @apply text-blue-500; }
  }
  
  &--success {
    .progress-card__icon { @apply text-green-500; }
    .progress-card__decoration { @apply text-green-500; }
  }
  
  &--warning {
    .progress-card__icon { @apply text-yellow-500; }
    .progress-card__decoration { @apply text-yellow-500; }
  }
  
  &--danger {
    .progress-card__icon { @apply text-red-500; }
    .progress-card__decoration { @apply text-red-500; }
  }
  
  &--info {
    .progress-card__icon { @apply text-cyan-500; }
    .progress-card__decoration { @apply text-cyan-500; }
  }
  
  &__decoration {
    @apply absolute top-0 right-0 w-40 h-40 opacity-20 pointer-events-none;
  }
  
  &__header {
    @apply flex items-start gap-3 mb-6;
    
    &-content {
      @apply flex-1;
    }
  }
  
  &__icon {
    @apply text-2xl;
  }
  
  &__title {
    @apply text-lg font-semibold text-gray-900 dark:text-white;
  }
  
  &__subtitle {
    @apply text-sm text-gray-500 dark:text-gray-400 mt-1;
  }
  
  &__visual {
    @apply mb-6;
  }
  
  // Circular progress styles
  &__circular {
    @apply relative mx-auto;
    width: fit-content;
    
    &-svg {
      @apply transform -rotate-90;
    }
    
    &-bg {
      @apply fill-none stroke-gray-200 dark:stroke-gray-700;
    }
    
    &-progress {
      @apply fill-none transition-all duration-1000 ease-out;
      stroke-linecap: round;
    }
    
    &-content {
      @apply absolute inset-0 flex flex-col items-center justify-center;
    }
  }
  
  &__particle {
    @apply fill-none;
    animation: rotate 3s linear infinite;
    transform-origin: center;
  }
  
  &__percentage {
    @apply text-3xl font-bold text-gray-900 dark:text-white;
  }
  
  &__label {
    @apply text-sm text-gray-500 dark:text-gray-400 mt-1;
  }
  
  // Linear progress styles
  &__linear {
    &-header {
      @apply flex justify-between items-center mb-2;
    }
    
    &-track {
      @apply relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2;
    }
    
    &-fill {
      @apply h-full rounded-full transition-all duration-1000 ease-out relative;
      
      &::after {
        content: '';
        @apply absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent;
        animation: shimmer 2s infinite;
      }
    }
    
    &-glow {
      @apply absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full blur-lg;
      background: inherit;
      animation: pulse 2s infinite;
    }
    
    &-footer {
      @apply flex justify-between items-center text-sm;
    }
  }
  
  &__value {
    @apply text-2xl font-bold text-gray-900 dark:text-white;
  }
  
  &__target {
    @apply text-sm text-gray-500 dark:text-gray-400;
  }
  
  &__percentage-text {
    @apply text-gray-600 dark:text-gray-400;
  }
  
  &__eta {
    @apply text-gray-500 dark:text-gray-400;
  }
  
  &__milestone {
    @apply absolute top-1/2 -translate-y-1/2 -translate-x-1/2 text-yellow-500 text-xs;
  }
  
  // Segmented progress styles
  &__segments {
    @apply flex gap-2 mb-3;
  }
  
  &__segment {
    @apply flex-1 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center text-sm font-medium text-gray-500 dark:text-gray-400 transition-all duration-300;
    
    &--completed {
      @apply text-white;
    }
  }
  
  &__segmented-info {
    @apply text-center text-sm text-gray-600 dark:text-gray-400;
  }
  
  // Stats section
  &__stats {
    @apply grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-gray-200 dark:border-gray-700;
  }
  
  &__stat {
    @apply flex justify-between items-center;
    
    &-label {
      @apply text-sm text-gray-500 dark:text-gray-400;
    }
    
    &-value {
      @apply text-sm font-semibold text-gray-900 dark:text-white;
    }
  }
  
  // Actions section
  &__actions {
    @apply flex gap-2;
  }
  
  // Trend indicator
  &__trend {
    @apply absolute top-6 right-6 flex items-center gap-1 text-sm;
  }
}

// Animations
@keyframes rotate {
  to {
    transform: rotate(360deg);
  }
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(200%);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

// Counter animation
.counter-enter-active,
.counter-leave-active {
  transition: all 0.3s ease;
}

.counter-enter-from {
  transform: scale(1.2);
  opacity: 0;
}

.counter-leave-to {
  transform: scale(0.8);
  opacity: 0;
}
</style>
