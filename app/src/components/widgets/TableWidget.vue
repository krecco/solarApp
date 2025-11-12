<template>
  <div class="table-widget" :class="{ 'table-widget--compact': compact }">
    <!-- Header -->
    <div class="table-widget__header" v-if="title || showSearch || showActions">
      <div class="table-widget__header-left">
        <h3 class="table-widget__title" v-if="title">{{ title }}</h3>
        <Badge 
          v-if="totalRecords"
          :value="`${totalRecords} records`"
          severity="secondary"
          class="ml-2"
        />
      </div>
      
      <div class="table-widget__header-right">
        <!-- Search -->
        <div v-if="showSearch" class="table-widget__search">
          <i class="pi pi-search table-widget__search-icon"></i>
          <InputText
            v-model="searchQuery"
            placeholder="Search..."
            class="table-widget__search-input"
            @input="handleSearch"
          />
        </div>
        
        <!-- Actions -->
        <div class="table-widget__actions">
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
          
          <Button
            v-if="showExport"
            icon="pi pi-download"
            severity="secondary"
            text
            size="small"
            @click="toggleExportMenu"
            v-tooltip="'Export'"
          />
          
          <Button
            v-if="showColumnToggle"
            icon="pi pi-cog"
            severity="secondary"
            text
            size="small"
            @click="toggleColumnMenu"
            v-tooltip="'Columns'"
          />
          
          <Button
            v-if="showAdd"
            icon="pi pi-plus"
            label="Add"
            severity="primary"
            size="small"
            @click="handleAdd"
          />
        </div>
        
        <!-- Export menu -->
        <Menu
          ref="exportMenu"
          :model="exportMenuItems"
          :popup="true"
        />
        
        <!-- Column toggle menu -->
        <OverlayPanel ref="columnMenu">
          <div class="table-widget__column-toggle">
            <h4 class="text-sm font-medium mb-3">Visible Columns</h4>
            <div 
              v-for="col in columns"
              :key="col.field"
              class="flex items-center gap-2 mb-2"
            >
              <Checkbox
                :modelValue="!col.hidden"
                :binary="true"
                @update:modelValue="toggleColumn(col)"
              />
              <label class="text-sm">{{ col.header }}</label>
            </div>
          </div>
        </OverlayPanel>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      :value="filteredData"
      :paginator="paginator"
      :rows="rows"
      :rowsPerPageOptions="rowsPerPageOptions"
      :loading="loading"
      :selection="selection"
      :selectionMode="selectionMode"
      :dataKey="dataKey"
      :sortField="sortField"
      :sortOrder="sortOrder"
      :globalFilterFields="globalFilterFields"
      :filters="filters"
      :rowHover="rowHover"
      :stripedRows="stripedRows"
      :showGridlines="showGridlines"
      :size="size"
      :scrollable="scrollable"
      :scrollHeight="scrollHeight"
      :responsiveLayout="responsiveLayout"
      :breakpoint="breakpoint"
      @update:selection="$emit('update:selection', $event)"
      @row-click="handleRowClick"
      @row-dblclick="handleRowDblClick"
      @sort="handleSort"
      class="table-widget__table"
    >
      <!-- Column templates -->
      <Column
        v-for="col in visibleColumns"
        :key="col.field"
        :field="col.field"
        :header="col.header"
        :sortable="col.sortable"
        :style="col.style"
        :headerStyle="col.headerStyle"
        :class="col.class"
        :headerClass="col.headerClass"
      >
        <!-- Custom body template -->
        <template #body="slotProps" v-if="col.template">
          <component 
            :is="col.template"
            :data="slotProps.data"
            :field="col.field"
            :index="slotProps.index"
          />
        </template>
        
        <!-- Status badge template -->
        <template #body="slotProps" v-else-if="col.type === 'badge'">
          <Badge 
            :value="slotProps.data[col.field]"
            :severity="getBadgeSeverity(slotProps.data[col.field], col.badgeMap)"
          />
        </template>
        
        <!-- Avatar template -->
        <template #body="slotProps" v-else-if="col.type === 'avatar'">
          <div class="flex items-center gap-2">
            <Avatar 
              :image="slotProps.data[col.avatarField || 'avatar']"
              :label="getInitials(slotProps.data[col.field])"
              shape="circle"
              size="small"
            />
            <span>{{ slotProps.data[col.field] }}</span>
          </div>
        </template>
        
        <!-- Date template -->
        <template #body="slotProps" v-else-if="col.type === 'date'">
          <span>{{ formatDate(slotProps.data[col.field], col.dateFormat) }}</span>
        </template>
        
        <!-- Number template -->
        <template #body="slotProps" v-else-if="col.type === 'number'">
          <span>{{ formatNumber(slotProps.data[col.field], col.numberFormat) }}</span>
        </template>
        
        <!-- Currency template -->
        <template #body="slotProps" v-else-if="col.type === 'currency'">
          <span>{{ formatCurrency(slotProps.data[col.field], col.currency) }}</span>
        </template>
        
        <!-- Progress template -->
        <template #body="slotProps" v-else-if="col.type === 'progress'">
          <div class="flex items-center gap-2">
            <ProgressBar 
              :value="slotProps.data[col.field]"
              :showValue="false"
              style="height: 0.5rem; width: 100px"
            />
            <span class="text-sm">{{ slotProps.data[col.field] }}%</span>
          </div>
        </template>
        
        <!-- Boolean template -->
        <template #body="slotProps" v-else-if="col.type === 'boolean'">
          <i 
            :class="[
              slotProps.data[col.field] ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-red-500'
            ]"
          ></i>
        </template>
        
        <!-- Link template -->
        <template #body="slotProps" v-else-if="col.type === 'link'">
          <a 
            :href="slotProps.data[col.linkField || col.field]"
            target="_blank"
            class="text-blue-600 hover:underline"
          >
            {{ slotProps.data[col.field] }}
          </a>
        </template>
        
        <!-- Actions template -->
        <template #body="slotProps" v-else-if="col.type === 'actions'">
          <div class="flex items-center gap-1">
            <Button
              v-for="action in getRowActions(slotProps.data)"
              :key="action.id"
              :icon="action.icon"
              :severity="action.severity || 'secondary'"
              text
              rounded
              size="small"
              @click.stop="handleRowAction(slotProps.data, action)"
              v-tooltip="action.label"
            />
          </div>
        </template>
      </Column>
      
      <!-- Selection column -->
      <Column 
        v-if="selectionMode"
        selectionMode="multiple"
        headerStyle="width: 3rem"
      ></Column>
      
      <!-- Expansion template -->
      <template #expansion="slotProps" v-if="expandable">
        <div class="table-widget__expansion">
          <slot name="expansion" :data="slotProps.data">
            <pre>{{ JSON.stringify(slotProps.data, null, 2) }}</pre>
          </slot>
        </div>
      </template>
      
      <!-- Empty message -->
      <template #empty>
        <div class="table-widget__empty">
          <i class="pi pi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
          <p class="text-gray-500 dark:text-gray-400">{{ emptyMessage }}</p>
        </div>
      </template>
      
      <!-- Loading -->
      <template #loading>
        <div class="table-widget__loading">
          <ProgressSpinner />
        </div>
      </template>
    </DataTable>

    <!-- Footer -->
    <div class="table-widget__footer" v-if="showFooter">
      <slot name="footer">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ firstRecord }} to {{ lastRecord }} of {{ totalRecords }} entries
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import Avatar from 'primevue/avatar'
import Checkbox from 'primevue/checkbox'
import Menu from 'primevue/menu'
import OverlayPanel from 'primevue/overlaypanel'
import ProgressBar from 'primevue/progressbar'
import ProgressSpinner from 'primevue/progressspinner'

export interface TableColumn {
  field: string
  header: string
  sortable?: boolean
  hidden?: boolean
  type?: 'text' | 'badge' | 'avatar' | 'date' | 'number' | 'currency' | 'progress' | 'boolean' | 'link' | 'actions'
  style?: any
  headerStyle?: any
  class?: string
  headerClass?: string
  template?: any
  avatarField?: string
  linkField?: string
  dateFormat?: string
  numberFormat?: any
  currency?: string
  badgeMap?: Record<string, string>
}

export interface TableAction {
  id: string
  label: string
  icon: string
  severity?: string
  command?: (data: any) => void
}

interface Props {
  title?: string
  data: any[]
  columns: TableColumn[]
  loading?: boolean
  compact?: boolean
  
  // Features
  paginator?: boolean
  rows?: number
  rowsPerPageOptions?: number[]
  selectionMode?: 'single' | 'multiple' | null
  selection?: any
  dataKey?: string
  expandable?: boolean
  
  // Search & Filter
  showSearch?: boolean
  globalFilterFields?: string[]
  filters?: any
  
  // Actions
  showAdd?: boolean
  showRefresh?: boolean
  showExport?: boolean
  showColumnToggle?: boolean
  rowActions?: TableAction[]
  
  // Appearance
  stripedRows?: boolean
  rowHover?: boolean
  showGridlines?: boolean
  size?: 'small' | 'normal' | 'large'
  scrollable?: boolean
  scrollHeight?: string
  responsiveLayout?: 'scroll' | 'stack'
  breakpoint?: string
  
  // Footer
  showFooter?: boolean
  emptyMessage?: string
  
  // Sort
  sortField?: string
  sortOrder?: number
}

const props = withDefaults(defineProps<Props>(), {
  data: () => [],
  columns: () => [],
  loading: false,
  compact: false,
  paginator: true,
  rows: 10,
  rowsPerPageOptions: () => [5, 10, 25, 50],
  selectionMode: null,
  dataKey: 'id',
  expandable: false,
  showSearch: true,
  showAdd: false,
  showRefresh: false,
  showExport: false,
  showColumnToggle: false,
  stripedRows: false,
  rowHover: true,
  showGridlines: false,
  size: 'normal',
  scrollable: false,
  scrollHeight: '400px',
  responsiveLayout: 'scroll',
  breakpoint: '960px',
  showFooter: true,
  emptyMessage: 'No records found',
  sortOrder: 1
})

const emit = defineEmits<{
  'update:selection': [value: any]
  'row-click': [data: any]
  'row-dblclick': [data: any]
  'row-action': [data: any, action: TableAction]
  'add': []
  'refresh': []
  'export': [format: string]
  'search': [query: string]
  'sort': [event: any]
}>()

// Refs
const exportMenu = ref()
const columnMenu = ref()

// State
const searchQuery = ref('')
const columnStates = ref<Record<string, boolean>>({})

// Export menu items
const exportMenuItems = [
  {
    label: 'Export as CSV',
    icon: 'pi pi-file',
    command: () => handleExport('csv')
  },
  {
    label: 'Export as Excel',
    icon: 'pi pi-file-excel',
    command: () => handleExport('xlsx')
  },
  {
    label: 'Export as PDF',
    icon: 'pi pi-file-pdf',
    command: () => handleExport('pdf')
  }
]

// Computed
const visibleColumns = computed(() => {
  return props.columns.filter(col => !col.hidden && !columnStates.value[col.field])
})

const filteredData = computed(() => {
  if (!searchQuery.value) return props.data
  
  const query = searchQuery.value.toLowerCase()
  return props.data.filter(item => {
    return props.globalFilterFields?.some(field => {
      const value = item[field]
      if (value == null) return false
      return value.toString().toLowerCase().includes(query)
    }) ?? true
  })
})

const totalRecords = computed(() => filteredData.value.length)

const firstRecord = computed(() => {
  if (!props.paginator) return 1
  return 1 // This would need to be calculated based on current page
})

const lastRecord = computed(() => {
  if (!props.paginator) return totalRecords.value
  return Math.min(props.rows, totalRecords.value)
})

// Methods
const handleSearch = () => {
  emit('search', searchQuery.value)
}

const handleRefresh = () => {
  emit('refresh')
}

const handleAdd = () => {
  emit('add')
}

const handleExport = (format: string) => {
  emit('export', format)
  // Implement actual export logic here
}

const toggleExportMenu = (event: Event) => {
  exportMenu.value?.toggle(event)
}

const toggleColumnMenu = (event: Event) => {
  columnMenu.value?.toggle(event)
}

const toggleColumn = (column: TableColumn) => {
  columnStates.value[column.field] = !columnStates.value[column.field]
}

const handleRowClick = (event: any) => {
  emit('row-click', event.data)
}

const handleRowDblClick = (event: any) => {
  emit('row-dblclick', event.data)
}

const handleRowAction = (data: any, action: TableAction) => {
  action.command?.(data)
  emit('row-action', data, action)
}

const handleSort = (event: any) => {
  emit('sort', event)
}

const getRowActions = (data: any) => {
  return props.rowActions || []
}

// Formatting helpers
const getBadgeSeverity = (value: string, map?: Record<string, string>) => {
  if (!map) return 'info'
  return map[value] || 'secondary'
}

const getInitials = (name: string) => {
  if (!name) return ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const formatDate = (date: any, format?: string) => {
  if (!date) return ''
  const d = new Date(date)
  
  if (format === 'short') {
    return d.toLocaleDateString()
  } else if (format === 'long') {
    return d.toLocaleString()
  } else if (format === 'relative') {
    const now = new Date()
    const seconds = Math.floor((now.getTime() - d.getTime()) / 1000)
    if (seconds < 60) return 'just now'
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
    return `${Math.floor(seconds / 86400)}d ago`
  }
  
  return d.toLocaleDateString()
}

const formatNumber = (value: number, format?: any) => {
  if (value == null) return ''
  
  if (format?.notation === 'compact') {
    return new Intl.NumberFormat('en-US', {
      notation: 'compact',
      maximumFractionDigits: 1
    }).format(value)
  }
  
  return new Intl.NumberFormat('en-US', format).format(value)
}

const formatCurrency = (value: number, currency = 'USD') => {
  if (value == null) return ''
  
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency
  }).format(value)
}

// Expose methods
defineExpose({
  refresh: handleRefresh,
  exportData: handleExport
})
</script>

<style scoped lang="scss">
.table-widget {
  @apply bg-white dark:bg-gray-800 rounded-2xl overflow-hidden;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  
  &--compact {
    .table-widget__header {
      @apply p-4;
    }
    
    :deep(.p-datatable) {
      .p-datatable-thead > tr > th {
        @apply py-2;
      }
      
      .p-datatable-tbody > tr > td {
        @apply py-2;
      }
    }
  }
  
  &__header {
    @apply flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700;
    
    &-left {
      @apply flex items-center;
    }
    
    &-right {
      @apply flex items-center gap-3;
    }
  }
  
  &__title {
    @apply text-lg font-semibold text-gray-900 dark:text-white;
  }
  
  &__search {
    @apply relative;
    
    &-icon {
      @apply absolute left-3 top-1/2 -translate-y-1/2 text-gray-400;
    }
    
    &-input {
      @apply pl-10;
      
      :deep(.p-inputtext) {
        @apply border-gray-200 dark:border-gray-700;
      }
    }
  }
  
  &__actions {
    @apply flex items-center gap-2;
  }
  
  &__column-toggle {
    @apply p-3 min-w-[200px];
  }
  
  &__table {
    :deep(.p-datatable) {
      @apply border-0;
      
      .p-datatable-header {
        @apply bg-transparent border-0 p-0;
      }
      
      .p-datatable-thead > tr > th {
        @apply bg-gray-50 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300 font-medium border-gray-200 dark:border-gray-700;
      }
      
      .p-datatable-tbody > tr {
        @apply transition-colors duration-150;
        
        &:hover {
          @apply bg-gray-50 dark:bg-gray-900/30;
        }
        
        &.p-highlight {
          @apply bg-blue-50 dark:bg-blue-900/20;
        }
        
        > td {
          @apply border-gray-200 dark:border-gray-700;
        }
      }
      
      .p-datatable-emptymessage > td {
        @apply py-8;
      }
      
      .p-paginator {
        @apply bg-transparent border-0 px-6 py-4;
        
        .p-paginator-element {
          @apply text-gray-600 dark:text-gray-400;
          
          &.p-highlight {
            @apply bg-blue-500 text-white;
          }
        }
      }
    }
    
    // Scrollable styles
    &.p-datatable-scrollable {
      :deep(.p-datatable-wrapper) {
        @apply border-x border-gray-200 dark:border-gray-700;
      }
    }
    
    // Gridlines styles
    &.p-datatable-gridlines {
      :deep(.p-datatable-tbody > tr > td) {
        @apply border border-gray-200 dark:border-gray-700;
      }
    }
  }
  
  &__expansion {
    @apply p-4 bg-gray-50 dark:bg-gray-900/50;
  }
  
  &__empty {
    @apply text-center py-8;
  }
  
  &__loading {
    @apply flex justify-center py-8;
  }
  
  &__footer {
    @apply px-6 py-4 border-t border-gray-200 dark:border-gray-700;
  }
}
</style>
