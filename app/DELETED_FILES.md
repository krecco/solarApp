# Deleted Files Log

**Date**: 2025-11-07
**Reason**: Cleanup of orphaned API services and stores from incomplete refactoring

---

## Phase 2: Additional Cleanup (Billing/Plans/Tenant Services & Stores)

**Date**: 2025-11-07 (Second cleanup pass)
**Reason**: Removed services and stores that were only imported but never used in any UI components

### Deleted API Services (3 files)

#### 1. `src/api/billing.service.ts`
- **Status**: Imported in store, but store never used by any component
- **Reason**: No billing/subscription pages exist in this basic admin app
- **Contained Methods**:
  - `getBillingOverview()` - Stripe billing details
  - `updateSubscription()`, `cancelSubscription()`, `resumeSubscription()`
  - `updatePaymentMethod()`, `downloadInvoice()`, `listInvoices()`
  - `getPortalSession()` - Stripe customer portal
- **Impact**: None - no billing routes or UI pages exist

#### 2. `src/api/plans.service.ts`
- **Status**: Imported in store, but store never used by any component
- **Reason**: No pricing/plans pages exist in this basic admin app
- **Contained Methods**:
  - `list()` - list all pricing plans
  - `get()` - get specific plan details
  - `compare()` - compare multiple plans
- **Impact**: None - no plans routes or UI pages exist

#### 3. `src/api/my-tenant.service.ts`
- **Status**: Imported in store, but store never used by any component
- **Reason**: No tenant management pages exist for end users
- **Contained Methods**:
  - `getMyTenant()` - get current tenant info
  - `updateMyTenant()` - update tenant details
  - `getDeploymentInfo()` - database/resource info
- **Impact**: None - no tenant routes or UI pages exist

### Deleted Store Files (4 files)

#### 1. `src/stores/billing.ts`
- **Status**: Never imported by any Vue component
- **Imported**: `billing.service.ts`
- **Actions**: fetchBillingOverview, updateSubscription, cancelSubscription, etc.
- **Impact**: None - no components use `useBillingStore()`

#### 2. `src/stores/plans.ts`
- **Status**: Never imported by any Vue component
- **Imported**: `plans.service.ts`
- **Actions**: fetchPlans, fetchPlan, comparePlans
- **Impact**: None - no components use `usePlansStore()`

#### 3. `src/stores/my-tenant.ts`
- **Status**: Never imported by any Vue component
- **Imported**: `my-tenant.service.ts`
- **Actions**: fetchMyTenant, updateMyTenant, fetchDeploymentInfo
- **Impact**: None - no components use `useMyTenantStore()`

#### 4. `src/stores/subscription.ts`
- **Status**: Never imported by any Vue component
- **Imported**: `useBillingStore` (wrapper around billing store)
- **Actions**: fetchCurrentPlan, changePlan, etc. (delegates to billingStore)
- **Impact**: None - no components use `useSubscriptionStore()`

### Verification

**Routes Check**: No routes exist for `/billing`, `/plans`, `/subscription`, or `/tenant`

**Sidebar Check**: Only 2 menu items exist - Dashboard and Users

**Component Usage**: Zero Vue components import any of these 4 stores

---

## Phase 1: Initial Cleanup (Monitoring/Profile/Tenant-API Services)

**Date**: 2025-11-07 (First cleanup pass)

## Deleted API Services (4 files)

### 1. `src/api/admin-monitoring.service.ts`
- **Status**: Orphaned (never imported)
- **Reason**: Complete duplication of functionality already in `admin.service.ts`
- **Contained Methods**:
  - `getSubscriptionEvents()` - duplicated in adminService
  - `getProvisioningJobs()` - duplicated in adminService
  - `getApiKeys()`, `createApiKey()`, `deleteApiKey()` - duplicated in adminService
  - `getFailedJobs()`, `retryFailedJob()` - monitoring-specific but unused
- **Impact**: None - no references found in codebase

---

### 2. `src/api/subscription.service.ts`
- **Status**: Orphaned (never imported)
- **Reason**: Redundant with `billing.service.ts` - subscription store uses billing service instead
- **Contained Methods**:
  - `getCurrent()` - get current subscription
  - `changePlan()` - change subscription plan
  - `getUsage()` - subscription usage tracking
  - `getActivity()` - subscription activity history
  - `previewChange()` - preview plan changes
  - `checkFeature()` - feature availability checking
  - `getUpgradeOptions()` - upgrade paths
  - `pause()`, `resume()` - pause/resume subscription
- **Note**: Advanced subscription management features appear to have been simplified to basic billing operations
- **Impact**: None - subscription store uses `billingService` instead

---

### 3. `src/api/profile.service.ts`
- **Status**: Orphaned (never imported)
- **Reason**: Not integrated into any store or component
- **Contained Methods**:
  - `updateProfile()` - update user profile
  - `updatePassword()` - change user password
- **API Endpoints**: `PUT /api/v1/profile`, `PUT /api/v1/profile/password`
- **Impact**: None - profile functionality appears to be handled through other mechanisms

---

### 4. `src/api/tenant-api.service.ts`
- **Status**: Orphaned (never imported)
- **Reason**: Multi-tenant API feature that was never integrated
- **Contained Methods**:
  - `getTenant()` - get tenant information
  - `getTenantSubscription()` - get tenant subscription
  - `getTenantFeatures()` - get tenant features
- **API Endpoints**: `/api/v1/tenant-api/tenant/{id}/*`
- **Impact**: None - multi-tenant architecture not implemented
- **Note**: Appears to be leftover from a more complex multi-tenant SaaS architecture

---

## Remaining API Services (4 active)

### Core Services (Actually in use)
1. ✅ `admin.service.ts` - Admin dashboard management (used by admin store)
2. ✅ `auth.service.ts` - Authentication and registration (used by auth store)
3. ✅ `notifications.service.ts` - User notifications (used by notifications store)
4. ✅ `preferences.service.ts` - User preferences (used during auth initialization)

### Supporting Files
- `index.ts` - Service exports barrel file
- `interceptors.ts` - HTTP interceptors

## Remaining Store Files (6 active)

1. ✅ `admin.ts` - Admin dashboard and user management (used by admin views)
2. ✅ `auth.ts` - Authentication state (used by auth views and guards)
3. ✅ `notifications.ts` - Notifications management (used by notification components)
4. ✅ `theme.ts` - Theme management (used by layout components)
5. ✅ `users.ts` - User management (used by admin user views)
6. ✅ `app.ts` - Application state

---

## Analysis Summary

This two-phase cleanup removes services and stores that are remnants from a more complex multi-tenant SaaS application that was simplified to a basic admin dashboard.

### Phase 1 Issues:
- **Duplication**: admin-monitoring.service.ts duplicated admin.service.ts methods
- **Abandonment**: subscription.service.ts was replaced by billing.service.ts but never removed
- **Non-integration**: profile.service.ts and tenant-api.service.ts were never wired into the application

### Phase 2 Issues:
- **Dead chain**: Services → Stores → [Nothing uses them]
- **No UI pages**: Zero routes exist for billing, plans, subscription, or tenant features
- **Sidebar reality**: Only Dashboard and Users menu items exist
- **Component usage**: Zero Vue components import billing/plans/my-tenant/subscription stores

### Total Cleanup:
- **11 files deleted** (7 services + 4 stores)
- **~700 lines of code removed**
- **Zero breaking changes** - all deleted files had zero actual usage in UI layer

---

## Verification Commands

To verify no references remain:
```bash
# Phase 1 deleted services
grep -r "admin-monitoring" app/src/
grep -r "subscription.service" app/src/
grep -r "profile.service" app/src/
grep -r "tenant-api" app/src/

# Phase 2 deleted services
grep -r "billing.service" app/src/
grep -r "plans.service" app/src/
grep -r "my-tenant.service" app/src/

# Phase 2 deleted stores
grep -r "useBillingStore" app/src/
grep -r "usePlansStore" app/src/
grep -r "useMyTenantStore" app/src/
grep -r "useSubscriptionStore" app/src/
```

All searches should return no results (except this documentation file and the index.ts barrel file).
