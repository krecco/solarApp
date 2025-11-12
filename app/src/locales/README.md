# Localization Structure

This directory contains the internationalization (i18n) setup for the Admin Panel V2 application.

## Directory Structure

```
src/locales/
├── types.ts          # TypeScript type definitions for all translations
├── en.ts            # English translations
├── es.ts            # Spanish translations
├── fr.ts            # French translations
├── de.ts            # German translations
├── index.ts         # Main exports and utility functions
└── README.md        # This file
```

## Features

- **Type-safe translations**: All translations are fully typed with TypeScript
- **Lazy loading**: Languages are loaded on-demand for better performance
- **Code splitting**: Each language is in a separate chunk
- **Browser detection**: Automatically detects user's preferred language
- **Persistent selection**: Saves user's language preference to localStorage
- **Date/Number formatting**: Built-in locale-specific formatting
- **RTL support**: Ready for right-to-left languages

## Usage

### In Vue Components

```vue
<template>
  <div>
    <h1>{{ t('common.welcome') }}</h1>
    <p>{{ t('dashboard.totalUsers') }}: {{ n(1234, 'decimal') }}</p>
    <p>{{ d(new Date(), 'short') }}</p>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t, n, d } = useI18n()
</script>
```

### Using the Composable

```vue
<script setup lang="ts">
import { useLocale } from '@/composables/useLocale'

const { 
  t, 
  locale, 
  changeLocale, 
  formatCurrency,
  formatRelativeTime 
} = useLocale()

// Change language
await changeLocale('es')

// Format currency
const price = formatCurrency(99.99) // $99.99 or €99,99 based on locale

// Format relative time
const timeAgo = formatRelativeTime(new Date(Date.now() - 3600000)) // "1 hour ago"
</script>
```

## Adding a New Language

1. Create a new file in `src/locales/` (e.g., `it.ts` for Italian)
2. Copy the structure from `en.ts` and translate all strings
3. Update `types.ts` to add the new locale to the `Locale` type
4. Update `index.ts` to include the new language in the loader
5. Add locale metadata in `index.ts`

Example:

```typescript
// src/locales/it.ts
import type { MessageSchema } from './types'

const it: MessageSchema = {
  common: {
    appName: 'Pannello di Amministrazione V2',
    welcome: 'Benvenuto',
    // ... translate all keys
  },
  // ... all other sections
} as const

export default it
```

## Translation Keys Structure

The translation keys are organized into logical sections:

- **common**: Frequently used terms across the app
- **auth**: Authentication-related strings
- **dashboard**: Dashboard-specific content
- **user**: User management strings
- **header**: Header navigation items
- **sidebar**: Sidebar menu items
- **navigation**: Navigation elements
- **validation**: Form validation messages
- **messages**: Success, error, and confirmation messages
- **settings**: Settings page content
- **commandPalette**: Command palette strings
- **pages**: Page titles

## Best Practices

1. **Keep keys consistent**: Use the same key structure across all languages
2. **Use parameters**: For dynamic content, use parameters: `t('validation.minLength', { min: 5 })`
3. **Avoid HTML in translations**: Keep translations text-only when possible
4. **Test all languages**: Regularly test all language variations
5. **Use TypeScript**: Leverage TypeScript for type safety and autocompletion
6. **Group related keys**: Organize translations by feature/component

## Available Formatting Functions

### Translation
- `t(key, params?)` - Translate a key with optional parameters
- `tm(key)` - Get translation as object
- `rt(message)` - Raw translation
- `te(key)` - Check if translation exists

### Formatting
- `d(date, format)` - Format dates
- `n(number, format)` - Format numbers
- `formatCurrency(amount, currency?)` - Format as currency
- `formatPercent(value, decimals?)` - Format as percentage
- `formatCompactNumber(num)` - Format with abbreviations (1.2K, 3.4M)
- `formatRelativeTime(date)` - Format as relative time ("2 hours ago")

## Environment Variables

Set the default locale in your `.env` file:

```env
VITE_DEFAULT_LOCALE=en
```

## Performance Considerations

- Languages are loaded on-demand using dynamic imports
- Only the selected language bundle is loaded initially
- Subsequent language changes load new bundles asynchronously
- Each language file is code-split into its own chunk

## Migration from Old Structure

If migrating from the old combined structure:

1. The API remains the same - no component changes needed
2. The new setup uses lazy loading for better performance
3. Type safety is improved with TypeScript interfaces
4. Locale switching is now asynchronous

## Troubleshooting

### Language not loading
- Check that the language file exists and exports correctly
- Verify the locale is added to the loader in `index.ts`
- Check browser console for loading errors

### Missing translations
- Enable missing warnings in development: `missingWarn: true`
- Check that all keys exist in all language files
- Use the TypeScript types to ensure consistency

### Formatting issues
- Verify the locale-specific format options in `i18n.ts`
- Check that date/number formats are appropriate for the locale
- Test with various inputs to ensure proper formatting
