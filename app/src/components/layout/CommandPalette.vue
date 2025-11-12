<template>
  <div class="command-palette">
    <div class="command-search">
      <i class="pi pi-search search-icon"></i>
      <input
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        class="search-input"
        :placeholder="$t('commandPalette.placeholder')"
        @keydown="handleKeydown"
      />
      <kbd class="search-hint">ESC</kbd>
    </div>
    
    <Divider />
    
    <div class="command-results">
      <ScrollPanel class="results-scroll">
        <!-- Recent Searches -->
        <div v-if="!searchQuery && recentSearches.length > 0" class="result-section">
          <div class="section-title">{{ $t('commandPalette.recent') }}</div>
          <div
            v-for="(item, index) in recentSearches"
            :key="`recent-${index}`"
            class="result-item"
            :class="{ active: selectedIndex === index }"
            @click="selectItem(item)"
            @mouseenter="selectedIndex = index"
          >
            <i class="pi pi-history result-icon"></i>
            <span class="result-label">{{ item.label }}</span>
          </div>
        </div>
        
        <!-- Quick Actions -->
        <div v-if="filteredQuickActions.length > 0" class="result-section">
          <div class="section-title">{{ $t('commandPalette.quickActions') }}</div>
          <div
            v-for="(action, index) in filteredQuickActions"
            :key="`action-${index}`"
            class="result-item"
            :class="{ active: selectedIndex === getActualIndex('actions', index) }"
            @click="executeAction(action)"
            @mouseenter="selectedIndex = getActualIndex('actions', index)"
          >
            <i :class="action.icon" class="result-icon"></i>
            <span class="result-label">{{ $t(action.label) }}</span>
            <kbd v-if="action.shortcut" class="result-shortcut">{{ action.shortcut }}</kbd>
          </div>
        </div>
        
        <!-- Pages -->
        <div v-if="filteredPages.length > 0" class="result-section">
          <div class="section-title">{{ $t('commandPalette.pages') }}</div>
          <div
            v-for="(page, index) in filteredPages"
            :key="`page-${index}`"
            class="result-item"
            :class="{ active: selectedIndex === getActualIndex('pages', index) }"
            @click="navigateToPage(page)"
            @mouseenter="selectedIndex = getActualIndex('pages', index)"
          >
            <i :class="page.icon" class="result-icon"></i>
            <div class="result-content">
              <span class="result-label">{{ $t(page.label) }}</span>
              <span class="result-path">{{ page.path }}</span>
            </div>
          </div>
        </div>
        
        <!-- Users (Example of searchable data) -->
        <div v-if="searchQuery && filteredUsers.length > 0" class="result-section">
          <div class="section-title">{{ $t('commandPalette.users') }}</div>
          <div
            v-for="(user, index) in filteredUsers"
            :key="`user-${index}`"
            class="result-item"
            :class="{ active: selectedIndex === getActualIndex('users', index) }"
            @click="openUser(user)"
            @mouseenter="selectedIndex = getActualIndex('users', index)"
          >
            <Avatar
              :image="user.avatar"
              :label="user.initials"
              shape="circle"
              size="small"
              class="result-avatar"
            />
            <div class="result-content">
              <span class="result-label">{{ user.name }}</span>
              <span class="result-meta">{{ user.email }}</span>
            </div>
          </div>
        </div>
        
        <!-- No Results -->
        <div v-if="searchQuery && totalResults === 0" class="no-results">
          <i class="pi pi-search no-results-icon"></i>
          <p class="no-results-text">{{ $t('commandPalette.noResults') }}</p>
          <p class="no-results-hint">{{ $t('commandPalette.tryDifferent') }}</p>
        </div>
      </ScrollPanel>
    </div>
    
    <Divider />
    
    <div class="command-footer">
      <div class="footer-hints">
        <span class="hint">
          <kbd>↑↓</kbd> {{ $t('commandPalette.navigate') }}
        </span>
        <span class="hint">
          <kbd>↵</kbd> {{ $t('commandPalette.select') }}
        </span>
        <span class="hint">
          <kbd>ESC</kbd> {{ $t('commandPalette.close') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Avatar from 'primevue/avatar'
import Divider from 'primevue/divider'
import ScrollPanel from 'primevue/scrollpanel'

interface MenuItem {
  key: string
  label: string
  icon: string
  to?: string
  path?: string
}

interface QuickAction {
  label: string
  icon: string
  shortcut?: string
  action: () => void
}

interface Props {
  pages?: MenuItem[]
  quickActions?: QuickAction[]
}

const props = withDefaults(defineProps<Props>(), {
  pages: () => [],
  quickActions: () => []
})

const emit = defineEmits<{
  close: []
}>()

const router = useRouter()
const { t } = useI18n()

// Refs
const searchInput = ref<HTMLInputElement>()
const searchQuery = ref('')
const selectedIndex = ref(0)
const recentSearches = ref<any[]>([])

// Computed
const filteredQuickActions = computed(() => {
  if (!searchQuery.value) return props.quickActions.slice(0, 6)
  const query = searchQuery.value.toLowerCase()
  return props.quickActions.filter(action =>
    t(action.label).toLowerCase().includes(query)
  )
})

const filteredPages = computed(() => {
  if (!searchQuery.value) return props.pages.slice(0, 8)
  const query = searchQuery.value.toLowerCase()
  return props.pages.filter(page => {
    const path = page.to || page.path || ''
    return (
      t(page.label).toLowerCase().includes(query) ||
      path.toLowerCase().includes(query)
    )
  })
})

// Remove user search for now - can be added back if needed
const filteredUsers = computed(() => {
  return []
})

const totalResults = computed(() => {
  let count = 0
  if (!searchQuery.value && recentSearches.value.length > 0) {
    count += recentSearches.value.length
  }
  count += filteredQuickActions.value.length
  count += filteredPages.value.length
  count += filteredUsers.value.length
  return count
})

// Methods
const getActualIndex = (section: string, index: number): number => {
  let actualIndex = index
  
  if (!searchQuery.value && recentSearches.value.length > 0 && section !== 'recent') {
    actualIndex += recentSearches.value.length
  }
  
  if (section === 'pages') {
    actualIndex += filteredQuickActions.value.length
  }
  
  if (section === 'users') {
    actualIndex += filteredQuickActions.value.length + filteredPages.value.length
  }
  
  return actualIndex
}

const handleKeydown = (event: KeyboardEvent) => {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, totalResults.value - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
      break
    case 'Enter':
      event.preventDefault()
      executeSelectedItem()
      break
    case 'Escape':
      event.preventDefault()
      emit('close')
      break
  }
}

const executeSelectedItem = () => {
  let currentIndex = 0
  
  // Check recent searches
  if (!searchQuery.value && recentSearches.value.length > 0) {
    if (selectedIndex.value < recentSearches.value.length) {
      selectItem(recentSearches.value[selectedIndex.value])
      return
    }
    currentIndex += recentSearches.value.length
  }
  
  // Check quick actions
  if (selectedIndex.value < currentIndex + filteredQuickActions.value.length) {
    executeAction(filteredQuickActions.value[selectedIndex.value - currentIndex])
    return
  }
  currentIndex += filteredQuickActions.value.length
  
  // Check pages
  if (selectedIndex.value < currentIndex + filteredPages.value.length) {
    navigateToPage(filteredPages.value[selectedIndex.value - currentIndex])
    return
  }
  currentIndex += filteredPages.value.length
  
  // Check users
  if (selectedIndex.value < currentIndex + filteredUsers.value.length) {
    openUser(filteredUsers.value[selectedIndex.value - currentIndex])
    return
  }
}

const selectItem = (item: any) => {
  searchQuery.value = item.label
  nextTick(() => searchInput.value?.focus())
}

const executeAction = (action: any) => {
  action.action()
  emit('close')
}

const navigateToPage = (page: any) => {
  const path = page.to || page.path
  if (path) {
    router.push(path)
    emit('close')
  }
}

const openUser = (user: any) => {
  router.push(`/users/${user.id}`)
  emit('close')
}

// Load recent searches from localStorage
const loadRecentSearches = () => {
  const stored = localStorage.getItem('commandPaletteRecent')
  if (stored) {
    recentSearches.value = JSON.parse(stored)
  }
}

// Lifecycle
onMounted(() => {
  loadRecentSearches()
  nextTick(() => searchInput.value?.focus())
})
</script>

<style lang="scss" scoped>
.command-palette {
  display: flex;
  flex-direction: column;
  height: 500px;
  max-height: 70vh;
  
  .command-search {
    display: flex;
    align-items: center;
    padding: 1rem;
    position: relative;
    
    .search-icon {
      position: absolute;
      left: 1.75rem;
      color: var(--text-color-secondary);
    }
    
    .search-input {
      flex: 1;
      padding: 0.75rem 3rem 0.75rem 2.75rem;
      border: 1px solid var(--surface-border);
      border-radius: 0.5rem;
      background: var(--surface-ground);
      color: var(--text-color);
      font-size: 1rem;
      outline: none;
      transition: all 0.2s;
      
      &:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem var(--primary-color-alpha);
      }
      
      &::placeholder {
        color: var(--text-color-secondary);
      }
    }
    
    .search-hint {
      position: absolute;
      right: 1.75rem;
      padding: 0.25rem 0.5rem;
      background: var(--surface-border);
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-color-secondary);
    }
  }
  
  .command-results {
    flex: 1;
    overflow: hidden;
    
    .results-scroll {
      height: 100%;
      
      :deep(.p-scrollpanel-wrapper) {
        padding: 0.5rem;
      }
      
      :deep(.p-scrollpanel-bar) {
        background: var(--surface-border);
        opacity: 0.3;
      }
    }
    
    .result-section {
      margin-bottom: 1rem;
      
      .section-title {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-color-secondary);
        letter-spacing: 0.05em;
      }
    }
    
    .result-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 0.75rem;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: all 0.15s;
      
      &:hover,
      &.active {
        background: var(--surface-hover);
        
        .result-icon {
          color: var(--primary-color);
        }
      }
      
      .result-icon {
        font-size: 1rem;
        color: var(--text-color-secondary);
        flex-shrink: 0;
        width: 1.25rem;
      }
      
      .result-avatar {
        flex-shrink: 0;
      }
      
      .result-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
      }
      
      .result-label {
        color: var(--text-color);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      
      .result-path,
      .result-meta {
        font-size: 0.75rem;
        color: var(--text-color-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      
      .result-shortcut {
        margin-left: auto;
        padding: 0.125rem 0.375rem;
        background: var(--surface-ground);
        border: 1px solid var(--surface-border);
        border-radius: 0.25rem;
        font-size: 0.625rem;
        font-weight: 600;
        color: var(--text-color-secondary);
      }
    }
    
    .no-results {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
      text-align: center;
      
      .no-results-icon {
        font-size: 3rem;
        color: var(--text-color-secondary);
        opacity: 0.3;
        margin-bottom: 1rem;
      }
      
      .no-results-text {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        margin: 0 0 0.5rem 0;
      }
      
      .no-results-hint {
        font-size: 0.875rem;
        color: var(--text-color-secondary);
        margin: 0;
      }
    }
  }
  
  .command-footer {
    padding: 0.75rem 1rem;
    background: var(--surface-ground);
    
    .footer-hints {
      display: flex;
      align-items: center;
      gap: 1rem;
      justify-content: center;
      
      .hint {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        color: var(--text-color-secondary);
        
        kbd {
          padding: 0.125rem 0.375rem;
          background: var(--surface-section);
          border: 1px solid var(--surface-border);
          border-radius: 0.25rem;
          font-size: 0.625rem;
          font-weight: 600;
        }
      }
    }
  }
}
</style>
