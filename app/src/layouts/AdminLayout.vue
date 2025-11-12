<template>
  <div class="admin-layout" :class="layoutClasses">
    <!-- Overlay for mobile sidebar -->
    <Transition name="fade">
      <div
        v-if="appStore.isSidebarMobileOpen"
        class="layout-overlay"
        @click="appStore.toggleSidebarMobile"
      />
    </Transition>

    <!-- Admin Sidebar -->
    <AdminSidebar />

    <!-- Main Content Area -->
    <div class="layout-main-container">
      <!-- Admin Header -->
      <AdminHeader />

      <!-- Breadcrumb -->
      <AdminBreadcrumb />

      <!-- Main Content -->
      <main class="layout-main">
        <div class="layout-content">
          <RouterView v-slot="{ Component, route }">
            <Transition name="fade-slide" mode="out-in">
              <component :is="Component" :key="route.path" />
            </Transition>
          </RouterView>
        </div>
      </main>

      <!-- Admin Footer -->
      <AdminFooter />
    </div>

    <!-- Admin Indicators -->
    <div class="admin-indicators">
      <Tag severity="danger" icon="pi pi-shield" :value="$t('admin.common.adminMode')" />
    </div>

    <!-- Toast Notifications -->
    <Toast position="top-right" />

    <!-- Confirm Dialog -->
    <ConfirmDialog />

    <!-- Dynamic Dialog -->
    <DynamicDialog />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import AdminSidebar from '@/components/admin/layout/AdminSidebar.vue'
import AdminHeader from '@/components/admin/layout/AdminHeader.vue'
import AdminBreadcrumb from '@/components/admin/layout/AdminBreadcrumb.vue'
import AdminFooter from '@/components/admin/layout/AdminFooter.vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import DynamicDialog from 'primevue/dynamicdialog'
import Tag from 'primevue/tag'
import { useAppStore } from '@/stores/app'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const appStore = useAppStore()
const themeStore = useThemeStore()
const authStore = useAuthStore()

// Computed layout classes
const layoutClasses = computed(() => ({
  'layout-sidebar