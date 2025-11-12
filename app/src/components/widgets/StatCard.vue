<template>
  <div 
    class="stat-card"
    :class="[
      colorClass,
      {
        'stat-card--clickable': clickable,
        'stat-card--loading': loading
      }
    ]"
    @click="handleClick"
  >
    <!-- Background decoration -->
    <div class="stat-card__decoration">
      <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path
          :d="decorationPath"
          fill="currentColor"
          fill-opacity="0.1"
        />
      </svg>
    </div>

    <!-- Loading skeleton -->
    <template v-if="loading">
      <div class="stat-card__skeleton">
        <Skeleton width="60%" height="1rem" class="mb-2" />
        <Skeleton width="80%" height="2rem" class="mb-3" />
        <Skeleton width="40%" height="0.875rem" />
      </div>
    </template>

    <!-- Content -->
    <template v-else>
      <!-- Icon -->
      <div class="stat-card__icon" v-if="icon">
        <i :class="icon"></i>
      </div>

      <!-- Title -->
      <div class="stat-card__title">
        {{ title }}
      </div>

      <!-- Value with animation -->
      <div class="stat-card__value">
        <transition name="counter" mode="out-in">
          <span :key="animatedValue">
            {{ formattedValue }}
          </span>
        </transition>
      </div>

      <!-- Trend indicator -->
      <div class="stat-card__trend" v-if="trend !== null">
        <span 
          class="stat-card__trend-icon"
          :class="trendClass"
        >
          <i v-if="trend > 0" class="pi pi-arrow-up"></i>
          <i v-else-if="trend < 0" class="pi pi-arrow-down"></i>
          <i v-else class="pi pi-minus"></i>
        </span>
        <span class="stat-card__trend-value">
          {{ Math.abs(trend) }}%
        </span>
        <span class="stat-card__trend-label">
          {{ trendLabel }}
        </span>
      </div>

      <!-- Additional info -->
      <div class="stat-card__info" v-if="info">
        {{ info }}
      </div>

      <!-- Mini chart -->
      <div class="stat-card__chart" v-if="chartData && chartData.length > 0">
        <svg 
          :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
          preserveAspectRatio="none"
        >
          <defs>
            <linearGradient :id="`gradient-${uid}`" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" :stop-color="chartColor" stop-opacity="0.3" />
              <stop offset="100%" :stop-color="chartColor" stop-opacity="0" />
            </linearGradient>
          </defs>
          <path
            :d="chartPath"
            fill="none"
            :stroke="chartColor"
            stroke-width="2"
          />
          <path
            :d="`${chartPath} L ${chartWidth},${chartHeight} L 0,${chartHeight} Z`"
            :fill="`url(#gradient-${uid})`"
          />
        </svg>
      </div>
    </template>

    <!-- Hover effect overlay -->
    <div class="stat-card__overlay"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import Skeleton from 'primevue/skeleton'

interface Props {
  title: string
  value: number | string
  icon?: string
  trend?: number | null
  trendLabel?: string
  info?: string
  color?: 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary'
  prefix?: string
  suffix?: string
  decimals?: number
  loading?: boolean
  clickable?: boolean
  chartData?: number[]
  animate?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  trend: null,
  trendLabel: 'vs last period',
  color: 'primary',
  prefix: '',
  suffix: '',
  decimals: 0,
  loading: false,
  clickable: false,
  animate: true
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

// Generate unique ID for gradients
const uid = ref(Math.random().toString(36).substr(2, 9))

// Animated value for counter effect
const animatedValue = ref(0)
const displayValue = ref(props.value)

// Chart dimensions
const chartWidth = 100
const chartHeight = 30

// Color classes
const colorClass = computed(() => `stat-card--${props.color}`)

// Trend classes
const trendClass = computed(() => {
  if (props.trend === null) return ''
  if (props.trend > 0) return 'stat-card__trend-icon--up'
  if (props.trend < 0) return 'stat-card__trend-icon--down'
  return 'stat-card__trend-icon--neutral'
})

// Chart color based on card color
const chartColor = computed(() => {
  const colors: Record<string, string> = {
    primary: '#3B82F6',
    success: '#10B981',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#06B6D4',
    secondary: '#6B7280'
  }
  return colors[props.color] || colors.primary
})

// Format value with prefix and suffix
const formattedValue = computed(() => {
  const val = props.animate ? animatedValue.value : props.value
  
  if (typeof val === 'string') {
    return `${props.prefix}${val}${props.suffix}`
  }
  
  const formatted = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: props.decimals,
    maximumFractionDigits: props.decimals
  }).format(val)
  
  return `${props.prefix}${formatted}${props.suffix}`
})

// Generate SVG path for decoration
const decorationPath = computed(() => {
  const paths = [
    'M0,100 Q50,50 100,100 T200,100 L200,200 L0,200 Z',
    'M0,150 Q100,50 200,150 L200,200 L0,200 Z',
    'M0,100 C50,150 150,50 200,100 L200,200 L0,200 Z'
  ]
  return paths[Math.floor(Math.random() * paths.length)]
})

// Generate SVG path for mini chart
const chartPath = computed(() => {
  if (!props.chartData || props.chartData.length === 0) return ''
  
  const data = props.chartData
  const max = Math.max(...data)
  const min = Math.min(...data)
  const range = max - min || 1
  
  const points = data.map((value, index) => {
    const x = (index / (data.length - 1)) * chartWidth
    const y = chartHeight - ((value - min) / range) * chartHeight
    return `${x},${y}`
  })
  
  return `M ${points.join(' L ')}`
})

// Animate counter
const animateCounter = () => {
  if (!props.animate || typeof props.value !== 'number') {
    animatedValue.value = props.value as number
    return
  }
  
  const startValue = animatedValue.value
  const endValue = props.value
  const duration = 1000
  const startTime = Date.now()
  
  const updateCounter = () => {
    const now = Date.now()
    const progress = Math.min((now - startTime) / duration, 1)
    
    // Easing function
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)
    
    animatedValue.value = Math.round(startValue + (endValue - startValue) * easeOutQuart)
    
    if (progress < 1) {
      requestAnimationFrame(updateCounter)
    }
  }
  
  requestAnimationFrame(updateCounter)
}

// Handle click
const handleClick = (event: MouseEvent) => {
  if (props.clickable) {
    emit('click', event)
  }
}

// Watch for value changes
watch(() => props.value, () => {
  if (props.animate && typeof props.value === 'number') {
    animateCounter()
  } else {
    displayValue.value = props.value
  }
})

// Initialize on mount
onMounted(() => {
  if (props.animate && typeof props.value === 'number') {
    animateCounter()
  }
})
</script>

<style scoped lang="scss">
.stat-card {
  @apply relative p-6 rounded-2xl bg-white dark:bg-gray-800 overflow-hidden transition-all duration-300;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    
    .stat-card__overlay {
      opacity: 1;
    }
  }
  
  &--clickable {
    @apply cursor-pointer;
  }
  
  &--loading {
    .stat-card__skeleton {
      @apply animate-pulse;
    }
  }
  
  // Color variants
  &--primary {
    .stat-card__icon { @apply text-blue-500; }
    .stat-card__decoration { @apply text-blue-500; }
  }
  
  &--success {
    .stat-card__icon { @apply text-green-500; }
    .stat-card__decoration { @apply text-green-500; }
  }
  
  &--warning {
    .stat-card__icon { @apply text-yellow-500; }
    .stat-card__decoration { @apply text-yellow-500; }
  }
  
  &--danger {
    .stat-card__icon { @apply text-red-500; }
    .stat-card__decoration { @apply text-red-500; }
  }
  
  &--info {
    .stat-card__icon { @apply text-cyan-500; }
    .stat-card__decoration { @apply text-cyan-500; }
  }
  
  &--secondary {
    .stat-card__icon { @apply text-gray-500; }
    .stat-card__decoration { @apply text-gray-500; }
  }
  
  &__decoration {
    @apply absolute top-0 right-0 w-32 h-32 opacity-20 pointer-events-none;
  }
  
  &__icon {
    @apply text-3xl mb-3;
    
    i {
      @apply block;
    }
  }
  
  &__title {
    @apply text-sm font-medium text-gray-600 dark:text-gray-400 mb-1;
  }
  
  &__value {
    @apply text-3xl font-bold text-gray-900 dark:text-white mb-3;
    
    span {
      @apply inline-block;
    }
  }
  
  &__trend {
    @apply flex items-center gap-1 text-sm;
    
    &-icon {
      @apply flex items-center justify-center w-5 h-5 rounded;
      
      &--up {
        @apply bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400;
      }
      
      &--down {
        @apply bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400;
      }
      
      &--neutral {
        @apply bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400;
      }
    }
    
    &-value {
      @apply font-semibold text-gray-900 dark:text-white;
    }
    
    &-label {
      @apply text-gray-500 dark:text-gray-400;
    }
  }
  
  &__info {
    @apply text-xs text-gray-500 dark:text-gray-400 mt-2;
  }
  
  &__chart {
    @apply absolute bottom-0 left-0 right-0 h-8 opacity-60;
    
    svg {
      @apply w-full h-full;
    }
  }
  
  &__overlay {
    @apply absolute inset-0 bg-gradient-to-r from-transparent to-white/5 dark:to-white/10 opacity-0 transition-opacity duration-300 pointer-events-none;
  }
}

// Counter animation
.counter-enter-active,
.counter-leave-active {
  transition: all 0.3s ease;
}

.counter-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.counter-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
