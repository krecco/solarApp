/**
 * Main application entry point
 * Updated to use modular PrimeVue configuration for better maintainability
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'

// Import PrimeVue styles
import 'primeicons/primeicons.css'
import 'primeflex/primeflex.css'

// Import global styles
import './styles/main.scss'

// Import App and Router
import App from './App.vue'
import router from './router'

// Import custom plugins
import { setupI18n } from './plugins/i18n'
import { setupPrimeVue } from './plugins/primevue'
import { setupInterceptors } from './api/interceptors'
import { initializeTheme } from './theme/utils'

// Import global styles AFTER PrimeVue setup to avoid conflicts
// This will be imported after PrimeVue theme CSS is generated

// Async initialization to support i18n lazy loading
async function initializeApp() {
  try {
    // Create Vue app
    const app = createApp(App)

    // Create and use Pinia store first
    const pinia = createPinia()
    app.use(pinia)

    // Initialize i18n asynchronously BEFORE using it
    console.log('🌍 Initializing i18n...')
    const i18n = await setupI18n()
    app.use(i18n)
    console.log(`✅ i18n initialized with locale: ${i18n.global.locale.value}`)

    // Use router
    app.use(router)
    
    // Setup PrimeVue with all services and directives
    // Configuration is now externalized for better maintainability
    setupPrimeVue(app)

    // Setup API interceptors
    setupInterceptors()

    // Initialize auth store to restore authentication state
    console.log('🔐 Initializing authentication...')
    const { useAuthStore } = await import('./stores/auth')
    const authStore = useAuthStore()
    await authStore.init()
    console.log(`✅ Authentication initialized. Authenticated: ${authStore.isAuthenticated}`)

    // Initialize theme (dark mode and preset from localStorage)
    initializeTheme()

    // Global error handler
    app.config.errorHandler = (err, instance, info) => {
      console.error('Global error:', err)
      console.error('Component:', instance)
      console.error('Error info:', info)
      
      // You can integrate with error tracking services here
      // e.g., Sentry, LogRocket, etc.
    }

    // Performance monitoring
    if (import.meta.env.PROD) {
      // Add performance monitoring in production
      window.addEventListener('load', () => {
        if ('performance' in window) {
          const perfData = window.performance.timing
          const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart
          console.log(`📊 Page load time: ${pageLoadTime}ms`)
        }
      })
    } else {
      // Enable performance monitoring in development
      app.config.performance = true
    }

    // Mount app
    app.mount('#app')
    
    // Log successful initialization
    console.log('✅ Admin Panel V2 initialized successfully')
    console.log(`🌍 Locale: ${i18n.global.locale.value}`)
    console.log(`🎨 Theme: ${document.documentElement.dataset.theme || 'auto'}`)
    
  } catch (error) {
    console.error('❌ Failed to initialize application:', error)
    throw error
  }
}

// Initialize the application with error handling
initializeApp().catch(error => {
  console.error('💥 Critical initialization error:', error)
  
  // Show user-friendly error message
  const appElement = document.getElementById('app')
  if (appElement) {
    appElement.innerHTML = `
      <div style="
        display: flex; 
        justify-content: center; 
        align-items: center; 
        height: 100vh; 
        font-family: system-ui, -apple-system, sans-serif;
        flex-direction: column;
        gap: 1.5rem;
        padding: 2rem;
        text-align: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
      ">
        <div style="
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(10px);
          border-radius: 1rem;
          padding: 2rem;
          border: 1px solid rgba(255, 255, 255, 0.2);
          max-width: 600px;
        ">
          <h1 style="color: #ff6b6b; margin: 0 0 1rem 0; font-size: 2rem;">⚠️ Application Error</h1>
          <p style="color: rgba(255, 255, 255, 0.9); margin: 0 0 2rem 0; line-height: 1.6;">
            Failed to initialize the admin panel. This could be due to a network issue, browser compatibility, 
            or a temporary server problem. Please try refreshing the page or contact support if the issue persists.
          </p>
          <button 
            onclick="location.reload()" 
            style="
              padding: 0.75rem 2rem; 
              background: #4ecdc4; 
              color: white; 
              border: none; 
              border-radius: 0.5rem; 
              cursor: pointer;
              font-size: 1rem;
              font-weight: 600;
              transition: all 0.3s ease;
              margin-right: 1rem;
            "
            onmouseover="this.style.background='#45b7b8'"
            onmouseout="this.style.background='#4ecdc4'"
          >
            🔄 Refresh Page
          </button>
          <details style="margin-top: 2rem; text-align: left; color: rgba(255, 255, 255, 0.8);">
            <summary style="cursor: pointer; margin-bottom: 1rem; font-weight: 600;">🔧 Technical Details</summary>
            <pre style="
              margin: 0; 
              padding: 1rem; 
              background: rgba(0, 0, 0, 0.3); 
              border-radius: 0.5rem; 
              overflow-x: auto;
              font-size: 0.875rem;
              color: #fff;
              white-space: pre-wrap;
              word-wrap: break-word;
            ">${String(error)}</pre>
          </details>
        </div>
      </div>
    `
  }
})

// Register service worker for PWA (if needed)
if ('serviceWorker' in navigator && import.meta.env.PROD) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').then(
      registration => {
        console.log('🔧 Service Worker registered:', registration)
      },
      error => {
        console.log('❌ Service Worker registration failed:', error)
      }
    )
  })
}
