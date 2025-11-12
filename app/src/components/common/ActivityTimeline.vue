<template>
  <div class="activity-timeline">
    <div v-for="(item, index) in items" :key="index" class="timeline-item" :class="{ 'last-item': index === items.length - 1 }">
      <div class="timeline-marker" :class="`marker-${item.type || 'default'}`">
        <i :class="getIcon(item.type)"></i>
      </div>

      <div class="timeline-content">
        <div class="timeline-header">
          <h4 class="timeline-title">{{ item.title }}</h4>
          <span class="timeline-time">{{ formatTime(item.timestamp) }}</span>
        </div>

        <p v-if="item.description" class="timeline-description">
          {{ item.description }}
        </p>

        <div v-if="item.metadata" class="timeline-metadata">
          <div v-for="(value, key) in item.metadata" :key="key" class="metadata-item">
            <span class="metadata-label">{{ key }}:</span>
            <span class="metadata-value">{{ value }}</span>
          </div>
        </div>

        <div v-if="item.user" class="timeline-user">
          <Avatar
            :label="item.user.name?.charAt(0)"
            :image="item.user.avatar"
            size="small"
            shape="circle"
          />
          <span class="user-name">{{ item.user.name }}</span>
        </div>
      </div>
    </div>

    <div v-if="items.length === 0" class="timeline-empty">
      <i class="pi pi-history"></i>
      <p>No activity yet</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import Avatar from 'primevue/avatar'
import { computed } from 'vue'

export interface TimelineItem {
  title: string
  description?: string
  timestamp: string | Date
  type?: 'create' | 'update' | 'delete' | 'login' | 'logout' | 'error' | 'success' | 'warning' | 'default'
  metadata?: Record<string, any>
  user?: {
    name: string
    avatar?: string
  }
}

interface Props {
  items: TimelineItem[]
}

const props = defineProps<Props>()

const getIcon = (type?: string) => {
  const icons = {
    create: 'pi pi-plus-circle',
    update: 'pi pi-pencil',
    delete: 'pi pi-trash',
    login: 'pi pi-sign-in',
    logout: 'pi pi-sign-out',
    error: 'pi pi-times-circle',
    success: 'pi pi-check-circle',
    warning: 'pi pi-exclamation-triangle',
    default: 'pi pi-circle-fill'
  }
  return icons[type as keyof typeof icons] || icons.default
}

const formatTime = (timestamp: string | Date) => {
  const date = typeof timestamp === 'string' ? new Date(timestamp) : timestamp
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}
</script>

<style lang="scss" scoped>
.activity-timeline {
  position: relative;
  padding: 1rem 0;

  .timeline-item {
    display: flex;
    gap: 1rem;
    position: relative;
    padding-bottom: 2rem;

    &:not(.last-item)::before {
      content: '';
      position: absolute;
      left: 1.125rem;
      top: 2.5rem;
      bottom: 0;
      width: 2px;
      background: var(--surface-border);
    }

    .timeline-marker {
      flex-shrink: 0;
      width: 2.25rem;
      height: 2.25rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--surface-card);
      border: 2px solid var(--surface-border);
      z-index: 1;

      i {
        font-size: 1rem;
        color: var(--text-color-secondary);
      }

      &.marker-create {
        background: rgba(16, 185, 129, 0.1);
        border-color: #10b981;
        i { color: #10b981; }
      }

      &.marker-update {
        background: rgba(59, 130, 246, 0.1);
        border-color: #3b82f6;
        i { color: #3b82f6; }
      }

      &.marker-delete {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        i { color: #ef4444; }
      }

      &.marker-login,
      &.marker-logout {
        background: rgba(139, 92, 246, 0.1);
        border-color: #8b5cf6;
        i { color: #8b5cf6; }
      }

      &.marker-error {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        i { color: #ef4444; }
      }

      &.marker-success {
        background: rgba(16, 185, 129, 0.1);
        border-color: #10b981;
        i { color: #10b981; }
      }

      &.marker-warning {
        background: rgba(245, 158, 11, 0.1);
        border-color: #f59e0b;
        i { color: #f59e0b; }
      }
    }

    .timeline-content {
      flex: 1;
      background: var(--surface-card);
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid var(--surface-border);
      transition: all 0.2s ease;

      &:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      }

      .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
        gap: 1rem;

        .timeline-title {
          font-size: 0.9375rem;
          font-weight: 600;
          color: var(--text-color);
          margin: 0;
        }

        .timeline-time {
          font-size: 0.8125rem;
          color: var(--text-color-secondary);
          white-space: nowrap;
        }
      }

      .timeline-description {
        font-size: 0.875rem;
        color: var(--text-color-secondary);
        margin: 0 0 0.75rem 0;
        line-height: 1.5;
      }

      .timeline-metadata {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 0.75rem;

        .metadata-item {
          font-size: 0.8125rem;
          padding: 0.25rem 0.5rem;
          background: var(--surface-100);
          border-radius: 4px;

          .metadata-label {
            color: var(--text-color-secondary);
            margin-right: 0.25rem;
          }

          .metadata-value {
            color: var(--text-color);
            font-weight: 500;
          }
        }
      }

      .timeline-user {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--surface-border);

        .user-name {
          font-size: 0.8125rem;
          color: var(--text-color-secondary);
        }
      }
    }
  }

  .timeline-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-color-secondary);

    i {
      font-size: 3rem;
      opacity: 0.3;
      margin-bottom: 1rem;
    }

    p {
      margin: 0;
      font-size: 0.9375rem;
    }
  }
}

.dark .activity-timeline {
  .timeline-item {
    &:not(.last-item)::before {
      background: var(--surface-700);
    }
  }

  .timeline-content {
    .timeline-metadata {
      .metadata-item {
        background: var(--surface-700);
      }
    }
  }
}
</style>
