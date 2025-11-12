<template>
  <div class="table-skeleton">
    <div v-for="i in rows" :key="i" class="skeleton-row">
      <div v-for="j in columns" :key="j" class="skeleton-cell">
        <Skeleton :width="getWidth(j)" height="1.5rem" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Skeleton from 'primevue/skeleton'

interface Props {
  rows?: number
  columns?: number
}

const props = withDefaults(defineProps<Props>(), {
  rows: 10,
  columns: 5
})

const getWidth = (index: number) => {
  // Vary widths for more natural look
  const widths = ['100%', '80%', '90%', '70%', '85%']
  return widths[index % widths.length]
}
</script>

<style lang="scss" scoped>
.table-skeleton {
  width: 100%;

  .skeleton-row {
    display: grid;
    grid-template-columns: repeat(v-bind(columns), 1fr);
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--surface-border);

    &:last-child {
      border-bottom: none;
    }

    .skeleton-cell {
      display: flex;
      align-items: center;
    }
  }
}
</style>
