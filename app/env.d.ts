/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_APP_TITLE: string
  readonly VITE_APP_VERSION: string
  readonly VITE_APP_ENV: string
  readonly VITE_API_BASE_URL: string
  readonly VITE_API_TIMEOUT: string
  readonly VITE_API_VERSION: string
  readonly VITE_AUTH_TOKEN_KEY: string
  readonly VITE_AUTH_REFRESH_TOKEN_KEY: string
  readonly VITE_AUTH_TOKEN_EXPIRES_IN: string
  readonly VITE_AUTH_REFRESH_TOKEN_EXPIRES_IN: string
  readonly VITE_ENABLE_PWA: string
  readonly VITE_ENABLE_ANALYTICS: string
  readonly VITE_ENABLE_SENTRY: string
  readonly VITE_ENABLE_MOCK: string
  readonly VITE_SENTRY_DSN: string
  readonly VITE_GA_MEASUREMENT_ID: string
  readonly VITE_STRIPE_PUBLISHABLE_KEY: string
  readonly VITE_WS_URL: string
  readonly VITE_WS_RECONNECT_INTERVAL: string
  readonly VITE_STORAGE_PREFIX: string
  readonly VITE_DEFAULT_THEME: string
  readonly VITE_DEFAULT_LOCALE: string
  readonly VITE_ENABLE_ENCRYPTION: string
  readonly VITE_ENCRYPTION_KEY: string
  readonly VITE_ENABLE_DEVTOOLS: string
  readonly VITE_ENABLE_CONSOLE_LOGS: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}

declare const __APP_VERSION__: string
declare const __BUILD_TIME__: string