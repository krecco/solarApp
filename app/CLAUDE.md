# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Vue 3 + PrimeVue admin panel for SaaS applications with JWT authentication, multi-language support, role-based access control, and glassmorphism design. Built with TypeScript, Pinia state management, and modern Vue 3 Composition API patterns.

## Development Commands

### Basic Development
```bash
pnpm install              # Install dependencies (required: Node >= 18, pnpm >= 8)
pnpm dev                  # Start dev server on http://localhost:3000
pnpm build                # Production build (drops console/debugger, minifies with terser)
pnpm preview              # Preview production build
pnpm type-check           # TypeScript type checking with vue-tsc
```

### Testing
```bash
pnpm test                 # Run Vitest unit tests in watch mode
pnpm test:unit            # Same as 'pnpm test'
pnpm test:e2e             # Run Playwright E2E tests
# Coverage thresholds: 80% for statements/branches/functions/lines
```

### Code Quality
```bash
pnpm lint                 # ESLint with auto-fix
pnpm format               # Format with Prettier
pnpm analyze              # Build with bundle analyzer
```

### Notes
- **Package Manager**: Project uses pnpm with workspace configuration
- **Dev Server**: Auto-opens browser, CORS enabled, proxies `/api` to backend
- **Hot Reload**: Vite HMR for instant updates during development

## High-Level Architecture

### 1. API Layer & Global Interceptors

**Critical Pattern: Window-Based Store References**

The API interceptor (`src/api/interceptors.ts`) cannot directly import Pinia stores due to circular dependencies. Instead, it accesses stores via global window references populated during app initialization in `main.ts`:

```typescript
// Populated in main.ts after store creation
window.authStore = useAuthStore()
window.appStore = useAppStore()
window.router = router
```

**Token Management Strategy**
- JWT tokens stored in localStorage (persistent login) OR sessionStorage (temporary login)
- All storage keys use `VITE_STORAGE_PREFIX` (e.g., `admin_v2_token`)
- 401 responses trigger automatic token refresh via `window.authStore.refreshAccessToken()`
- Refresh includes `_retry` flag to prevent infinite loops
- Failed refresh redirects to login via `window.router.push()`

**HTTP Error Handling**
- 404: No logout (allows deprecated endpoint tolerance)
- 401: Auto-refresh attempt once
- 403: Redirect to `/403` error page
- 429: Extract `retry-after` header for rate limiting
- Network errors: Log but don't auto-logout

**API Service Pattern**
```typescript
// Each domain has dedicated service (auth, investment, solarPlant, etc.)
// Services use helper methods from src/api/index.ts
export const investmentService = {
  getAll: () => api.get('/investments'),
  create: (data) => api.post('/investments', data)
}
```

### 2. Authentication Flow & Initialization

**Bootstrap Sequence (main.ts)**
1. Create router
2. Create Pinia stores
3. **Call `authStore.init()`** - Restores auth state from storage BEFORE routing
4. Setup API interceptors with window references
5. Mount app

**Auth Store Critical Methods**
- `init()`: Restores token/user from storage, loads preferences **asynchronously in background**
- `loadPreferences()`: Fetches user preferences (theme, language), updates localStorage + theme store
- `refreshAccessToken()`: Called by interceptor on 401, uses Laravel Sanctum token endpoint
- Storage strategy: Multi-storage (localStorage vs sessionStorage based on "remember me")

**Login Methods Supported**
- Email/password (`/auth/login`)
- OAuth callback (Google, GitHub, Microsoft)
- OTP-based authentication (`/auth/verify-otp`)
- Two-factor verification fallback

**Profile Completion Flow**
After OAuth/registration, check `needs_profile_completion` flag → redirect to profile completion if tenant is missing.

### 3. Router Guards & Email Verification

**Two-Layer Authentication**

```typescript
// src/middleware/authMiddleware.ts
export async function authMiddleware(to, from, next) {
  await ensureAuthInitialized() // Waits for auth store init

  // Check localStorage/sessionStorage directly (not just store)
  const token = localStorage.getItem(`${VITE_STORAGE_PREFIX}token`)
  const user = JSON.parse(localStorage.getItem(`${VITE_STORAGE_PREFIX}user`))

  // Email verification gate - blocks before protected route access
  if (!user.email_verified_at) {
    return next('/auth/verify-email/pending')
  }

  // Role-based redirection
  if (to.path === '/dashboard') {
    return next(`/${user.role}/dashboard`)
  }
}
```

**Important**: Email verification redirect stored in `sessionStorage.setItem('post_verification_redirect')` for post-verification navigation.

### 4. State Management Patterns

**Defensive Computed Properties**

Stores use defensive null checks because computed properties can be accessed before data loads:

```typescript
// src/stores/investment.ts
const pendingInvestments = computed(() =>
  (investments.value || []).filter(i => i.status === 'pending')
)
```

**Auth Store Defensive Role Handling**
- If user role is missing → defaults to 'user'
- If admin role is lost from server → preserves it from localStorage backup
- User preferences sync: `loadPreferences()` checks localStorage cache before API fetch

**Theme Store Special Behavior**
- **Light mode forced**: `setThemeMode()` ignores parameter and always sets light mode
- `syncFromPreferences()` called by auth store when loading user prefs
- `applyPrimaryColor()` sets all 11 CSS variable shades (50-950) + document class

**Store Action Pattern**
```typescript
async function fetchData() {
  loading.value = true
  try {
    const response = await service.getData()
    data.value = response.data
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Failed', detail: error.message })
  } finally {
    loading.value = false
  }
}
```

### 5. Internationalization (i18n)

**Lazy-Loaded Locale Messages**

Locales loaded dynamically via `await import()`, not bundled:

```typescript
// src/plugins/i18n.ts
async function loadLocaleMessages(locale: string) {
  switch (locale) {
    case 'en': return (await import('@/locales/en')).default
    case 'es': return (await import('@/locales/es')).default
    // ...
  }
}
```

**Locale Detection Priority**
1. Saved locale in localStorage (`${VITE_STORAGE_PREFIX}locale`)
2. Environment variable `VITE_DEFAULT_LOCALE`
3. Browser language (`navigator.language.split('-')[0]`)
4. Fallback to English

**Locale Metadata**
Each locale has associated info (flag emoji, native name, date format, currency) in `localeInfo` object. Date/time formats vary per locale (US: MM/DD/YYYY, EU: DD/MM/YYYY).

**Document Language Update**
On locale change: `document.documentElement.lang = locale` and `document.documentElement.dir` for RTL support.

### 6. Theme System & Glassmorphism

**Three-Layer Theming**

1. **PrimeVue Preset** (`src/theme/lara-green.ts`): Uses `definePreset()` to override Lara base theme
2. **CSS Variables** (`src/styles/main.scss`): Root custom properties for primary/surface/text colors
3. **SCSS Utilities** (`src/styles/_glass-morphism.scss`): Reusable `.glass-card` classes

**Dynamic Color Application**

```typescript
// Theme store sets all shade variables programmatically
applyPrimaryColor(color) {
  const shades = { 50: '#...', 100: '#...', ..., 950: '#...' }
  Object.entries(shades).forEach(([shade, value]) => {
    root.style.setProperty(`--primary-${shade}`, value)
  })
  // Also set RGB values for RGBA support
  root.style.setProperty('--primary-color-rgb', hexToRgb(color))
}
```

**Glassmorphism Implementation**
```scss
.glass-card {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.18);
  will-change: backdrop-filter; // Performance optimization
}

@media (prefers-reduced-motion: reduce) {
  .glass-card { backdrop-filter: none; } // Accessibility
}
```

### 7. Component Patterns

**PrimeVue-Only UI Components**

**ALWAYS use PrimeVue components, not plain HTML:**
- Layout: `Card`, `Panel`, `Fieldset`, `Divider`, `Splitter`
- Forms: `InputText`, `Dropdown`, `Calendar`, `Checkbox`, `RadioButton`
- Data: `DataTable`, `Column`, `Tree`, `TreeTable`
- Feedback: `Toast`, `Message`, `ConfirmDialog`
- Overlay: `Dialog`, `Sidebar`, `OverlayPanel`

**PrimeFlex Utility Classes** (never write custom CSS for these):
- Spacing: `p-4`, `m-2`, `mb-4`, `px-3`, `py-2`
- Flexbox: `flex`, `align-items-center`, `justify-content-between`, `gap-3`
- Grid: `grid`, `col-12`, `md:col-6`, `lg:col-4`
- Text: `text-xl`, `font-bold`, `text-color-secondary`

**Form Composable Pattern**

```vue
<script setup lang="ts">
import { useForm } from '@/composables/useForm'

const { values, errors, handleSubmit, isSubmitting } = useForm({
  initialValues: { email: '', password: '' },
  validationSchema: {
    email: (value) => /.+@.+/.test(value) || 'Invalid email',
    password: (value) => value.length >= 8 || 'Min 8 characters'
  }
})

const onSubmit = handleSubmit(async (formData) => {
  await api.post('/endpoint', formData)
})
</script>
```

**Role-Based Access Control**

```vue
<script setup>
import { useRole } from '@/composables/useRole'
const { hasRole } = useRole()
</script>

<template>
  <Button v-if="hasRole('admin')" label="Admin Only" />
  <!-- OR use directive: -->
  <Button v-role="'admin'" label="Admin Only" />
</template>
```

Hierarchical permissions: Admin can access customer areas, customers cannot access admin.

### 8. Path Aliases

Configure in `vite.config.js` and `tsconfig.json`:

```typescript
'@' → './src'
'@components' → './src/components'
'@views' → './src/views'
'@stores' → './src/stores'
'@composables' → './src/composables'
'@utils' → './src/utils'
'@api' → './src/api'
'@layouts' → './src/layouts'
'@assets' → './src/assets'
'@styles' → './src/styles'
'@plugins' → './src/plugins'
'@router' → './src/router'
'@middleware' → './src/middleware'
'@types' → './src/types'
'@widgets' → './src/components/widgets'
```

### 9. Build Configuration

**Production Optimizations**
- Terser minification with `drop_console` and `drop_debugger`
- Manual chunk splitting: `vue-vendor`, `primevue-vendor`, `chart-vendor`, `utils-vendor`, `ui-vendor`
- Gzip compression for files > 10KB
- Source maps only in development mode

**SCSS Auto-Injection**
All Vue components automatically have access to SCSS abstracts:
```scss
// Automatically injected into every component
@use "@styles/abstracts" as *;
```

**Global Constants**
```javascript
__APP_VERSION__  // From package.json version
__BUILD_TIME__   // ISO timestamp of build
```

## Critical Non-Obvious Patterns

### 1. Async Preference Sync
User preferences (theme, language) load **in the background** during auth init, preventing UI blocking. `loadPreferences()` checks localStorage cache first, then fetches from API if needed.

### 2. Email Verification Gate
Router guard redirects to email verification **before** allowing access to protected routes, not after login. Verification status is blocking.

### 3. Storage Prefix Everywhere
Every storage key uses `VITE_STORAGE_PREFIX` for multi-tenant support: `admin_v2_token`, `admin_v2_user`, `admin_v2_locale`, etc.

### 4. Light Mode Forced
Theme store's `setThemeMode()` deliberately ignores dark mode requests and forces light mode only. This appears to be a design constraint.

### 5. Laravel Sanctum Token Handling
Refresh token endpoint returns the same token (no actual refresh), indicating long-lived tokens are used. The "refresh" is more of a validation check.

### 6. SCSS Modern API
Vite config uses `api: 'modern'` and suppresses import deprecation warnings during Sass migration.

## Working in This Codebase

### Before Making Changes
1. **Check auth flow**: Modifications to auth, routing, or interceptors require understanding the window-based store references
2. **Test across roles**: Admin and customer roles have different route access
3. **Verify i18n**: Ensure all locale files (en, es, fr, de) have matching translation keys
4. **Theme compatibility**: Remember light mode is forced, don't add dark mode toggles

### Adding New Features
1. **Create service file** in `src/api/[domain].service.ts`
2. **Add store** in `src/stores/[domain].ts` with loading state + error handling
3. **Create view** in `src/views/[domain]/` using PrimeVue components + PrimeFlex utilities
4. **Update router** in `src/router/index.ts` with meta.requiresRole if needed
5. **Add translations** to all locale files in `src/locales/`

### Common Pitfalls
- **Don't import stores in interceptors** - Use `window.authStore` instead
- **Don't skip locale files** - Missing translation keys cause hydration errors
- **Don't write custom CSS for layouts** - Use PrimeFlex utilities
- **Don't access store state before init** - Use defensive null checks in computed properties
- **Don't forget storage prefix** - All localStorage/sessionStorage keys need `VITE_STORAGE_PREFIX`

## Environment Variables

Key variables in `.env`:
```bash
VITE_API_URL                    # Backend API base URL
VITE_STORAGE_PREFIX             # Storage key prefix (default: admin_v2_)
VITE_ENABLE_SOCIAL_LOGIN        # Enable OAuth providers
VITE_GOOGLE_CLIENT_ID           # Google OAuth client ID
VITE_GITHUB_CLIENT_ID           # GitHub OAuth client ID
VITE_MICROSOFT_CLIENT_ID        # Microsoft OAuth client ID
```

## Backend Integration

**API Expectations**
- Laravel backend with Sanctum authentication
- JWT tokens in Authorization header: `Bearer {token}`
- User object format: `{ id, name, email, email_verified_at, role, tenant_id }`
- Preferences endpoint: `/user/preferences` (GET/POST)
- Refresh token endpoint: `/auth/refresh` (returns same token)

**Response Format**
Most endpoints return:
```json
{
  "data": { ... },
  "message": "Success",
  "meta": { "pagination": { ... } }
}
```

Service methods unwrap `response.data` or `response.data.data` automatically.
