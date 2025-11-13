<template>
  <b-nav-item-dropdown
    id="dropdown-language"
    variant="link"
    right
    toggle-class="nav-link"
  >
    <template #button-content>
      <feather-icon
        icon="GlobeIcon"
        size="21"
      />
    </template>

    <b-dropdown-item
      v-for="locale in availableLocales"
      :key="locale.code"
      :active="currentLocale === locale.code"
      @click="changeLanguage(locale.code)"
    >
      <span class="mr-50">{{ locale.flag }}</span>
      <span>{{ locale.name }}</span>
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>

<script>
import { BNavItemDropdown, BDropdownItem } from 'bootstrap-vue'

export default {
  name: 'LanguageSwitcher',
  components: {
    BNavItemDropdown,
    BDropdownItem,
  },
  data() {
    return {
      availableLocales: [
        { code: 'en', name: 'English', flag: '🇬🇧' },
        { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
        { code: 'es', name: 'Español', flag: '🇪🇸' },
        { code: 'fr', name: 'Français', flag: '🇫🇷' },
        { code: 'si', name: 'සිංහල', flag: '🇱🇰' },
      ],
      currentLocale: this.$i18n.locale,
    }
  },
  methods: {
    async changeLanguage(locale) {
      // Update i18n locale
      this.$i18n.locale = locale
      this.currentLocale = locale

      // Save to localStorage
      localStorage.setItem('userLanguage', locale)

      // Update user preferences in backend if user is logged in
      const userData = JSON.parse(localStorage.getItem('userData') || '{}')
      if (userData && userData.id) {
        try {
          // Call API to update user preferences
          await this.$http.put('/api/settings/preferences', {
            language: locale,
          })

          // Update stored user preferences
          const storedPreferences = JSON.parse(localStorage.getItem('userPreferences') || '{}')
          storedPreferences.language = locale
          localStorage.setItem('userPreferences', JSON.stringify(storedPreferences))
        } catch (error) {
          console.error('Failed to update language preference:', error)
        }
      }

      // Show success toast
      this.$toast({
        component: this.$options.components.ToastificationContent,
        props: {
          title: 'Language Updated',
          text: `Interface language changed to ${this.availableLocales.find(l => l.code === locale)?.name}`,
          variant: 'success',
          icon: 'GlobeIcon',
        },
      })
    },
  },
  mounted() {
    // Load saved language from localStorage or user preferences
    const savedLanguage = localStorage.getItem('userLanguage')
    const userPreferences = JSON.parse(localStorage.getItem('userPreferences') || '{}')
    const preferredLanguage = savedLanguage || userPreferences.language || 'en'

    if (preferredLanguage && preferredLanguage !== this.$i18n.locale) {
      this.$i18n.locale = preferredLanguage
      this.currentLocale = preferredLanguage
    }
  },
}
</script>

<style scoped>
.dropdown-item.active {
  font-weight: bold;
}
</style>
