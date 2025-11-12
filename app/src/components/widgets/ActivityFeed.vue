<template>
  <div class="activity-feed" :class="{ 'activity-feed--compact': compact }">
    <!-- Header -->
    <div class="activity-feed__header" v-if="title || showFilter">
      <h3 class="activity-feed__title" v-if="title">{{ title }}</h3>
      
      <div class="activity-feed__controls" v-if="showFilter || showRefresh">
        <!-- Filter dropdown -->
        <Dropdown
          v-if="showFilter"
          v-model="selectedFilter"
          :options="filterOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="All activities"
          class="activity-feed__filter"
          @change="handleFilterChange"
        />
        
        <!-- Refresh button -->
        <Button
          v-if="showRefresh"
          icon="pi pi-refresh"
          severity="secondary"
          text
          size="small"
          :loading="loading"
          @click="handleRefresh"
          v-tooltip="'Refresh'"
        />
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading && !activities.length" class="activity-feed__loading">
      <div v-for="i in 3" :key="i" class="activity-feed__skeleton">
        <Skeleton shape="circle" size="2.5rem" />
        <div class="flex-1">
          <Skeleton width="60%" height="1rem" class="mb-2" />
          <Skeleton width="40%" height="0.875rem" />
        </div>
      </div>
    </div>

    <!-- Timeline -->
    <Timeline 
      v-else
      :value="filteredActivities"
      class="activity-feed__timeline"
      :align="align"
    >
      <template #marker="slotProps">
        <div 
          class="activity-feed__marker"
          :class="getMarkerClass(slotProps.item)"
        >
          <i :class="getActivityIcon(slotProps.item)"></i>
        </div>
      </template>
      
      <template #content="slotProps">
        <div 
          class="activity-feed__item"
          :class="{ 'activity-feed__item--unread': slotProps.item.unread }"
          @click="handleItemClick(slotProps.item)"
        >
          <!-- Content header -->
          <div class="activity-feed__item-header">
            <!-- User avatar -->
            <Avatar 
              v-if="slotProps.item.user?.avatar"
              :image="slotProps.item.user.avatar"
              :label="slotProps.item.user.name"
              shape="circle"
              size="small"
              class="activity-feed__avatar"
            />
            <Avatar 
              v-else-if="slotProps.item.user?.name"
              :label="getInitials(slotProps.item.user.name)"
              shape="circle"
              size="small"
              class="activity-feed__avatar"
              :style="{ backgroundColor: getAvatarColor(slotProps.item.user.name) }"
            />
            
            <!-- User info -->
            <div class="activity-feed__user">
              <span class="activity-feed__user-name">
                {{ slotProps.item.user?.name || 'System' }}
              </span>
              <Badge 
                v-if="slotProps.item.user?.role"
                :value="slotProps.item.user.role"
                :severity="getRoleSeverity(slotProps.item.user.role)"
                class="activity-feed__user-role"
              />
            </div>
            
            <!-- Time -->
            <span class="activity-feed__time" v-tooltip="formatFullDate(slotProps.item.timestamp)">
              {{ formatRelativeTime(slotProps.item.timestamp) }}
            </span>
          </div>
          
          <!-- Content body -->
          <div class="activity-feed__item-body">
            <!-- Title -->
            <div class="activity-feed__item-title">
              <span v-html="highlightKeywords(slotProps.item.title)"></span>
              <Badge 
                v-if="slotProps.item.badge"
                :value="slotProps.item.badge.value"
                :severity="slotProps.item.badge.severity"
                class="ml-2"
              />
            </div>
            
            <!-- Description -->
            <p 
              v-if="slotProps.item.description"
              class="activity-feed__item-description"
              v-html="highlightKeywords(slotProps.item.description)"
            ></p>
            
            <!-- Metadata -->
            <div 
              v-if="slotProps.item.metadata"
              class="activity-feed__item-metadata"
            >
              <Chip 
                v-for="(value, key) in slotProps.item.metadata"
                :key="key"
                :label="`${key}: ${value}`"
                class="activity-feed__item-chip"
              />
            </div>
            
            <!-- Attachments -->
            <div 
              v-if="slotProps.item.attachments?.length"
              class="activity-feed__item-attachments"
            >
              <div 
                v-for="attachment in slotProps.item.attachments"
                :key="attachment.id"
                class="activity-feed__attachment"
                @click.stop="handleAttachmentClick(attachment)"
              >
                <i :class="getFileIcon(attachment.type)" class="mr-2"></i>
                <span>{{ attachment.name }}</span>
                <span class="activity-feed__attachment-size">
                  ({{ formatFileSize(attachment.size) }})
                </span>
              </div>
            </div>
            
            <!-- Actions -->
            <div 
              v-if="slotProps.item.actions?.length"
              class="activity-feed__item-actions"
            >
              <Button
                v-for="action in slotProps.item.actions"
                :key="action.id"
                :label="action.label"
                :icon="action.icon"
                :severity="action.severity || 'secondary'"
                size="small"
                text
                @click.stop="handleAction(slotProps.item, action)"
              />
            </div>
          </div>
        </div>
      </template>
      
      <template #opposite="slotProps" v-if="!compact">
        <small class="activity-feed__date">
          {{ formatDate(slotProps.item.timestamp) }}
        </small>
      </template>
    </Timeline>

    <!-- Empty state -->
    <div v-if="!loading && !filteredActivities.length" class="activity-feed__empty">
      <i class="pi pi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
      <p class="text-gray-500 dark:text-gray-400">No activities to display</p>
    </div>

    <!-- Load more -->
    <div 
      v-if="hasMore && !loading"
      class="activity-feed__load-more"
    >
      <Button
        label="Load more"
        icon="pi pi-arrow-down"
        severity="secondary"
        text
        @click="handleLoadMore"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Timeline from 'primevue/timeline'
import Avatar from 'primevue/avatar'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import Chip from 'primevue/chip'
import Dropdown from 'primevue/dropdown'
import Skeleton from 'primevue/skeleton'

export interface ActivityUser {
  id: string
  name: string
  avatar?: string
  role?: string
}

export interface ActivityAttachment {
  id: string
  name: string
  type: string
  size: number
  url?: string
}

export interface ActivityAction {
  id: string
  label: string
  icon?: string
  severity?: string
  command?: () => void
}

export interface Activity {
  id: string
  type: 'info' | 'success' | 'warning' | 'danger' | 'create' | 'update' | 'delete' | 'login' | 'logout'
  title: string
  description?: string
  timestamp: Date | string
  user?: ActivityUser
  unread?: boolean
  metadata?: Record<string, any>
  attachments?: ActivityAttachment[]
  actions?: ActivityAction[]
  badge?: {
    value: string
    severity?: string
  }
}

interface Props {
  activities: Activity[]
  title?: string
  loading?: boolean
  compact?: boolean
  align?: 'left' | 'right' | 'alternate'
  showFilter?: boolean
  showRefresh?: boolean
  hasMore?: boolean
  filterOptions?: Array<{ label: string; value: string }>
  highlightWords?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  activities: () => [],
  loading: false,
  compact: false,
  align: 'left',
  showFilter: false,
  showRefresh: false,
  hasMore: false,
  filterOptions: () => [
    { label: 'All activities', value: 'all' },
    { label: 'Users', value: 'user' },
    { label: 'System', value: 'system' },
    { label: 'Errors', value: 'error' }
  ],
  highlightWords: () => []
})

const emit = defineEmits<{
  itemClick: [activity: Activity]
  attachmentClick: [attachment: ActivityAttachment]
  action: [activity: Activity, action: ActivityAction]
  loadMore: []
  refresh: []
  filterChange: [value: string]
}>()

// State
const selectedFilter = ref('all')

// Filtered activities
const filteredActivities = computed(() => {
  if (selectedFilter.value === 'all') {
    return props.activities
  }
  
  // Apply filter logic based on selected filter
  return props.activities.filter(activity => {
    switch (selectedFilter.value) {
      case 'user':
        return activity.user !== undefined
      case 'system':
        return activity.user === undefined
      case 'error':
        return activity.type === 'danger' || activity.type === 'warning'
      default:
        return true
    }
  })
})

// Get activity icon based on type
const getActivityIcon = (activity: Activity) => {
  const icons: Record<string, string> = {
    info: 'pi pi-info-circle',
    success: 'pi pi-check-circle',
    warning: 'pi pi-exclamation-triangle',
    danger: 'pi pi-times-circle',
    create: 'pi pi-plus-circle',
    update: 'pi pi-pencil',
    delete: 'pi pi-trash',
    login: 'pi pi-sign-in',
    logout: 'pi pi-sign-out'
  }
  return icons[activity.type] || 'pi pi-circle'
}

// Get marker class based on activity type
const getMarkerClass = (activity: Activity) => {
  return `activity-feed__marker--${activity.type}`
}

// Get role severity
const getRoleSeverity = (role: string) => {
  const severities: Record<string, string> = {
    admin: 'danger',
    moderator: 'warning',
    user: 'info'
  }
  return severities[role.toLowerCase()] || 'secondary'
}

// Get file icon based on type
const getFileIcon = (type: string) => {
  const icons: Record<string, string> = {
    pdf: 'pi pi-file-pdf',
    image: 'pi pi-image',
    video: 'pi pi-video',
    audio: 'pi pi-volume-up',
    document: 'pi pi-file',
    spreadsheet: 'pi pi-file-excel',
    archive: 'pi pi-file-archive'
  }
  return icons[type] || 'pi pi-file'
}

// Get initials from name
const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Get avatar color based on name
const getAvatarColor = (name: string) => {
  const colors = [
    '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
    '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
  ]
  const index = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)
  return colors[index % colors.length]
}

// Format relative time
const formatRelativeTime = (timestamp: Date | string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000)
  
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`
  
  return date.toLocaleDateString()
}

// Format date
const formatDate = (timestamp: Date | string) => {
  return new Date(timestamp).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Format full date
const formatFullDate = (timestamp: Date | string) => {
  return new Date(timestamp).toLocaleString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format file size
const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// Highlight keywords in text
const highlightKeywords = (text: string) => {
  if (!props.highlightWords.length) return text
  
  let highlightedText = text
  props.highlightWords.forEach(word => {
    const regex = new RegExp(`(${word})`, 'gi')
    highlightedText = highlightedText.replace(regex, '<mark>$1</mark>')
  })
  
  return highlightedText
}

// Handlers
const handleItemClick = (activity: Activity) => {
  emit('itemClick', activity)
}

const handleAttachmentClick = (attachment: ActivityAttachment) => {
  emit('attachmentClick', attachment)
}

const handleAction = (activity: Activity, action: ActivityAction) => {
  action.command?.()
  emit('action', activity, action)
}

const handleLoadMore = () => {
  emit('loadMore')
}

const handleRefresh = () => {
  emit('refresh')
}

const handleFilterChange = () => {
  emit('filterChange', selectedFilter.value)
}
</script>

<style scoped lang="scss">
.activity-feed {
  @apply bg-white dark:bg-gray-800 rounded-2xl p-6;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  
  &--compact {
    @apply p-4;
    
    .activity-feed__item {
      @apply p-3;
    }
  }
  
  &__header {
    @apply flex items-center justify-between mb-4;
  }
  
  &__title {
    @apply text-lg font-semibold text-gray-900 dark:text-white;
  }
  
  &__controls {
    @apply flex items-center gap-2;
  }
  
  &__filter {
    @apply text-sm;
    
    :deep(.p-dropdown) {
      @apply border-gray-200 dark:border-gray-700;
    }
  }
  
  &__loading {
    @apply space-y-4;
  }
  
  &__skeleton {
    @apply flex items-center gap-3;
  }
  
  &__timeline {
    :deep(.p-timeline-event-opposite) {
      @apply text-xs text-gray-500 dark:text-gray-400;
    }
    
    :deep(.p-timeline-event-separator) {
      @apply flex-none;
    }
    
    :deep(.p-timeline-event-connector) {
      @apply bg-gray-200 dark:bg-gray-700;
    }
  }
  
  &__marker {
    @apply w-10 h-10 rounded-full flex items-center justify-center text-white shadow-lg;
    
    &--info {
      @apply bg-blue-500;
    }
    
    &--success {
      @apply bg-green-500;
    }
    
    &--warning {
      @apply bg-yellow-500;
    }
    
    &--danger {
      @apply bg-red-500;
    }
    
    &--create {
      @apply bg-indigo-500;
    }
    
    &--update {
      @apply bg-purple-500;
    }
    
    &--delete {
      @apply bg-pink-500;
    }
    
    &--login {
      @apply bg-teal-500;
    }
    
    &--logout {
      @apply bg-gray-500;
    }
  }
  
  &__item {
    @apply p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 cursor-pointer transition-all duration-200;
    
    &:hover {
      @apply bg-gray-100 dark:bg-gray-900/70 transform scale-[1.01];
    }
    
    &--unread {
      @apply border-l-4 border-blue-500 bg-blue-50 dark:bg-blue-900/20;
      
      &:hover {
        @apply bg-blue-100 dark:bg-blue-900/30;
      }
    }
    
    &-header {
      @apply flex items-center gap-3 mb-2;
    }
    
    &-body {
      @apply ml-11;
    }
    
    &-title {
      @apply text-sm font-medium text-gray-900 dark:text-white mb-1;
      
      :deep(mark) {
        @apply bg-yellow-200 dark:bg-yellow-900/50 px-1 rounded;
      }
    }
    
    &-description {
      @apply text-sm text-gray-600 dark:text-gray-400 mb-2;
      
      :deep(mark) {
        @apply bg-yellow-200 dark:bg-yellow-900/50 px-1 rounded;
      }
    }
    
    &-metadata {
      @apply flex flex-wrap gap-2 mb-2;
    }
    
    &-chip {
      @apply text-xs;
      
      :deep(.p-chip) {
        @apply bg-gray-200 dark:bg-gray-700;
      }
    }
    
    &-attachments {
      @apply space-y-1 mb-2;
    }
    
    &-actions {
      @apply flex gap-2 mt-2;
    }
  }
  
  &__avatar {
    @apply flex-shrink-0;
  }
  
  &__user {
    @apply flex items-center gap-2 flex-1;
    
    &-name {
      @apply text-sm font-medium text-gray-900 dark:text-white;
    }
    
    &-role {
      @apply text-xs;
    }
  }
  
  &__time {
    @apply text-xs text-gray-500 dark:text-gray-400;
  }
  
  &__date {
    @apply text-xs text-gray-500 dark:text-gray-400;
  }
  
  &__attachment {
    @apply flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer;
    
    &-size {
      @apply text-gray-500 dark:text-gray-400 ml-1;
    }
  }
  
  &__empty {
    @apply text-center py-8;
  }
  
  &__load-more {
    @apply text-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700;
  }
}
</style>
