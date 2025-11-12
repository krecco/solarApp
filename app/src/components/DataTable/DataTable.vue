<template>
  <div class="data-table">
    <!-- Toolbar -->
    <div v-if="showToolbar" class="data-table__toolbar">
      <div class="data-table__toolbar-left">
        <!-- Search -->
        <span v-if="searchable" class="p-input-icon-left">
          <i class="pi pi-search" />
          <InputText
            v-model="searchQuery"
            :placeholder="searchPlaceholder"
            class="data-table__search"
            @input="onSearch"
          />
        </span>
        
        <!-- Bulk Actions -->
        <div v-if="selection && selection.length > 0 && bulkActions.length > 0" class="data-table__bulk-actions">
          <Dropdown
            v-model="selectedBulkAction"
            :options="bulkActions"
            optionLabel="label"
            optionValue="value"
            placeholder="Bulk Actions"
            class="data-table__bulk-dropdown"
            @change="handleBulkAction"
          />
          <span class="data-table__selection-count">
            {{ selection.length }} {{ selection.length === 1 ? 'item' : 'items' }} selected
          </span>
        </div>
        
        <!-- Custom left toolbar content -->
        <slot name="toolbar-left" />
      </div>
      
      <div class="data-table__toolbar-right">
        <!-- Custom right toolbar content -->
        <slot name="toolbar-right" />
        
        <!-- Export Button -->
        <Button
          v-if="exportable"
          icon="pi pi-download"
          label="Export"
          severity="secondary"
          outlined
          @click="handleExport"
        />
        
        <!-- Refresh Button -->
        <Button
          v-if="refreshable"
          icon="pi pi-refresh"
          severity="secondary"
          outlined
          :loading="loading"
          @click="handleRefresh"
        />
        
        <!-- Column Toggle -->
        <MultiSelect
          v-if="columnToggle"
          v-model="visibleColumns"
          :options="columns"
          optionLabel="header"
          placeholder="Columns"
          :maxSelectedLabels="0"
          selectedItemsLabel="{0} columns"
          class="data-table__column-toggle"
        />
      </div>
    </div>
    
    <!-- DataTable -->
    <DataTablePrime
      v-model:selection="selection"
      v-model:filters="filters"
      :value="filteredData"
      :paginator="paginator"
      :rows="rows"
      :rowsPerPageOptions="rowsPerPageOptions"
      :totalRecords="totalRecords"
      :lazy="lazy"
      :loading="loading"
      :sortField="sortField"
      :sortOrder="sortOrder"
      :multiSortMeta="multiSortMeta"
      :selectionMode="selectionMode"
      :dataKey="dataKey"
      :metaKeySelection="metaKeySelection"
      :contextMenu="contextMenu"
      :contextMenuSelection="contextMenuSelection"
      :rowHover="rowHover"
      :csvSeparator="csvSeparator"
      :exportFilename="exportFilename"
      :autoLayout="autoLayout"
      :resizableColumns="resizableColumns"
      :columnResizeMode="columnResizeMode"
      :reorderableColumns="reorderableColumns"
      :expandedRows="expandedRows"
      :rowGroupMode="rowGroupMode"
      :groupRowsBy="groupRowsBy"
      :expandableRowGroups="expandableRowGroups"
      :responsiveLayout="responsiveLayout"
      :breakpoint="breakpoint"
      :stripedRows="stripedRows"
      :size="size"
      :showGridlines="showGridlines"
      :stateStorage="stateStorage"
      :stateKey="stateKey"
      :editMode="editMode"
      :class="tableClass"
      @sort="onSort"
      @page="onPage"
      @filter="onFilter"
      @row-select="onRowSelect"
      @row-unselect="onRowUnselect"
      @row-select-all="onRowSelectAll"
      @row-unselect-all="onRowUnselectAll"
      @row-expand="onRowExpand"
      @row-collapse="onRowCollapse"
      @row-click="onRowClick"
      @row-dblclick="onRowDblClick"
      @row-contextmenu="onRowContextMenu"
      @cell-edit-complete="onCellEditComplete"
      @row-edit-save="onRowEditSave"
      @row-edit-cancel="onRowEditCancel"
    >
      <!-- Column Templates -->
      <Column
        v-if="selectable"
        selectionMode="multiple"
        :headerStyle="{ width: '3rem' }"
        :frozen="frozenSelection"
      />
      
      <Column
        v-for="col in displayColumns"
        :key="col.field"
        :field="col.field"
        :header="col.header"
        :sortable="col.sortable !== false"
        :style="col.style"
        :headerStyle="col.headerStyle"
        :bodyStyle="col.bodyStyle"
        :class="col.class"
        :headerClass="col.headerClass"
        :bodyClass="col.bodyClass"
        :frozen="col.frozen"
        :alignFrozen="col.alignFrozen"
        :hidden="col.hidden"
        :expander="col.expander"
        :colspan="col.colspan"
        :rowspan="col.rowspan"
        :rowReorder="col.rowReorder"
        :rowEditor="col.rowEditor"
        :filterField="col.filterField || col.field"
        :filterMatchMode="col.filterMatchMode"
        :excludeGlobalFilter="col.excludeGlobalFilter"
        :filterHeaderStyle="col.filterHeaderStyle"
        :filterHeaderClass="col.filterHeaderClass"
        :showFilterMatchModes="col.showFilterMatchModes"
        :showFilterOperator="col.showFilterOperator"
        :showClearButton="col.showClearButton"
        :showApplyButton="col.showApplyButton"
        :showFilterMenu="col.showFilterMenu"
        :showAddButton="col.showAddButton"
        :dataType="col.dataType"
      >
        <!-- Custom column body -->
        <template v-if="col.body" #body="slotProps">
          <component :is="col.body" v-bind="slotProps" />
        </template>
        
        <!-- Custom column header -->
        <template v-if="col.headerTemplate" #header="slotProps">
          <component :is="col.headerTemplate" v-bind="slotProps" />
        </template>
        
        <!-- Custom column filter -->
        <template v-if="col.filterTemplate" #filter="slotProps">
          <component :is="col.filterTemplate" v-bind="slotProps" />
        </template>
        
        <!-- Custom column editor -->
        <template v-if="col.editor" #editor="slotProps">
          <component :is="col.editor" v-bind="slotProps" />
        </template>
        
        <!-- Default templates for common data types -->
        <template v-else-if="col.type === 'boolean'" #body="slotProps">
          <i :class="slotProps.data[col.field] ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-red-500'" />
        </template>
        
        <template v-else-if="col.type === 'date'" #body="slotProps">
          {{ formatDate(slotProps.data[col.field]) }}
        </template>
        
        <template v-else-if="col.type === 'currency'" #body="slotProps">
          {{ formatCurrency(slotProps.data[col.field]) }}
        </template>
        
        <template v-else-if="col.type === 'percentage'" #body="slotProps">
          {{ formatPercentage(slotProps.data[col.field]) }}
        </template>
        
        <template v-else-if="col.type === 'image'" #body="slotProps">
          <img :src="slotProps.data[col.field]" :alt="col.header" class="data-table__image" />
        </template>
        
        <template v-else-if="col.type === 'badge'" #body="slotProps">
          <Tag :value="slotProps.data[col.field]" :severity="getBadgeSeverity(slotProps.data[col.field], col.badgeMap)" />
        </template>
        
        <template v-else-if="col.type === 'actions'" #body="slotProps">
          <div class="data-table__actions">
            <Button
              v-for="action in getRowActions(slotProps.data, col.actions)"
              :key="action.icon"
              :icon="action.icon"
              :severity="action.severity"
              :disabled="action.disabled"
              rounded
              text
              size="small"
              @click="handleRowAction(action, slotProps.data)"
            />
          </div>
        </template>
      </Column>
      
      <!-- Expansion Template -->
      <template v-if="expandable" #expansion="slotProps">
        <slot name="expansion" v-bind="slotProps" />
      </template>
      
      <!-- Empty Template -->
      <template #empty>
        <slot name="empty">
          <div class="data-table__empty">
            <i class="pi pi-inbox" />
            <p>{{ emptyMessage }}</p>
          </div>
        </slot>
      </template>
      
      <!-- Loading Template -->
      <template #loading>
        <slot name="loading">
          <div class="data-table__loading">
            <i class="pi pi-spin pi-spinner" />
            <p>{{ loadingMessage }}</p>
          </div>
        </slot>
      </template>
    </DataTablePrime>
    
    <!-- Context Menu -->
    <ContextMenu v-if="contextMenu" ref="cm" :model="contextMenuItems" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import DataTablePrime from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import ContextMenu from 'primevue/contextmenu';
import Tag from 'primevue/tag';

/**
 * Column Definition Interface
 */
export interface DataTableColumn {
  field: string;
  header: string;
  sortable?: boolean;
  type?: 'text' | 'number' | 'boolean' | 'date' | 'currency' | 'percentage' | 'image' | 'badge' | 'actions';
  style?: any;
  headerStyle?: any;
  bodyStyle?: any;
  class?: string;
  headerClass?: string;
  bodyClass?: string;
  frozen?: boolean;
  alignFrozen?: 'left' | 'right';
  hidden?: boolean;
  expander?: boolean;
  colspan?: number;
  rowspan?: number;
  rowReorder?: boolean;
  rowEditor?: boolean;
  filterField?: string;
  filterMatchMode?: string;
  excludeGlobalFilter?: boolean;
  body?: any;
  headerTemplate?: any;
  filterTemplate?: any;
  editor?: any;
  badgeMap?: Record<string, string>;
  actions?: DataTableAction[];
  dataType?: string;
  showFilterMatchModes?: boolean;
  showFilterOperator?: boolean;
  showClearButton?: boolean;
  showApplyButton?: boolean;
  showFilterMenu?: boolean;
  showAddButton?: boolean;
  filterHeaderStyle?: any;
  filterHeaderClass?: string;
}

/**
 * Action Definition Interface
 */
export interface DataTableAction {
  icon: string;
  label?: string;
  severity?: string;
  disabled?: boolean | ((data: any) => boolean);
  visible?: boolean | ((data: any) => boolean);
  command: (data: any) => void;
}

/**
 * Bulk Action Interface
 */
export interface BulkAction {
  label: string;
  value: string;
  icon?: string;
  severity?: string;
  command: (selection: any[]) => void;
}

/**
 * DataTable Props
 */
interface DataTableProps {
  // Data
  data: any[];
  columns: DataTableColumn[];
  dataKey?: string;
  
  // Features
  selectable?: boolean;
  selectionMode?: 'single' | 'multiple';
  selection?: any | any[];
  metaKeySelection?: boolean;
  
  // Pagination
  paginator?: boolean;
  rows?: number;
  rowsPerPageOptions?: number[];
  totalRecords?: number;
  lazy?: boolean;
  
  // Sorting
  sortField?: string;
  sortOrder?: number;
  multiSortMeta?: any[];
  
  // Filtering
  searchable?: boolean;
  searchPlaceholder?: string;
  filters?: any;
  filterDelay?: number;
  
  // Loading
  loading?: boolean;
  loadingMessage?: string;
  
  // Empty
  emptyMessage?: string;
  
  // Toolbar
  showToolbar?: boolean;
  exportable?: boolean;
  exportFilename?: string;
  csvSeparator?: string;
  refreshable?: boolean;
  columnToggle?: boolean;
  bulkActions?: BulkAction[];
  
  // Expansion
  expandable?: boolean;
  expandedRows?: any;
  rowGroupMode?: string;
  groupRowsBy?: string;
  expandableRowGroups?: boolean;
  
  // Context Menu
  contextMenu?: boolean;
  contextMenuItems?: any[];
  contextMenuSelection?: any;
  
  // Appearance
  stripedRows?: boolean;
  rowHover?: boolean;
  size?: 'small' | 'normal' | 'large';
  showGridlines?: boolean;
  responsiveLayout?: 'stack' | 'scroll';
  breakpoint?: string;
  autoLayout?: boolean;
  
  // Columns
  resizableColumns?: boolean;
  columnResizeMode?: 'fit' | 'expand';
  reorderableColumns?: boolean;
  frozenSelection?: boolean;
  
  // State
  stateStorage?: 'session' | 'local';
  stateKey?: string;
  
  // Edit
  editMode?: 'cell' | 'row';
}

const props = withDefaults(defineProps<DataTableProps>(), {
  dataKey: 'id',
  selectable: false,
  selectionMode: 'multiple',
  selection: () => [],
  metaKeySelection: true,
  paginator: true,
  rows: 10,
  rowsPerPageOptions: () => [5, 10, 20, 50, 100],
  totalRecords: 0,
  lazy: false,
  sortField: '',
  sortOrder: 1,
  multiSortMeta: () => [],
  searchable: true,
  searchPlaceholder: 'Search...',
  filters: () => ({}),
  filterDelay: 300,
  loading: false,
  loadingMessage: 'Loading...',
  emptyMessage: 'No records found',
  showToolbar: true,
  exportable: false,
  exportFilename: 'data',
  csvSeparator: ',',
  refreshable: false,
  columnToggle: false,
  bulkActions: () => [],
  expandable: false,
  expandedRows: null,
  rowGroupMode: '',
  groupRowsBy: '',
  expandableRowGroups: false,
  contextMenu: false,
  contextMenuItems: () => [],
  contextMenuSelection: null,
  stripedRows: true,
  rowHover: true,
  size: 'normal',
  showGridlines: false,
  responsiveLayout: 'scroll',
  breakpoint: '960px',
  autoLayout: false,
  resizableColumns: false,
  columnResizeMode: 'fit',
  reorderableColumns: false,
  frozenSelection: false,
  stateStorage: undefined,
  stateKey: undefined,
  editMode: undefined
});

const emit = defineEmits<{
  'update:selection': [value: any];
  'update:filters': [value: any];
  'update:expandedRows': [value: any];
  'sort': [event: any];
  'page': [event: any];
  'filter': [event: any];
  'search': [query: string];
  'refresh': [];
  'export': [data: any[]];
  'bulk-action': [action: string, selection: any[]];
  'row-action': [action: DataTableAction, data: any];
  'row-select': [event: any];
  'row-unselect': [event: any];
  'row-select-all': [event: any];
  'row-unselect-all': [event: any];
  'row-expand': [event: any];
  'row-collapse': [event: any];
  'row-click': [event: any];
  'row-dblclick': [event: any];
  'row-contextmenu': [event: any];
  'cell-edit-complete': [event: any];
  'row-edit-save': [event: any];
  'row-edit-cancel': [event: any];
}>();

/**
 * Local state
 */
const searchQuery = ref('');
const selectedBulkAction = ref(null);
const visibleColumns = ref<DataTableColumn[]>([...props.columns]);
const filters = ref(props.filters);
const selection = ref(props.selection);
const expandedRows = ref(props.expandedRows);
const contextMenuSelection = ref(props.contextMenuSelection);
const cm = ref();

/**
 * Computed properties
 */
const displayColumns = computed(() => {
  if (!props.columnToggle) return props.columns;
  return props.columns.filter(col => visibleColumns.value.includes(col));
});

const filteredData = computed(() => {
  if (!props.searchable || !searchQuery.value || props.lazy) {
    return props.data;
  }
  
  const query = searchQuery.value.toLowerCase();
  return props.data.filter(item => {
    return props.columns.some(col => {
      if (col.excludeGlobalFilter) return false;
      const value = item[col.field];
      if (value === null || value === undefined) return false;
      return String(value).toLowerCase().includes(query);
    });
  });
});

const tableClass = computed(() => ({
  'p-datatable-sm': props.size === 'small',
  'p-datatable-lg': props.size === 'large',
  'p-datatable-striped': props.stripedRows,
  'p-datatable-gridlines': props.showGridlines
}));

/**
 * Watch for prop changes
 */
watch(() => props.selection, (newVal) => {
  selection.value = newVal;
});

watch(() => props.filters, (newVal) => {
  filters.value = newVal;
});

watch(() => props.expandedRows, (newVal) => {
  expandedRows.value = newVal;
});

watch(selection, (newVal) => {
  emit('update:selection', newVal);
});

watch(filters, (newVal) => {
  emit('update:filters', newVal);
});

watch(expandedRows, (newVal) => {
  emit('update:expandedRows', newVal);
});

/**
 * Methods
 */
const onSearch = () => {
  emit('search', searchQuery.value);
};

const handleRefresh = () => {
  emit('refresh');
};

const handleExport = () => {
  const exportData = selection.value.length > 0 ? selection.value : filteredData.value;
  emit('export', exportData);
  
  // Default CSV export
  if (props.exportable) {
    exportCSV(exportData);
  }
};

const exportCSV = (data: any[]) => {
  const headers = props.columns
    .filter(col => !col.hidden && col.type !== 'actions')
    .map(col => col.header);
  
  const rows = data.map(item => {
    return props.columns
      .filter(col => !col.hidden && col.type !== 'actions')
      .map(col => {
        const value = item[col.field];
        if (value === null || value === undefined) return '';
        if (col.type === 'date') return formatDate(value);
        if (col.type === 'currency') return formatCurrency(value);
        if (col.type === 'percentage') return formatPercentage(value);
        return String(value);
      });
  });
  
  const csv = [
    headers.join(props.csvSeparator),
    ...rows.map(row => row.join(props.csvSeparator))
  ].join('\n');
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `${props.exportFilename}.csv`;
  link.click();
};

const handleBulkAction = () => {
  if (!selectedBulkAction.value) return;
  
  const action = props.bulkActions.find(a => a.value === selectedBulkAction.value);
  if (action) {
    action.command(selection.value);
    emit('bulk-action', action.value, selection.value);
  }
  
  selectedBulkAction.value = null;
};

const handleRowAction = (action: DataTableAction, data: any) => {
  action.command(data);
  emit('row-action', action, data);
};

const getRowActions = (data: any, actions?: DataTableAction[]) => {
  if (!actions) return [];
  
  return actions.filter(action => {
    if (typeof action.visible === 'function') {
      return action.visible(data);
    }
    return action.visible !== false;
  });
};

const getBadgeSeverity = (value: any, badgeMap?: Record<string, string>) => {
  if (!badgeMap) return 'info';
  return badgeMap[value] || 'info';
};

const formatDate = (value: any) => {
  if (!value) return '';
  const date = new Date(value);
  return date.toLocaleDateString();
};

const formatCurrency = (value: any) => {
  if (value === null || value === undefined) return '';
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
};

const formatPercentage = (value: any) => {
  if (value === null || value === undefined) return '';
  return `${(value * 100).toFixed(2)}%`;
};

// Event handlers
const onSort = (event: any) => emit('sort', event);
const onPage = (event: any) => emit('page', event);
const onFilter = (event: any) => emit('filter', event);
const onRowSelect = (event: any) => emit('row-select', event);
const onRowUnselect = (event: any) => emit('row-unselect', event);
const onRowSelectAll = (event: any) => emit('row-select-all', event);
const onRowUnselectAll = (event: any) => emit('row-unselect-all', event);
const onRowExpand = (event: any) => emit('row-expand', event);
const onRowCollapse = (event: any) => emit('row-collapse', event);
const onRowClick = (event: any) => emit('row-click', event);
const onRowDblClick = (event: any) => emit('row-dblclick', event);
const onRowContextMenu = (event: any) => {
  if (props.contextMenu && cm.value) {
    cm.value.show(event.originalEvent);
  }
  emit('row-contextmenu', event);
};
const onCellEditComplete = (event: any) => emit('cell-edit-complete', event);
const onRowEditSave = (event: any) => emit('row-edit-save', event);
const onRowEditCancel = (event: any) => emit('row-edit-cancel', event);

/**
 * Expose methods
 */
defineExpose({
  exportCSV,
  refresh: handleRefresh
});
</script>

<style scoped lang="scss">
.data-table {
  &__toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    border-bottom: 0;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    gap: 1rem;
    flex-wrap: wrap;

    &-left,
    &-right {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
  }

  &__search {
    width: 20rem;
  }

  &__bulk-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  &__selection-count {
    color: var(--text-color-secondary);
    font-size: 0.875rem;
  }

  &__column-toggle {
    width: 10rem;
  }

  &__actions {
    display: flex;
    gap: 0.25rem;
  }

  &__image {
    width: 3rem;
    height: 3rem;
    object-fit: cover;
    border-radius: var(--border-radius);
  }

  &__empty,
  &__loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--text-color-secondary);

    i {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    p {
      margin: 0;
      font-size: 1.125rem;
    }
  }

  &__loading i {
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

:deep(.p-datatable) {
  .p-datatable-header {
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    border-bottom: 0;
    padding: 1rem;
  }

  .p-datatable-footer {
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    border-top: 0;
    padding: 1rem;
  }

  .p-datatable-thead > tr > th {
    background: var(--surface-50);
  }

  &.p-datatable-sm {
    .p-datatable-tbody > tr > td {
      padding: 0.25rem 0.5rem;
    }
  }

  &.p-datatable-lg {
    .p-datatable-tbody > tr > td {
      padding: 1rem 1.25rem;
    }
  }
}
</style>
