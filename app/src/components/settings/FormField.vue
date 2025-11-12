<template>
  <div :class="containerClass">
    <label 
      :for="id" 
      class="block mb-2 font-medium text-surface-700 dark:text-surface-200"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <p v-if="hint" class="text-sm text-surface-500 dark:text-surface-400 mb-2">
      {{ hint }}
    </p>
    <slot :id="id"></slot>
    <small v-if="error" class="text-red-500 mt-1 block">{{ error }}</small>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  id?: string
  hint?: string
  error?: string
  required?: boolean
  fullWidth?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  fullWidth: true
})

const containerClass = computed(() => {
  return props.fullWidth ? 'w-full' : ''
})
</script>
