/**
 * OAuth Helper Utilities
 */

interface OAuthProvider {
  name: string
  clientId: string
  authUrl: string
  scope: string
  responseType: string
}

// OAuth Provider Configurations
const providers: Record<string, OAuthProvider> = {
  google: {
    name: 'Google',
    clientId: import.meta.env.VITE_GOOGLE_CLIENT_ID || '',
    authUrl: 'https://accounts.google.com/o/oauth2/v2/auth',
    scope: 'openid email profile',
    responseType: 'code'
  },
  github: {
    name: 'GitHub',
    clientId: import.meta.env.VITE_GITHUB_CLIENT_ID || '',
    authUrl: 'https://github.com/login/oauth/authorize',
    scope: 'user:email',
    responseType: 'code'
  },
  microsoft: {
    name: 'Microsoft',
    clientId: import.meta.env.VITE_MICROSOFT_CLIENT_ID || '',
    authUrl: 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    scope: 'openid email profile',
    responseType: 'code'
  }
}

/**
 * Generate a random state parameter for CSRF protection
 */
export function generateState(): string {
  const array = new Uint8Array(32)
  crypto.getRandomValues(array)
  return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('')
}

/**
 * Build OAuth authorization URL
 */
export function buildOAuthUrl(provider: string, state?: string): string {
  const config = providers[provider.toLowerCase()]
  
  if (!config) {
    throw new Error(`Unknown OAuth provider: ${provider}`)
  }
  
  if (!config.clientId) {
    throw new Error(`${config.name} OAuth is not configured. Please set VITE_${provider.toUpperCase()}_CLIENT_ID`)
  }
  
  const redirectUri = `${window.location.origin}/auth/${provider}/callback`
  const stateParam = state || generateState()
  
  // Store state in session storage for validation
  const storagePrefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
  sessionStorage.setItem(`${storagePrefix}oauth_state`, stateParam)
  
  const params = new URLSearchParams({
    client_id: config.clientId,
    redirect_uri: redirectUri,
    response_type: config.responseType,
    scope: config.scope,
    state: stateParam,
    // Additional parameters for specific providers
    ...(provider === 'google' && { access_type: 'offline', prompt: 'consent' }),
    ...(provider === 'microsoft' && { response_mode: 'query' })
  })
  
  return `${config.authUrl}?${params.toString()}`
}

/**
 * Parse OAuth callback parameters
 */
export function parseOAuthCallback(urlParams: URLSearchParams): {
  code: string | null
  state: string | null
  error: string | null
  errorDescription: string | null
} {
  return {
    code: urlParams.get('code'),
    state: urlParams.get('state'),
    error: urlParams.get('error'),
    errorDescription: urlParams.get('error_description')
  }
}

/**
 * Validate OAuth state parameter
 */
export function validateOAuthState(state: string): boolean {
  const storagePrefix = import.meta.env.VITE_STORAGE_PREFIX || 'admin_v2_'
  const savedState = sessionStorage.getItem(`${storagePrefix}oauth_state`)
  
  if (!savedState || savedState !== state) {
    return false
  }
  
  // Clear state after validation
  sessionStorage.removeItem(`${storagePrefix}oauth_state`)
  return true
}

/**
 * Get provider configuration
 */
export function getProviderConfig(provider: string): OAuthProvider | null {
  return providers[provider.toLowerCase()] || null
}

/**
 * Check if a provider is configured
 */
export function isProviderConfigured(provider: string): boolean {
  const config = providers[provider.toLowerCase()]
  return !!(config && config.clientId)
}

/**
 * Get list of configured providers
 */
export function getConfiguredProviders(): string[] {
  return Object.keys(providers).filter(provider => isProviderConfigured(provider))
}

/**
 * Build the redirect URL for a provider
 */
export function getRedirectUri(provider: string): string {
  return `${window.location.origin}/auth/${provider}/callback`
}
