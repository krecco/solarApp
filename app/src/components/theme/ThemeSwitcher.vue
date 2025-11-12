<template>
  <div class="theme-switcher flex align-items-center gap-3">
    <!-- Dark Mode Toggle -->
    <div class="flex align-items-center gap-2">
      <label for="darkmode-toggle" class="text-sm font-medium">
        <i :class="isDarkMode ? 'pi pi-moon' : 'pi pi-sun'" class="mr-2"></i>
        {{ isDarkMode ? 'Dark' : 'Light' }}
      </label>
      <InputSwitch
        v-model="isDarkMode"
        input-id="darkmode-toggle"
        @change="toggleDarkMode"
      />
    </div>

    <!-- Theme Selector -->
    <Dropdown
      v-model="currentTheme"
      :options="availableThemes"
      option-label="label"
      option-value="name"
      placeholder="Select Theme"
      class="w-10rem"
      @change="handleThemeChange"
    >
      <template #value="slotProps">
        <div v-if="slotProps.value" class="flex align-items-center gap-2">
          <i class="pi pi-palette"></i>
          <span>{{ getThemeLabel(slotProps.value) }}</span>
        </div>
      </template>
      <template #option="slotProps">
        <div class="flex align-items-center gap-2">
          <i class="pi pi-palette"></i>
          <span>{{ slotProps.option.label }}</span>
        </div>
      </template>
    </Dropdown>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import InputSwitch from 'primevue/inputswitch'
import Dropdown from 'primevue/dropdown'
import { useTheme } from '@/composables/useTheme'

const { 
  isDarkMode, 
  currentTheme, 
  toggleDarkMode, 
  changeTheme, 
  availableThemes 
} = useTheme()

const getThemeLabel = (themeName: string) => {
  const theme = availableThemes.value.find(t => t.name === themeName)
  return theme?.label || themeName
}

const handleThemeChange = (event: any) => {
  changeTheme(event.value)
}
</script>

<style scoped>
.theme-switcher {
  padding: 0.5rem;
  border-radius: 0.5rem;
  background: var(--surface-0);
  border: 1px solid var(--surface-border);
}
</style>