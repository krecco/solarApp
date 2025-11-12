<template>
  <div class="mb-6">
    <h3 class="text-xl font-semibold mb-4 text-surface-700 dark:text-surface-100">
      <i v-if="icon" :class="icon" class="mr-2"></i>
      {{ title }}
    </h3>
    <p v-if="description" class="text-surface-500 dark:text-surface-400 mb-4">
      {{ description }}
    </p>
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  description?: string
  icon?: string
}

defineProps<Props>()
</script>
