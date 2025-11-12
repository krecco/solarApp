<template>
  <Card class="mb-4">
    <template v-if="title" #title>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i v-if="icon" :class="icon"></i>
          <span>{{ title }}</span>
        </div>
        <slot name="actions"></slot>
      </div>
    </template>
    <template #content>
      <slot></slot>
    </template>
    <template v-if="$slots.footer" #footer>
      <slot name="footer"></slot>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from 'primevue/card'

interface Props {
  title?: string
  icon?: string
}

defineProps<Props>()
</script>
