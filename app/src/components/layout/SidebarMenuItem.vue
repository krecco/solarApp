<template>
  <div class="sidebar-menu-item">
    <!-- Single Item or Parent Item -->
    <router-link
      v-if="item.to && !item.items"
      v-slot="{ isActive, isExactActive }"
      :to="item.to"
      custom
    >
      <a
        :href="item.to"
        class="menu-link"
        :class="{ 
          'active': isActive || isExactActive || active,
          'collapsed': collapsed 
        }"
        @click="navigate($event, item.to)"
      >
        <i :class="item.icon" class="menu-icon"></i>
        <Transition name="fade">
          <span v-if="!collapsed" class="menu-label">{{ t(item.label) }}</span>
        </Transition>
        <div v-if="item.badge && !collapsed" class="menu-badge-wrapper">
          <Badge :value="item.badge" :severity="getBadgeSeverity(item.badgeClass)" />
        </div>
      </a>
    </router-link>
    
    <!-- Parent Item with Children -->
    <a
      v-else-if="item.items"
      href="#"
      class="menu-link"
      :class="{ 
        'active': active,
        'collapsed': collapsed,
        'expanded': isExpanded 
      }"
      @click="toggleExpanded"
    >
      <i :class="item.icon" class="menu-icon"></i>
      <Transition name="fade">
        <span v-if="!collapsed" class="menu-label">{{ t(item.label) }}</span>
      </Transition>
      <i
        v-if="!collapsed"
        class="pi menu-chevron"
        :class="isExpanded ? 'pi-chevron-down' : 'pi-chevron-right'"
      ></i>
    </a>
    
    <!-- Child Items -->
    <Transition name="slide">
      <ul v-if="item.items && isExpanded && !collapsed" class="menu-children">
        <li v-for="child in item.items" :key="child.key" class="menu-child">
          <router-link
            v-slot="{ isActive, isExactActive }"
            :to="child.to"
            custom
          >
            <a
              :href="child.to"
              class="child-link"
              :class="{ 'active': isActive || isExactActive }"
              @click="navigate($event, child.to)"
            >
              <i :class="child.icon" class="child-icon"></i>
              <span class="child-label">{{ t(child.label) }}</span>
              <div v-if="child.badge" class="child-badge-wrapper">
                <Badge :value="child.badge" :severity="getBadgeSeverity(child.badgeClass)" />
              </div>
            </a>
          </router-link>
        </li>
      </ul>
    </Transition>
    
    <!-- Tooltip for Collapsed State -->
    <div 
      v-if="collapsed && !item.items"
      v-tooltip.right="tooltipText"
      class="menu-tooltip-trigger"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useLocale } from '@/composables/useLocale'
import Badge from 'primevue/badge'
import Button from 'primevue/button'

interface MenuItem {
  key: string
  label: string
  icon: string
  to?: string
  items?: MenuItem[]
  badge?: string
  badgeClass?: string
  isFavorite?: boolean
}

interface Props {
  item: MenuItem
  collapsed: boolean
  active: boolean
}

const props = defineProps<Props>()

const router = useRouter()
const route = useRoute()
const { t } = useLocale()

// State
const isExpanded = ref(false)

// Computed
const tooltipText = computed(() => t(props.item.label))

// Helper function to map badge class to severity
const getBadgeSeverity = (badgeClass?: string) => {
  if (!badgeClass) return 'secondary'
  
  const severityMap: Record<string, string> = {
    'p-badge-info': 'info',
    'p-badge-success': 'success',
    'p-badge-warning': 'warning',
    'p-badge-danger': 'danger',
    'p-badge-help': 'help',
    'p-badge-secondary': 'secondary'
  }
  
  return severityMap[badgeClass] || 'secondary'
}

// Watch for active state changes
watch(() => props.active, (newValue) => {
  if (newValue && props.item.items) {
    isExpanded.value = true
  }
})

// Check if any child is active on mount
if (props.item.items) {
  const hasActiveChild = props.item.items.some(child => 
    child.to && (route.path === child.to || route.path.startsWith(child.to + '/'))
  )
  if (hasActiveChild) {
    isExpanded.value = true
  }
}

// Methods
const toggleExpanded = (event: Event) => {
  event.preventDefault()
  isExpanded.value = !isExpanded.value
}

const navigate = (event: Event, to: string) => {
  event.preventDefault()
  router.push(to)
}
</script>

<style lang="scss" scoped>
.sidebar-menu-item {
  position: relative;
  
  // Main menu link
  .menu-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--text-color-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    border-radius: 0.5rem;
    margin: 0.125rem 0.5rem;
    
    &:hover {
      background: var(--surface-hover);
      color: var(--text-color);
    }
    
    &.active {
      background: var(--primary-color-alpha);
      color: var(--primary-color);
      font-weight: 600;
      
      &::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 70%;
        background: var(--primary-color);
        border-radius: 0 2px 2px 0;
      }
      
      .menu-icon {
        color: var(--primary-color);
      }
    }
    
    &.collapsed {
      justify-content: center;
      padding: 0.75rem;
      
      .menu-icon {
        margin: 0;
      }
    }
    
    .menu-icon {
      font-size: 1.125rem;
      margin-right: 0.75rem;
      flex-shrink: 0;
      width: 1.125rem;
      text-align: center;
    }
    
    .menu-label {
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .menu-chevron {
      font-size: 0.75rem;
      margin-left: auto;
      transition: transform 0.2s ease;
    }
    
    .menu-badge-wrapper {
      margin-left: auto;
      flex-shrink: 0;

      :deep(.p-badge) {
        min-width: 1.5rem;
        height: 1.5rem;
        line-height: 1.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.75rem;
      }
    }
  }
  
  // Child menu items
  .menu-children {
    list-style: none;
    padding: 0;
    margin: 0;
    overflow: hidden;
    
    .menu-child {
      .child-link {
        display: flex;
        align-items: center;
        padding: 0.625rem 1rem 0.625rem 2.5rem;
        color: var(--text-color-secondary);
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        
        &:hover {
          background: var(--surface-hover);
          color: var(--text-color);
        }
        
        &.active {
          color: var(--primary-color);
          font-weight: 600;
          background: var(--primary-color-alpha);
        }
        
        .child-icon {
          font-size: 0.875rem;
          margin-right: 0.625rem;
          flex-shrink: 0;
          width: 0.875rem;
          text-align: center;
        }
        
        .child-label {
          flex: 1;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        
        .child-badge-wrapper {
          margin-left: auto;
          flex-shrink: 0;
          
          :deep(.p-badge) {
            min-width: 1.25rem;
            height: 1.25rem;
            line-height: 1.25rem;
            font-size: 0.625rem;
            font-weight: 600;
          }
        }
      }
    }
  }
  
  // Tooltip trigger for collapsed state
  .menu-tooltip-trigger {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
  }
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-height: 500px;
  opacity: 1;
}
</style>