<template>
  <div class="error-page flex items-center justify-center min-h-screen bg-surface-50 dark:bg-surface-900">
    <div class="text-center p-8">
      <div class="error-icon mb-6">
        <i class="pi pi-server text-9xl text-red-600"></i>
      </div>
      <h1 class="text-6xl font-bold text-surface-700 dark:text-surface-100 mb-4">500</h1>
      <h2 class="text-2xl font-semibold text-surface-600 dark:text-surface-200 mb-4">
        Server Error
      </h2>
      <p class="text-surface-500 dark:text-surface-400 mb-8 max-w-md mx-auto">
        Something went wrong on our end. We're working to fix the issue. Please try again later.
      </p>
      <div class="flex gap-3 justify-center">
        <Button
          label="Refresh Page"
          icon="pi pi-refresh"
          severity="secondary"
          @click="window.location.reload()"
        />
        <Button
          label="Go to Dashboard"
          icon="pi pi-home"
          @click="router.push('/dashboard')"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

const router = useRouter()
</script>

<style scoped>
.error-page {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-icon {
  animation: shake 2s infinite;
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-5px);
  }
  20%, 40%, 60%, 80% {
    transform: translateX(5px);
  }
}
</style>