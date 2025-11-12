<template>
  <Card class="recent-activity-card">
    <template #header>
      <div class="flex align-items-center justify-content-between p-3 pb-0">
        <div>
          <h3 class="text-xl font-semibold m-0">Recent Activity</h3>
          <p class="text-color-secondary text-sm m-0 mt-1">Latest system events and updates</p>
        </div>
        <Button
          label="View All"
          icon="pi pi-arrow-right"
          text
          size="small"
          @click="viewAll"
        />
      </div>
    </template>
    <template #content>
      <div v-if="loading" class="activity-loading">
        <ProgressSpinner style="width: 40px; height: 40px" strokeWidth="4" />
        <p class="text-color-secondary mt-3">Loading activity...</p>
      </div>
      
      <div v-else-if="!events || events.length === 0" class="activity-empty">
        <i class="pi pi-inbox text-4xl text-color-secondary mb-3"></i>
        <p class="text-color-secondary m-0">No recent activity</p>
        <p class="text-color-secondary text-sm mt-1">Events will appear here as they occur</p>
      </div>
      
      <div v-else class="activity-timeline">
        <div 
          v-for="(event, index) in displayEvents" 
          :key="`event-${index}`"
          class="timeline-item"
          :class="{ 'timeline-item-last': index === displayEvents.length - 1 }"
        >
          <div class="timeline-marker" :class="`marker-${getEventSeverity(event)}`">
            <i :class="getEventIcon(event)"></i>
          </div>
          <div class="timeline-content">
            <div class="timeline-header">
              <span class="timeline-title">{{ getEventTitle(event) }}</span>
              <span class="timeline-time">{{ formatTime(event.created_at || event.timestamp) }}</span>
            </div>
            <p class="timeline-description">{{ getEventDescription(event) }}</p>
            <div v-if="event.metadata && Object.keys(getEventTags(event)).length > 0" class="timeline-metadata">
              <Chip 
                v-for="(value, key) in getEventTags(event)" 
                :key="key"
                :label="`${key}: ${value}`"
                class="text-xs mr-2"
              />
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="events && events.length > 5 && !loading" class="activity-footer">
        <Button
          :label="showAll ? 'Show Less' : `Show ${events.length - 5} More`"
          :icon="showAll ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
          text
          size="small"
          class="w-full"
          @click="toggleShowAll"
        />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Chip from 'primevue/chip'
import ProgressSpinner from 'primevue/progressspinner'

interface Props {
  events?: any[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  events: () => [],
  loading: false
})

const router = useRouter()
const showAll = ref(false)

const displayEvents = computed(() => {
  if (!props.events) return []
  return showAll.value ? props.events : props.events.slice(0, 5)
})

const getEventIcon = (event: any) => {
  const eventType = event.event_type || event.type || 'default'
  const iconMap: Record<string, string> = {
    'user.created': 'pi pi-user-plus',
    'user.updated': 'pi pi-user-edit',
    'user.deleted': 'pi pi-user-minus',
    'tenant.created': 'pi pi-building',
    'tenant.activated': 'pi pi-check-circle',
    'tenant.suspended': 'pi pi-ban',
    'subscription.created': 'pi pi-credit-card',
    'subscription.updated': 'pi pi-sync',
    'subscription.cancelled': 'pi pi-times-circle',
    'plan.changed': 'pi pi-arrow-right-arrow-left',
    'payment.succeeded': 'pi pi-dollar',
    'payment.failed': 'pi pi-exclamation-triangle',
    'webhook.sent': 'pi pi-send',
    'api_key.created': 'pi pi-key',
    'system.error': 'pi pi-exclamation-circle',
    'default': 'pi pi-info-circle'
  }
  return iconMap[eventType] || iconMap.default
}

const getEventSeverity = (event: any) => {
  const eventType = event.event_type || event.type || ''
  if (eventType.includes('failed') || eventType.includes('error') || eventType.includes('suspended')) {
    return 'danger'
  }
  if (eventType.includes('warning') || eventType.includes('cancelled')) {
    return 'warning'
  }
  if (eventType.includes('created') || eventType.includes('succeeded') || eventType.includes('activated')) {
    return 'success'
  }
  return 'info'
}

const getEventTitle = (event: any) => {
  const eventType = event.event_type || event.type || 'Activity'
  return eventType.split('.').map((word: string) => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const getEventDescription = (event: any) => {
  if (event.description) return event.description
  
  const eventType = event.event_type || event.type || ''
  const metadata = event.metadata || {}
  
  // Generate description based on event type
  if (eventType.includes('user')) {
    return `User ${metadata.user_email || metadata.user_id || 'unknown'} ${eventType.split('.')[1]}`
  }
  if (eventType.includes('tenant')) {
    return `Tenant ${metadata.tenant_name || metadata.tenant_id || 'unknown'} ${eventType.split('.')[1]}`
  }
  if (eventType.includes('subscription')) {
    return `Subscription ${eventType.split('.')[1]} for ${metadata.plan || 'plan'}`
  }
  if (eventType.includes('payment')) {
    return `Payment of ${metadata.amount || '$0'} ${eventType.split('.')[1]}`
  }
  
  return `System event: ${eventType}`
}

const getEventTags = (event: any) => {
  const metadata = event.metadata || {}
  const tags: Record<string, any> = {}
  
  // Select important metadata to display as tags
  if (metadata.user_id) tags.user = `#${metadata.user_id}`
  if (metadata.tenant_id) tags.tenant = `#${metadata.tenant_id}`
  if (metadata.plan) tags.plan = metadata.plan
  if (metadata.amount) tags.amount = metadata.amount
  
  // Limit to 3 tags
  return Object.fromEntries(Object.entries(tags).slice(0, 3))
}

const formatTime = (timestamp: string | Date) => {
  if (!timestamp) return 'Unknown'
  
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

const viewAll = () => {
  router.push('/admin/monitoring/events')
}

const toggleShowAll = () => {
  showAll.value = !showAll.value
}
</script>

<style scoped lang="scss">
.recent-activity-card {
  height: 100%;
  border: 1px solid var(--surface-border);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  
  :deep(.p-card-header) {
    background: var(--surface-50);
    border-bottom: 1px solid var(--surface-border);
    
    .dark & {
      background: var(--surface-800);
    }
  }
  
  :deep(.p-card-content) {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
}

.activity-loading,
.activity-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  flex: 1;
}

.activity-timeline {
  flex: 1;
  overflow-y: auto;
  max-height: 400px;
  
  &::-webkit-scrollbar {
    width: 6px;
  }
  
  &::-webkit-scrollbar-track {
    background: var(--surface-100);
    
    .dark & {
      background: var(--surface-800);
    }
  }
  
  &::-webkit-scrollbar-thumb {
    background: var(--surface-400);
    border-radius: 3px;
    
    &:hover {
      background: var(--surface-500);
    }
    
    .dark & {
      background: var(--surface-600);
      
      &:hover {
        background: var(--surface-500);
      }
    }
  }
}

.timeline-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  position: relative;
  
  &:not(.timeline-item-last)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 52px;
    bottom: 0;
    width: 2px;
    background: var(--surface-border);
    
    .dark & {
      background: var(--surface-600);
    }
  }
  
  &:first-child {
    padding-top: 0;
  }
  
  &:last-child {
    padding-bottom: 0;
  }
}

.timeline-marker {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
  transition: all 0.2s ease;
  
  i {
    font-size: 1.125rem;
  }
  
  &.marker-success {
    background: rgba(34, 197, 94, 0.1);
    color: var(--green-600);
    
    .dark & {
      color: var(--green-400);
    }
  }
  
  &.marker-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--yellow-600);
    
    .dark & {
      color: var(--yellow-400);
    }
  }
  
  &.marker-danger {
    background: rgba(239, 68, 68, 0.1);
    color: var(--red-600);
    
    .dark & {
      color: var(--red-400);
    }
  }
  
  &.marker-info {
    background: rgba(59, 130, 246, 0.1);
    color: var(--blue-600);
    
    .dark & {
      color: var(--blue-400);
    }
  }
}

.timeline-content {
  flex: 1;
  min-width: 0;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.25rem;
}

.timeline-title {
  font-weight: 600;
  color: var(--text-color);
  font-size: 0.875rem;
}

.timeline-time {
  font-size: 0.75rem;
  color: var(--text-color-secondary);
  white-space: nowrap;
}

.timeline-description {
  margin: 0.25rem 0;
  font-size: 0.8125rem;
  color: var(--text-color-secondary);
  line-height: 1.5;
}

.timeline-metadata {
  margin-top: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  
  :deep(.p-chip) {
    background: var(--surface-100);
    color: var(--text-color-secondary);
    padding: 0.125rem 0.5rem;
    
    .dark & {
      background: var(--surface-700);
    }
  }
}

.activity-footer {
  padding-top: 1rem;
  margin-top: 1rem;
  border-top: 1px solid var(--surface-border);
  
  .dark & {
    border-color: var(--surface-600);
  }
}
</style>
