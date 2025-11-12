<template>
  <div class="locale-switcher">
    <Dropdown
      v-model="currentLocale"
      :options="localeOptions"
      optionLabel="label"
      optionValue="value"
      :loading="isChangingLocale"
      :disabled="isChangingLocale"
      @change="handleLocaleChange"
      :pt="{
        root: { class: 'locale-dropdown' },
        input: { class: 'locale-input' },
        panel: { class: 'locale-panel' }
      }"
    >
      <template #value="{ value, placeholder }">
        <div v-if="value" class="flex items-center gap-2">
          <span class="text-xl">{{ getLocaleFlag(value) }}</span>
          <span class="hidden sm:inline">{{ getLocaleName(value) }}</span>
        </div>
        <span v-else>{{ placeholder }}</span>
      </template>
      
      <template #option="{ option }">
        <div class="flex items-center gap-3 py-1">
          <span class="text-xl">{{ option.flag }}</span>
          <div class="flex-1">
            <div class="font-medium">{{ option.nativeName }}</div>
            <div class="text-xs text-muted-foreground">{{ option.label }}</div>
          </div>
        </div>
      </template>
    </Dropdown>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useLocale } from '@/composables/useLocale'
import Dropdown from 'primevue/dropdown'
import type { Locale } from '@/locales/types'

// Props
interface Props {
  showLabel?: boolean
  showFlag?: boolean
  size?: 'small' | 'medium' | 'large'
}

const props = withDefaults(defineProps<Props>(), {
  showLabel: true,
  showFlag: true,
  size: 'medium'
})

// Emits
const emit = defineEmits<{
  'locale-changed': [locale: Locale]
}>()

// Composables
const { locale, availableLocales, changeLocale, isChangingLocale } = useLocale()

// State
const currentLocale = ref(locale.value)

// Computed
const localeOptions = computed(() => 
  availableLocales.value.map(loc => ({
    value: loc.code,
    label: loc.name,
    nativeName: loc.nativeName,
    flag: loc.flag
  }))
)

// Methods
const getLocaleFlag = (code: string) => {
  const locale = availableLocales.value.find(l => l.code === code)
  return locale?.flag || '🌐'
}

const getLocaleName = (code: string) => {
  const locale = availableLocales.value.find(l => l.code === code)
  return locale?.nativeName || code
}

const handleLocaleChange = async (event: any) => {
  const newLocale = event.value as Locale
  
  try {
    await changeLocale(newLocale)
    emit('locale-changed', newLocale)
    
    // Optional: Show success notification
    // toast.success(t('messages.success.localeChanged'))
  } catch (error) {
    console.error('Failed to change locale:', error)
    // Revert selection on error
    currentLocale.value = locale.value
    
    // Optional: Show error notification
    // toast.error(t('messages.error.localeChangeFailed'))
  }
}
</script>

<style scoped>
.locale-switcher {
  display: inline-block;
}

.locale-dropdown {
  min-width: 120px;
}

.locale-input {
  padding-right: 2.5rem;
}

.locale-panel {
  min-width: 200px;
}

/* Size variations */
.locale-switcher[data-size="small"] .locale-dropdown {
  font-size: 0.875rem;
}

.locale-switcher[data-size="large"] .locale-dropdown {
  font-size: 1.125rem;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .locale-panel {
    background-color: var(--surface-900);
    border-color: var(--surface-700);
  }
}
</style>
