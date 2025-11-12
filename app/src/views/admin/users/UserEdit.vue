<template>
  <div class="page-container">
    <div class="empty-state">
      <i class="pi pi-info-circle empty-icon text-primary-500"></i>
      <h3 class="empty-title">{{ $t('admin.users.redirecting') }}</h3>
      <p class="empty-subtitle">{{ $t('admin.users.editUserViaList') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

onMounted(() => {
  // Redirect to user detail page where edit button will open sidebar
  const userId = route.params.id
  router.replace(`/admin/users/${userId}`)
})
</script>

<style scoped lang="scss">
/* Use utility classes from _layouts.scss for empty-state */
</style>
