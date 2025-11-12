/**
 * User Data Transformation Utilities
 *
 * Transforms API responses to match frontend expectations
 */

import type { AdminUser } from '@/api/admin.service'

export interface TransformedUser {
  id: number
  name: string
  email: string
  avatar_url?: string | null
  role: string
  roles: string[]
  email_verified_at?: string
  emailVerified: boolean
  last_login_at?: string
  created_at: string
  createdAt: string
  updated_at: string
  updatedAt: string
  status: string
  phone?: string
  lastLoginAt?: string
}

/**
 * Transform API user response to frontend format
 */
export function transformUser(apiUser: AdminUser | any): TransformedUser {
  // Extract first role from roles array
  const role = Array.isArray(apiUser.roles) && apiUser.roles.length > 0
    ? apiUser.roles[0]
    : 'user'

  // Check if email is verified
  const emailVerified = !!apiUser.email_verified_at

  return {
    id: apiUser.id,
    name: apiUser.name,
    email: apiUser.email,
    avatar_url: apiUser.avatar_url,
    role: role,
    roles: apiUser.roles || [role],
    email_verified_at: apiUser.email_verified_at,
    emailVerified: emailVerified,
    last_login_at: apiUser.last_login_at,
    created_at: apiUser.created_at,
    createdAt: apiUser.created_at,
    updated_at: apiUser.updated_at,
    updatedAt: apiUser.updated_at,
    status: apiUser.status || 'active',
    phone: apiUser.phone,
    lastLoginAt: apiUser.lastLoginAt || apiUser.last_login_at
  }
}

/**
 * Transform array of API users
 */
export function transformUsers(apiUsers: AdminUser[]): TransformedUser[] {
  return apiUsers.map(transformUser)
}

/**
 * Transform user data for API submission (reverse transformation)
 * Converts frontend format back to API format
 */
export function prepareUserForApi(user: Partial<TransformedUser>): any {
  const apiData: any = {}

  if (user.name !== undefined) apiData.name = user.name
  if (user.email !== undefined) apiData.email = user.email
  if (user.phone !== undefined) apiData.phone = user.phone
  if (user.role !== undefined) apiData.role = user.role
  if (user.status !== undefined) apiData.status = user.status

  // Convert emailVerified boolean to email_verified_at
  if (user.emailVerified !== undefined) {
    apiData.email_verified_at = user.emailVerified ? new Date().toISOString() : null
  }

  return apiData
}
