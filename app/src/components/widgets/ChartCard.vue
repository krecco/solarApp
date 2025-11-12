<template>
  <div 
    class="chart-card"
    :class="[
      `chart-card--${size}`,
      { 'chart-card--loading': loading }
    ]"
  >
    <!-- Header -->
    <div class="chart-card__header" v-if="title || $slots.header || actions.length > 0">
      <div class="chart-card__header-content">
        <slot name="header">
          <h3 class="chart-card__title" v-if="title">{{ title }}</h3>
          <p class="chart-card__subtitle" v-if="subtitle">{{ subtitle }}</p>
        </slot>
      </div>
      
      <!-- Actions -->
      <div class="chart-card__actions" v-if="actions.length > 0 || $slots.actions">
        <slot name="actions">
          <!-- Time range selector -->
          <Dropdown 
            v-if="showTimeRange"
            v-model="selectedTimeRange"
            :options="timeRangeOptions"
            optionLabel="label"
            optionValue="value"
            class="chart-card__time-range"
            @change="handleTimeRangeChange"
          />
          
          <!-- Custom actions -->
          <Button
            v-for="action in actions"
            :key="action.id"
            :icon="action.icon"
            :label="action.label"
            :severity="action.severity || 'secondary'"
            text
            size="small"
            @click="handleAction(action)"
          />
          
          <!-- Fullscreen toggle -->
          <Button
            v-if="showFullscreen"
            :icon="isFullscreen ? 'pi pi-compress' : 'pi pi-expand'"
            severity="secondary"
            text
            size="small"
            @click="toggleFullscreen"
            v-tooltip="'Toggle fullscreen'"
          />
          
          <!-- Export menu -->
          <Button
            v-if="showExport"
            icon="pi pi-download"
            severity="secondary"
            text
            size="small"
            @click="toggleExportMenu"
            v-tooltip="'Export chart'"
          />
          <Menu
            ref="exportMenu"
            :model="exportMenuItems"
            :popup="true"
          />
        </slot>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="chart-card__loading">
      <Skeleton height="100%" />
    </div>

    <!-- Chart container -->
    <div 
      v-else
      class="chart-card__body"
      :style="{ height: computedHeight }"
    >
      <canvas 
        v-if="type === 'canvas'"
        ref="canvasRef"
        :id="chartId"
      ></canvas>
      
      <div 
        v-else-if="type === 'svg'"
        ref="svgContainer"
        :id="chartId"
        class="chart-card__svg"
      ></div>
      
      <div 
        v-else
        ref="customContainer"
        :id="chartId"
        class="chart-card__custom"
      >
        <slot></slot>
      </div>
    </div>

    <!-- Footer -->
    <div class="chart-card__footer" v-if="$slots.footer || showLegend">
      <slot name="footer">
        <div class="chart-card__legend" v-if="showLegend && legendItems.length > 0">
          <div 
            v-for="item in legendItems"
            :key="item.label"
            class="chart-card__legend-item"
            :class="{ 'chart-card__legend-item--disabled': item.disabled }"
            @click="toggleLegendItem(item)"
          >
            <span 
              class="chart-card__legend-color"
              :style="{ backgroundColor: item.color }"
            ></span>
            <span class="chart-card__legend-label">{{ item.label }}</span>
            <span class="chart-card__legend-value" v-if="item.value">
              {{ formatValue(item.value) }}
            </span>
          </div>
        </div>
      </slot>
    </div>

    <!-- Overlay for interactions -->
    <div 
      v-if="interactive"
      class="chart-card__overlay"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import Menu from 'primevue/menu'
import Skeleton from 'primevue/skeleton'
import Chart from 'chart.js/auto'
import { useTheme } from '@/stores/theme'

interface ChartAction {
  id: string
  label?: string
  icon: string
  severity?: string
  command?: () => void
}

interface LegendItem {
  label: string
  color: string
  value?: number | string
  disabled?: boolean
}

interface Props {
  title?: string
  subtitle?: string
  type?: 'canvas' | 'svg' | 'custom'
  size?: 'small' | 'medium' | 'large' | 'full'
  height?: string
  loading?: boolean
  showTimeRange?: boolean
  showFullscreen?: boolean
  showExport?: boolean
  showLegend?: boolean
  interactive?: boolean
  actions?: ChartAction[]
  legendItems?: LegendItem[]
  chartConfig?: any
}

const props = withDefaults(defineProps<Props>(), {
  type: 'canvas',
  size: 'medium',
  loading: false,
  showTimeRange: false,
  showFullscreen: false,
  showExport: false,
  showLegend: false,
  interactive: false,
  actions: () => [],
  legendItems: () => []
})

const emit = defineEmits<{
  timeRangeChange: [value: string]
  export: [format: string]
  action: [action: ChartAction]
  legendToggle: [item: LegendItem]
}>()

// Refs
const canvasRef = ref<HTMLCanvasElement>()
const svgContainer = ref<HTMLDivElement>()
const customContainer = ref<HTMLDivElement>()
const exportMenu = ref()
const chartInstance = ref<Chart | null>(null)

// State
const selectedTimeRange = ref('7d')
const isFullscreen = ref(false)
const chartId = ref(`chart-${Math.random().toString(36).substr(2, 9)}`)

// Theme
const theme = useTheme()

// Time range options
const timeRangeOptions = [
  { label: 'Last 24 hours', value: '1d' },
  { label: 'Last 7 days', value: '7d' },
  { label: 'Last 30 days', value: '30d' },
  { label: 'Last 3 months', value: '3m' },
  { label: 'Last 6 months', value: '6m' },
  { label: 'Last year', value: '1y' }
]

// Export menu items
const exportMenuItems = [
  {
    label: 'Export as PNG',
    icon: 'pi pi-image',
    command: () => exportChart('png')
  },
  {
    label: 'Export as SVG',
    icon: 'pi pi-file',
    command: () => exportChart('svg')
  },
  {
    label: 'Export as CSV',
    icon: 'pi pi-file-excel',
    command: () => exportChart('csv')
  },
  {
    label: 'Export as JSON',
    icon: 'pi pi-code',
    command: () => exportChart('json')
  }
]

// Computed height based on size
const computedHeight = computed(() => {
  if (props.height) return props.height
  
  const heights: Record<string, string> = {
    small: '200px',
    medium: '300px',
    large: '400px',
    full: '100%'
  }
  
  return heights[props.size] || heights.medium
})

// Initialize chart
const initChart = async () => {
  if (props.type !== 'canvas' || !canvasRef.value || !props.chartConfig) return
  
  await nextTick()
  
  // Destroy existing chart
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  // Apply theme colors to chart config
  const themedConfig = {
    ...props.chartConfig,
    options: {
      ...props.chartConfig.options,
      plugins: {
        ...props.chartConfig.options?.plugins,
        legend: {
          display: false, // We use custom legend
          ...props.chartConfig.options?.plugins?.legend
        }
      },
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index'
      },
      scales: {
        ...props.chartConfig.options?.scales,
        x: {
          grid: {
            display: false,
            ...props.chartConfig.options?.scales?.x?.grid
          },
          ticks: {
            color: theme.isDarkMode ? '#9CA3AF' : '#6B7280',
            ...props.chartConfig.options?.scales?.x?.ticks
          }
        },
        y: {
          grid: {
            color: theme.isDarkMode ? '#374151' : '#E5E7EB',
            ...props.chartConfig.options?.scales?.y?.grid
          },
          ticks: {
            color: theme.isDarkMode ? '#9CA3AF' : '#6B7280',
            ...props.chartConfig.options?.scales?.y?.ticks
          }
        }
      }
    }
  }
  
  chartInstance.value = new Chart(canvasRef.value, themedConfig)
}

// Update chart
const updateChart = () => {
  if (!chartInstance.value) return
  
  chartInstance.value.update()
}

// Export chart
const exportChart = (format: string) => {
  emit('export', format)
  
  if (format === 'png' && chartInstance.value) {
    const url = chartInstance.value.toBase64Image()
    const link = document.createElement('a')
    link.download = `chart-${Date.now()}.png`
    link.href = url
    link.click()
  }
}

// Toggle fullscreen
const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
  
  const element = document.querySelector(`.chart-card`)
  if (!element) return
  
  if (isFullscreen.value) {
    element.requestFullscreen?.()
  } else {
    document.exitFullscreen?.()
  }
}

// Toggle export menu
const toggleExportMenu = (event: Event) => {
  exportMenu.value?.toggle(event)
}

// Handle time range change
const handleTimeRangeChange = () => {
  emit('timeRangeChange', selectedTimeRange.value)
}

// Handle action
const handleAction = (action: ChartAction) => {
  action.command?.()
  emit('action', action)
}

// Toggle legend item
const toggleLegendItem = (item: LegendItem) => {
  item.disabled = !item.disabled
  emit('legendToggle', item)
}

// Format value
const formatValue = (value: number | string) => {
  if (typeof value === 'string') return value
  
  return new Intl.NumberFormat('en-US', {
    notation: 'compact',
    maximumFractionDigits: 1
  }).format(value)
}

// Handle mouse interactions
const handleMouseEnter = () => {
  // Add hover effect
}

const handleMouseLeave = () => {
  // Remove hover effect
}

// Watch for config changes
watch(() => props.chartConfig, () => {
  if (props.type === 'canvas') {
    initChart()
  }
}, { deep: true })

// Watch for theme changes
watch(() => theme.isDarkMode, () => {
  if (chartInstance.value) {
    initChart()
  }
})

// Lifecycle
onMounted(() => {
  if (props.type === 'canvas' && props.chartConfig) {
    initChart()
  }
})

onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
})

// Expose methods
defineExpose({
  updateChart,
  exportChart
})
</script>

<style scoped lang="scss">
.chart-card {
  @apply bg-white dark:bg-gray-800 rounded-2xl overflow-hidden transition-all duration-300;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  
  &:hover {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  }
  
  &--small {
    @apply p-4;
  }
  
  &--medium {
    @apply p-6;
  }
  
  &--large {
    @apply p-8;
  }
  
  &--full {
    @apply p-0 h-full;
    
    .chart-card__body {
      @apply h-full;
    }
  }
  
  &--loading {
    .chart-card__loading {
      @apply animate-pulse;
    }
  }
  
  &__header {
    @apply flex items-start justify-between mb-4;
    
    &-content {
      @apply flex-1;
    }
  }
  
  &__title {
    @apply text-lg font-semibold text-gray-900 dark:text-white;
  }
  
  &__subtitle {
    @apply text-sm text-gray-500 dark:text-gray-400 mt-1;
  }
  
  &__actions {
    @apply flex items-center gap-2;
  }
  
  &__time-range {
    @apply text-sm;
    
    :deep(.p-dropdown) {
      @apply border-gray-200 dark:border-gray-700;
    }
  }
  
  &__loading {
    @apply w-full;
  }
  
  &__body {
    @apply relative w-full;
    
    canvas {
      @apply w-full h-full;
    }
  }
  
  &__svg,
  &__custom {
    @apply w-full h-full;
  }
  
  &__footer {
    @apply mt-4 pt-4 border-t border-gray-200 dark:border-gray-700;
  }
  
  &__legend {
    @apply flex flex-wrap gap-4;
    
    &-item {
      @apply flex items-center gap-2 cursor-pointer transition-opacity;
      
      &:hover {
        @apply opacity-80;
      }
      
      &--disabled {
        @apply opacity-40;
      }
    }
    
    &-color {
      @apply w-3 h-3 rounded-full;
    }
    
    &-label {
      @apply text-sm text-gray-600 dark:text-gray-400;
    }
    
    &-value {
      @apply text-sm font-medium text-gray-900 dark:text-white;
    }
  }
  
  &__overlay {
    @apply absolute inset-0 pointer-events-none;
  }
  
  // Fullscreen styles
  &:fullscreen {
    @apply p-8 bg-white dark:bg-gray-900;
    
    .chart-card__body {
      @apply h-[calc(100vh-200px)];
    }
  }
}
</style>
