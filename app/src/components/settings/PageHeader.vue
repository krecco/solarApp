<template>
  <div class="mb-6">
    <div class="flex flex-column sm:flex-row sm:align-items-center sm:justify-content-between">
      <div>
        <h1 class="text-3xl font-bold text-surface-700 dark:text-surface-100 mb-2">
          <i v-if="icon" :class="icon" class="mr-2"></i>
          {{ title }}
        </h1>
        <p v-if="subtitle" class="text-surface-500 dark:text-surface-400">
          {{ subtitle }}
        </p>
      </div>
      <div v-if="$slots.actions" class="mt-3 sm:mt-0">
        <slot name="actions"></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  subtitle?: string
  icon?: string
}

defineProps<Props>()
</script>
