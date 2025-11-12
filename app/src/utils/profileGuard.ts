/**
 * Profile Completion Guard Utilities
 * Simplified version without tenant requirements
 */

import type { Router, RouteLocationNormalized } from 'vue-router'
import type { User } from '@/stores/auth'

/**
 * Routes that are allowed even without authentication
 */
const ALLOWED_ROUTES = [
  'Login',
  'Register',
  'SimpleRegister',
  'OAuthCallback',
  'Logout',
  'ForgotPassword',
  'ResetPassword',
  'Terms',
  'Privacy',
  'Support'
]

/**
 * Check if user has completed their profile
 * In the simplified system, users just need to be registered and verified
 */
export function checkProfileCompletion(user: User | null): boolean {
  if (!user) return false

  // Profile is complete if user exists, has name and email
  return !!(user.name && user.email)
}

/**
 * Check if the user needs to complete their profile
 */
export function needsProfileCompletion(user: User | null): boolean {
  if (!user) return false

  // User is logged in but missing basic info
  return !checkProfileCompletion(user)
}

/**
 * Redirect to profile setup page
 */
export function redirectToProfileSetup(router: Router): void {
  router.push({
    name: 'ProfileSettings',
    query: { redirect: router.currentRoute.value.fullPath }
  })
}

/**
 * Check if a route name is for profile-related pages
 */
export function isProfileRoute(routeName: string | null | undefined): boolean {
  if (!routeName) return false

  return routeName === 'ProfileSettings' ||
         routeName === 'AccountSettings'
}

/**
 * Check if a route is allowed without profile completion
 */
export function isRouteAllowedWithoutProfile(routeName: string | null | undefined): boolean {
  if (!routeName) return false

  return ALLOWED_ROUTES.includes(routeName)
}

/**
 * Navigation guard for profile completion
 */
export function profileCompletionGuard(
  to: RouteLocationNormalized,
  user: User | null,
  needsCompletion: boolean
): { allowed: boolean; redirect?: string } {
  const toRouteName = to.name as string

  // No user, let auth guard handle it
  if (!user) {
    return { allowed: true }
  }

  // User needs profile completion
  if (needsCompletion) {
    // Already going to profile settings
    if (isProfileRoute(toRouteName)) {
      return { allowed: true }
    }

    // Check if route is allowed without profile
    if (isRouteAllowedWithoutProfile(toRouteName)) {
      return { allowed: true }
    }

    // Redirect to profile settings
    return {
      allowed: false,
      redirect: '/profile/settings'
    }
  }

  return { allowed: true }
}

/**
 * Get profile completion percentage
 */
export function getProfileCompletionPercentage(user: User | null): number {
  if (!user) return 0

  let completed = 0
  const total = 3

  // Basic user info
  if (user.name) completed++
  if (user.email) completed++
  if (user.email_verified_at) completed++

  return Math.round((completed / total) * 100)
}

/**
 * Get missing profile fields
 */
export function getMissingProfileFields(user: User | null): string[] {
  if (!user) return ['All fields']

  const missing: string[] = []

  if (!user.name) missing.push('Name')
  if (!user.email) missing.push('Email')
  if (!user.email_verified_at) missing.push('Email Verification')

  return missing
}
