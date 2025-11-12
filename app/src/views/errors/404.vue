<template>
  <div class="error-page flex items-center justify-center min-h-screen bg-surface-50 dark:bg-surface-900">
    <div class="text-center p-8">
      <div class="error-icon mb-6">
        <i class="pi pi-exclamation-triangle text-9xl text-orange-500"></i>
      </div>
      <h1 class="text-6xl font-bold text-surface-700 dark:text-surface-100 mb-4">404</h1>
      <h2 class="text-2xl font-semibold text-surface-600 dark:text-surface-200 mb-4">
        Page Not Found
      </h2>
      <p class="text-surface-500 dark:text-surface-400 mb-8 max-w-md mx-auto">
        The page you are looking for doesn't exist or has been moved.
      </p>
      <div class="flex gap-3 justify-center">
        <Button
          label="Go Back"
          icon="pi pi-arrow-left"
          severity="secondary"
          @click="router.back()"
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
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-20px);
  }
  60% {
    transform: translateY(-10px);
  }
}
</style>