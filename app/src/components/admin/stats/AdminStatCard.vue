<template>
  <Card class="stat-card" :class="cardClasses">
    <template #content>
      <div class="stat-card-content">
        <div class="stat-header">
          <div class="stat-info">
            <div class="stat-title">
              <Skeleton v-if="loading" width="100px" height="1rem" />
              <span v-else>{{ title }}</span>
            </div>
            <div class="stat-value-wrapper">
              <div class="stat-value">
                <Skeleton v-if="loading" width="120px" height="2.5rem" />
                <CountUp v-else :end="numericValue" :duration="1.5" :prefix="prefix" :suffix="suffix" />
              </div>
              <div v-if="trend !== undefined" class="stat-trend" :class="trendClass">
                <i :class="trendIcon"></i>
                <span>{{ Math.abs(trend) }}%</span>
              </div>
            </div>
            <div v-if="subtitle" class="stat-subtitle">
              <Skeleton v-if="loading" width="80px" height="0.875rem" />
              <small v-else class="text-color-secondary">{{ subtitle }}</small>
            </div>
          </div>
          <div class="stat-icon-wrapper" :style="iconStyle">
            <i :class="icon" class="stat-icon"></i>
          </div>
        </div>
        
        <div v-if="sparklineData && sparklineData.length" class="stat-sparkline">
          <svg :viewBox="`0 0 ${sparklineWidth} ${sparklineHeight}`" preserveAspectRatio="none">
            <polyline
              :points="sparklinePoints"
              fill="none"
              :stroke="color"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <polyline
              :points="`${sparklinePoints} ${sparklineWidth},${sparklineHeight} 0,${sparklineHeight}`"
              :fill="`${color}15`"
              stroke="none"
            />
          </svg>
        </div>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Card from 'primevue/card'
import Skeleton from 'primevue/skeleton'
import CountUp from '@/components/common/CountUp.vue'

interface Props {
  title: string
  value: number | string
  icon: string
  trend?: number
  color?: string
  loading?: boolean
  subtitle?: string
  prefix?: string
  suffix?: string
  severity?: 'success' | 'warning' | 'danger' | 'info'
  sparklineData?: number[]
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  color: '#10b981',
  severity: 'success'
})

const numericValue = computed(() => {
  if (typeof props.value === 'string') {
    return parseFloat(props.value.replace(/[^0-9.-]/g, '')) || 0
  }
  return props.value
})

const cardClasses = computed(() => ({
  [`stat-card-${props.severity}`]: props.severity,
  'stat-card-loading': props.loading
}))

const iconStyle = computed(() => ({
  backgroundColor: `${props.color}20`,
  color: props.color
}))

const trendClass = computed(() => {
  if (props.trend === undefined) return ''
  return props.trend >= 0 ? 'trend-up' : 'trend-down'
})

const trendIcon = computed(() => {
  if (props.trend === undefined) return ''
  return props.trend >= 0 ? 'pi pi-arrow-up' : 'pi pi-arrow-down'
})

// Sparkline calculations
const sparklineWidth = 100
const sparklineHeight = 30

const sparklinePoints = computed(() => {
  if (!props.sparklineData || props.sparklineData.length === 0) return ''
  
  const data = props.sparklineData
  const max = Math.max(...data)
  const min = Math.min(...data)
  const range = max - min || 1
  
  return data.map((value, index) => {
    const x = (index / (data.length - 1)) * sparklineWidth
    const y = sparklineHeight - ((value - min) / range) * sparklineHeight
    return `${x},${y}`
  }).join(' ')
})
</script>

<style scoped lang="scss">
.stat-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid var(--surface-border);
  background: var(--surface-card);
  overflow: hidden;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    
    .stat-icon-wrapper {
      transform: scale(1.05);
    }
  }
  
  :deep(.p-card-content) {
    padding: 1.5rem;
  }
  
  .stat-card-content {
    position: relative;
  }
  
  .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }
  
  .stat-info {
    flex: 1;
  }
  
  .stat-title {
    color: var(--text-color-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .stat-value-wrapper {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
  }
  
  .stat-value {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--text-color);
    line-height: 1;
  }
  
  .stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    
    &.trend-up {
      color: var(--green-600);
      background: rgba(34, 197, 94, 0.1);
      
      .dark & {
        color: var(--green-400);
      }
    }
    
    &.trend-down {
      color: var(--red-600);
      background: rgba(239, 68, 68, 0.1);
      
      .dark & {
        color: var(--red-400);
      }
    }
    
    i {
      font-size: 0.625rem;
    }
  }
  
  .stat-subtitle {
    font-size: 0.75rem;
  }
  
  .stat-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    
    .stat-icon {
      font-size: 1.75rem;
    }
  }
  
  .stat-sparkline {
    margin-top: 1.5rem;
    height: 30px;
    opacity: 0.8;
    
    svg {
      width: 100%;
      height: 100%;
    }
  }
}

// Severity variants
.stat-card-success {
  .stat-icon-wrapper {
    background-color: rgba(34, 197, 94, 0.1);
    color: var(--green-500);
  }
}

.stat-card-warning {
  .stat-icon-wrapper {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--yellow-500);
  }
}

.stat-card-danger {
  .stat-icon-wrapper {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--red-500);
  }
}

.stat-card-info {
  .stat-icon-wrapper {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--blue-500);
  }
}
</style>
