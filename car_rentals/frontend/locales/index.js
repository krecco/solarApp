// i18n configuration for Car Rentals module
import { createI18n } from 'vue-i18n';
import en from './en.json';
import de from './de.json';
import fr from './fr.json';

const i18n = createI18n({
  legacy: false, // Use Composition API
  locale: 'en', // Default locale
  fallbackLocale: 'en',
  messages: {
    en,
    de,
    fr,
  },
});

export default i18n;
