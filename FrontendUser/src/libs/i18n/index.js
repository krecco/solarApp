import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

function loadLocaleMessages() {
  const locales = require.context('./locales', true, /[A-Za-z0-9-_,\s]+\.json$/i)
  const messages = {}
  locales.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i)
    if (matched && matched.length > 1) {
      const locale = matched[1]
      messages[locale] = locales(key)
    }
  })
  return messages
}

// Get user's preferred language
function getUserLanguage() {
  // First check localStorage for saved language preference
  const savedLanguage = localStorage.getItem('userLanguage')
  if (savedLanguage) {
    return savedLanguage
  }

  // Check user preferences from stored user data
  try {
    const userPreferences = JSON.parse(localStorage.getItem('userPreferences') || '{}')
    if (userPreferences.language) {
      return userPreferences.language
    }
  } catch (error) {
    console.error('Error parsing user preferences:', error)
  }

  // Fallback to browser language or English
  const browserLang = navigator.language.split('-')[0]
  const availableLanguages = ['en', 'de', 'es', 'fr', 'si']
  return availableLanguages.includes(browserLang) ? browserLang : 'en'
}

export default new VueI18n({
  locale: getUserLanguage(),
  fallbackLocale: 'en',
  messages: loadLocaleMessages(),
})
