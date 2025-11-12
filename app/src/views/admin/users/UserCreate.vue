<template>
  <div class="page-container">
    <div class="empty-state">
      <i class="pi pi-info-circle empty-icon text-primary-500"></i>
      <h3 class="empty-title">{{ $t('admin.users.redirecting') }}</h3>
      <p class="empty-subtitle">{{ $t('admin.users.createUserViaList') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

onMounted(() => {
  // Redirect to users list where the create sidebar will be used
  router.replace('/admin/users')
})
</script>

<style scoped lang="scss">
/* Use utility classes from _layouts.scss for empty-state */
</style>
