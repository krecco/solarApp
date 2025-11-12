// Middleware to ensure auth store is initialized before route checks
import { useAuthStore } from '@/stores/auth'

let authInitialized = false
let authInitPromise: Promise<void> | null = null

export async function ensureAuthInitialized(): Promise<void> {
  if (authInitialized) {
    return
  }
  
  if (authInitPromise) {
    return authInitPromise
  }
  
  authInitPromise = new Promise(async (resolve) => {
    const authStore = useAuthStore()
    
    if (!authStore.user && !authStore.token) {
      // Auth store not initialized yet, initialize it
      await authStore.init()
    }
    
    authInitialized = true
    resolve()
  })
  
  return authInitPromise
}

export function resetAuthInitialization() {
  authInitialized = false
  authInitPromise = null
}
