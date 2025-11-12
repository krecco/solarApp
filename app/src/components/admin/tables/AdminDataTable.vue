<template>
  <div class="admin-data-table">
    <DataTable
      v-model:filters="filters"
      v-model:selection="selectedItems"
      :value="data"
      :paginator="paginator"
      :rows="rows"
      :rowsPerPageOptions="rowsPerPageOptions"
      :loading="loading"
      :globalFilterFields="globalFilterFields"
      :dataKey="dataKey"
      :stripedRows="stripedRows"
      :showGridlines="showGridlines"
      :removableSort="true"
      :resizableColumns="resizableColumns"
      :reorderableColumns="reorderableColumns"
      :exportFilename="exportFilename"
      :class="tableClass"
      @page="onPage"
      @sort="onSort"
      @filter="onFilter"
    >
      <!-- Header Template -->
      <template #header v-if="showHeader">
        <div class="table-header">
          <div class="table-header-left">
            <slot name="header-left">
              <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText
                  v-model="filters['global'].value"
                  :placeholder="searchPlaceholder"
                  class="table-search"
                />
              </span>
            </slot>
          </div>
          
          <div class="table-header-right">
            <slot name="header-right">
              <Button
                v-if="showExport"
                icon="pi pi-download"
                label="Export"
                text
                @click="exportData"
              />
              <Button
                v-if="showRefresh"
                icon="pi pi-refresh"
                text
                @click="$emit('refresh')"
              />
              <slot name="header-actions" />
            </slot>
          </div>
        </div>
      </template>

      <!-- Empty Template -->
      <template #empty>
        <div class="empty-state">
          <i :class="emptyIcon" class="empty-icon"></i>
          <p class="empty-message">{{ emptyMessage }}</p>
          <slot name="empty-action" />
        </div>
      </template>

      <!-- Loading Template -->
      <template #loading>
        <div class="loading-state">
          <ProgressSpinner />
          <p>{{ loadingMessage }}</p>
        </div>
      </template>

      <!-- Column Templates -->
      <slot />
      
      <!-- Expansion Template -->
      <template #expansion="slotProps" v-if="$slots.expansion">
        <slot name="expansion" v-bind="slotProps" />
      </template>
    </DataTable>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import DataTable, { DataTableFilterMeta } from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
// FilterMatchMode not needed - using string directly

interface Props {
  data: any[]
  dataKey?: string
  loading?: boolean
  paginator?: boolean
  rows?: number
  rowsPerPageOptions?: number[]
  globalFilterFields?: string[]
  searchPlaceholder?: string
  emptyMessage?: string
  emptyIcon?: string
  loadingMessage?: string
  showHeader?: boolean
  showExport?: boolean
  showRefresh?: boolean
  stripedRows?: boolean
  showGridlines?: boolean
  resizableColumns?: boolean
  reorderableColumns?: boolean
  exportFilename?: string
  tableClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  dataKey: 'id',
  loading: false,
  paginator: true,
  rows: 10,
  rowsPerPageOptions: () => [5, 10, 25, 50],
  globalFilterFields: () => [],
  searchPlaceholder: 'Search...',
  emptyMessage: 'No records found',
  emptyIcon: 'pi pi-inbox',
  loadingMessage: 'Loading data...',
  showHeader: true,
  showExport: true,
  showRefresh: true,
  stripedRows: true,
  showGridlines: false,
  resizableColumns: true,
  reorderableColumns: true,
  exportFilename: 'data-export',
  tableClass: ''
})

const emit = defineEmits<{
  refresh: []
  page: [event: any]
  sort: [event: any]
  filter: [event: any]
  'update:selection': [value: any[]]
}>()

const selectedItems = ref<any[]>([])

const filters = ref<DataTableFilterMeta>({
  global: { value: null, matchMode: 'contains' }
})

const onPage = (event: any) => {
  emit('page', event)
}

const onSort = (event: any) => {
  emit('sort', event)
}

const onFilter = (event: any) => {
  emit('filter', event)
}

const exportData = () => {
  const dt = document.querySelector('.p-datatable')
  if (dt) {
    const exportData = props.data.map(item => {
      const row: any = {}
      props.globalFilterFields.forEach(field => {
        row[field] = item[field]
      })
      return row
    })
    
    // Convert to CSV
    const csv = convertToCSV(exportData)
    downloadCSV(csv, props.exportFilename)
  }
}

const convertToCSV = (data: any[]) => {
  if (data.length === 0) return ''
  
  const headers = Object.keys(data[0])
  const csvHeaders = headers.join(',')
  
  const csvRows = data.map(row => {
    return headers.map(header => {
      const value = row[header]
      return typeof value === 'string' && value.includes(',') 
        ? `"${value}"` 
        : value
    }).join(',')
  })
  
  return [csvHeaders, ...csvRows].join('\n')
}

const downloadCSV = (csv: string, filename: string) => {
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${filename}.csv`
  link.click()
  window.URL.revokeObjectURL(url)
}

defineExpose({
  exportData,
  selectedItems
})
</script>

<style scoped lang="scss">
.admin-data-table {
  .table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
    background: var(--surface-ground);
    border: 1px solid var(--surface-border);
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    
    .table-header-left {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex: 1;
      
      .table-search {
        width: 100%;
        max-width: 400px;
      }
    }
    
    .table-header-right {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
  }
  
  :deep(.p-datatable) {
    .p-datatable-header {
      border-top: none;
      border-radius: 0;
    }
  }
  
  .empty-state {
    padding: 3rem;
    text-align: center;
    
    .empty-icon {
      font-size: 4rem;
      color: var(--text-color-secondary);
      opacity: 0.5;
    }
    
    .empty-message {
      margin-top: 1rem;
      color: var(--text-color-secondary);
      font-size: 1.125rem;
    }
  }
  
  .loading-state {
    padding: 3rem;
    text-align: center;
    
    p {
      margin-top: 1rem;
      color: var(--text-color-secondary);
    }
  }
}

@media (max-width: 768px) {
  .admin-data-table {
    .table-header {
      flex-direction: column;
      align-items: stretch;
      
      .table-header-left {
        width: 100%;
        
        .table-search {
          max-width: none;
        }
      }
      
      .table-header-right {
        justify-content: flex-end;
      }
    }
  }
}
</style>
