<template>
  <span>{{ displayValue }}</span>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

interface Props {
  start?: number
  end: number
  duration?: number
  prefix?: string
  suffix?: string
  decimals?: number
  separator?: string
}

const props = withDefaults(defineProps<Props>(), {
  start: 0,
  duration: 2,
  decimals: 0,
  separator: ','
})

const currentValue = ref(props.start)

const displayValue = computed(() => {
  const formattedNumber = currentValue.value
    .toFixed(props.decimals)
    .replace(/\B(?=(\d{3})+(?!\d))/g, props.separator)
  
  return `${props.prefix || ''}${formattedNumber}${props.suffix || ''}`
})

const animateValue = () => {
  const startTime = Date.now()
  const startValue = currentValue.value
  const endValue = props.end
  const duration = props.duration * 1000

  const updateValue = () => {
    const now = Date.now()
    const progress = Math.min((now - startTime) / duration, 1)
    
    // Easing function for smooth animation
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)
    
    currentValue.value = startValue + (endValue - startValue) * easeOutQuart
    
    if (progress < 1) {
      requestAnimationFrame(updateValue)
    } else {
      currentValue.value = endValue
    }
  }
  
  requestAnimationFrame(updateValue)
}

watch(() => props.end, () => {
  animateValue()
})

onMounted(() => {
  animateValue()
})
</script>
