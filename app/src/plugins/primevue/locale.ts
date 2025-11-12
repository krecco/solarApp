/**
 * PrimeVue Locale Configuration
 * Centralized locale strings for PrimeVue components
 * Can be dynamically switched based on i18n locale in the future
 */

export interface PrimeVueLocale {
  startsWith: string
  contains: string
  notContains: string
  endsWith: string
  equals: string
  notEquals: string
  noFilter: string
  filter: string
  lt: string
  lte: string
  gt: string
  gte: string
  dateIs: string
  dateIsNot: string
  dateBefore: string
  dateAfter: string
  clear: string
  apply: string
  matchAll: string
  matchAny: string
  addRule: string
  removeRule: string
  accept: string
  reject: string
  choose: string
  upload: string
  cancel: string
  dayNames: string[]
  dayNamesShort: string[]
  dayNamesMin: string[]
  monthNames: string[]
  monthNamesShort: string[]
  today: string
  weekHeader: string
  firstDayOfWeek: number
  dateFormat: string
  weak: string
  medium: string
  strong: string
  passwordPrompt: string
  emptyFilterMessage: string
  searchMessage: string
  selectionMessage: string
  emptySelectionMessage: string
  emptySearchMessage: string
  emptyMessage: string
  aria: {
    trueLabel: string
    falseLabel: string
    nullLabel: string
    star: string
    stars: string
    selectAll: string
    unselectAll: string
    close: string
    previous: string
    next: string
    navigation: string
    scrollTop: string
    moveTop: string
    moveUp: string
    moveDown: string
    moveBottom: string
    moveToTarget: string
    moveToSource: string
    moveAllToTarget: string
    moveAllToSource: string
    pageLabel: string
    firstPageLabel: string
    lastPageLabel: string
    nextPageLabel: string
    previousPageLabel: string
    rowsPerPageLabel: string
    jumpToPageDropdownLabel: string
    jumpToPageInputLabel: string
    selectRow: string
    unselectRow: string
    expandRow: string
    collapseRow: string
    showFilterMenu: string
    hideFilterMenu: string
    filterOperator: string
    filterConstraint: string
    editRow: string
    saveEdit: string
    cancelEdit: string
    listView: string
    gridView: string
    slide: string
    slideNumber: string
    zoomImage: string
    zoomIn: string
    zoomOut: string
    rotateRight: string
    rotateLeft: string
  }
}

/**
 * English locale configuration
 * Default locale for PrimeVue components
 */
export const enUS: PrimeVueLocale = {
  startsWith: 'Starts with',
  contains: 'Contains',
  notContains: 'Not contains',
  endsWith: 'Ends with',
  equals: 'Equals',
  notEquals: 'Not equals',
  noFilter: 'No Filter',
  filter: 'Filter',
  lt: 'Less than',
  lte: 'Less than or equal to',
  gt: 'Greater than',
  gte: 'Greater than or equal to',
  dateIs: 'Date is',
  dateIsNot: 'Date is not',
  dateBefore: 'Date is before',
  dateAfter: 'Date is after',
  clear: 'Clear',
  apply: 'Apply',
  matchAll: 'Match All',
  matchAny: 'Match Any',
  addRule: 'Add Rule',
  removeRule: 'Remove Rule',
  accept: 'Yes',
  reject: 'No',
  choose: 'Choose',
  upload: 'Upload',
  cancel: 'Cancel',
  dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
  dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
  monthNames: [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ],
  monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  today: 'Today',
  weekHeader: 'Wk',
  firstDayOfWeek: 0,
  dateFormat: 'mm/dd/yy',
  weak: 'Weak',
  medium: 'Medium',
  strong: 'Strong',
  passwordPrompt: 'Enter a password',
  emptyFilterMessage: 'No results found',
  searchMessage: '{0} results are available',
  selectionMessage: '{0} items selected',
  emptySelectionMessage: 'No selected item',
  emptySearchMessage: 'No results found',
  emptyMessage: 'No available options',
  aria: {
    trueLabel: 'True',
    falseLabel: 'False',
    nullLabel: 'Not Selected',
    star: '1 star',
    stars: '{star} stars',
    selectAll: 'All items selected',
    unselectAll: 'All items unselected',
    close: 'Close',
    previous: 'Previous',
    next: 'Next',
    navigation: 'Navigation',
    scrollTop: 'Scroll Top',
    moveTop: 'Move Top',
    moveUp: 'Move Up',
    moveDown: 'Move Down',
    moveBottom: 'Move Bottom',
    moveToTarget: 'Move to Target',
    moveToSource: 'Move to Source',
    moveAllToTarget: 'Move All to Target',
    moveAllToSource: 'Move All to Source',
    pageLabel: 'Page {page}',
    firstPageLabel: 'First Page',
    lastPageLabel: 'Last Page',
    nextPageLabel: 'Next Page',
    previousPageLabel: 'Previous Page',
    rowsPerPageLabel: 'Rows per page',
    jumpToPageDropdownLabel: 'Jump to Page Dropdown',
    jumpToPageInputLabel: 'Jump to Page Input',
    selectRow: 'Row Selected',
    unselectRow: 'Row Unselected',
    expandRow: 'Row Expanded',
    collapseRow: 'Row Collapsed',
    showFilterMenu: 'Show Filter Menu',
    hideFilterMenu: 'Hide Filter Menu',
    filterOperator: 'Filter Operator',
    filterConstraint: 'Filter Constraint',
    editRow: 'Row Edit',
    saveEdit: 'Save Edit',
    cancelEdit: 'Cancel Edit',
    listView: 'List View',
    gridView: 'Grid View',
    slide: 'Slide',
    slideNumber: '{slideNumber}',
    zoomImage: 'Zoom Image',
    zoomIn: 'Zoom In',
    zoomOut: 'Zoom Out',
    rotateRight: 'Rotate Right',
    rotateLeft: 'Rotate Left'
  }
}

/**
 * Get locale configuration based on language code
 * Can be extended to support multiple languages
 */
export function getLocale(lang: string = 'en'): PrimeVueLocale {
  // Add more locale mappings here as needed
  const localeMap: Record<string, PrimeVueLocale> = {
    'en': enUS,
    'en-US': enUS,
    // Add more locales here: 'es': esES, 'fr': frFR, etc.
  }
  
  return localeMap[lang] || enUS
}

export default enUS
