<template>
  <div class="page-container">
    <!-- Modern Page Header with Back Button -->
    <div class="page-header-modern">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <Button
              icon="pi pi-arrow-left"
              severity="secondary"
              text
              rounded
              @click="router.push({ name: 'AdminSolarPlantDetail', params: { id: plantId } })"
              v-tooltip.top="'Back'"
              class="back-btn"
            />
            <h1 class="header-title">
              <i class="pi pi-sun"></i>
              Edit Solar Plant
            </h1>
          </div>
          <p class="header-subtitle" v-if="store.currentPlant">
            Editing: {{ store.currentPlant.title }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="store.loading && !store.currentPlant" class="flex justify-content-center p-5">
      <ProgressSpinner />
    </div>

    <Card v-else-if="store.currentPlant">
      <template #content>
        <p class="text-gray-500 mb-4">
          Editing: <strong>{{ store.currentPlant.title }}</strong>
        </p>
        <!-- Reuse the same form structure as SolarPlantCreate -->
        <!-- For brevity, using simplified version -->
        <p class="text-center p-4">
          Edit form implementation here (similar to Create form)
        </p>
        <Button
          label="Back to Details"
          icon="pi pi-arrow-left"
          @click="router.push({ name: 'AdminSolarPlantDetail', params: { id: plantId } })"
        />
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import Button from 'primevue/button'
import Card from 'primevue/card'
import ProgressSpinner from 'primevue/progressspinner'

const route = useRoute()
const router = useRouter()
const store = useSolarPlantStore()

const plantId = computed(() => route.params.id as string)

onMounted(async () => {
  await store.fetchPlant(plantId.value)
})
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';
</style>
