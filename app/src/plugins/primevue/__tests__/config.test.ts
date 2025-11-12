/**
 * Example test file demonstrating the benefits of modular PrimeVue configuration
 * This shows how the new structure improves testability
 */

import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createApp } from 'vue'
import { getPrimeVueConfig, setupPrimeVue, type PrimeVueLocale } from '@/plugins/primevue'

describe('PrimeVue Configuration', () => {
  describe('getPrimeVueConfig', () => {
    it('returns default configuration when no options provided', () => {
      const config = getPrimeVueConfig()
      
      expect(config.ripple).toBe(true)
      expect(config.inputStyle).toBe('outlined')
      expect(config.locale.startsWith).toBe('Starts with')
    })

    it('allows ripple effect to be disabled', () => {
      const config = getPrimeVueConfig({ ripple: false })
      
      expect(config.ripple).toBe(false)
    })

    it('supports different locales', () => {
      const config = getPrimeVueConfig({ locale: 'en-US' })
      
      expect(config.locale.startsWith).toBe('Starts with')
      expect(config.locale.dayNames).toHaveLength(7)
    })
  })

  describe('setupPrimeVue', () => {
    let app: ReturnType<typeof createApp>

    beforeEach(() => {
      app = {
        use: vi.fn(),
        directive: vi.fn()
      } as any
    })

    it('registers PrimeVue with default config', () => {
      setupPrimeVue(app)
      
      // Check that PrimeVue was registered
      expect(app.use).toHaveBeenCalledTimes(4) // PrimeVue + 3 services
      
      // Check that directives were registered
      expect(app.directive).toHaveBeenCalledWith('tooltip', expect.any(Object))
      expect(app.directive).toHaveBeenCalledWith('ripple', expect.any(Object))
      expect(app.directive).toHaveBeenCalledWith('badge', expect.any(Object))
      expect(app.directive).toHaveBeenCalledWith('styleclass', expect.any(Object))
      expect(app.directive).toHaveBeenCalledWith('focustrap', expect.any(Object))
    })

    it('accepts custom configuration', () => {
      const customConfig = getPrimeVueConfig({ ripple: false })
      setupPrimeVue(app, customConfig)
      
      // Verify custom config was used
      const primeVueCall = (app.use as any).mock.calls[0]
      expect(primeVueCall[1].ripple).toBe(false)
    })
  })

  describe('Locale Management', () => {
    it('provides type-safe locale interface', () => {
      const testLocale: PrimeVueLocale = {
        startsWith: 'Test Start',
        contains: 'Test Contains',
        notContains: 'Test Not Contains',
        endsWith: 'Test End',
        equals: 'Test Equals',
        notEquals: 'Test Not Equals',
        noFilter: 'Test No Filter',
        filter: 'Test Filter',
        lt: 'Test Less Than',
        lte: 'Test Less Than Equal',
        gt: 'Test Greater Than',
        gte: 'Test Greater Than Equal',
        dateIs: 'Test Date Is',
        dateIsNot: 'Test Date Is Not',
        dateBefore: 'Test Date Before',
        dateAfter: 'Test Date After',
        clear: 'Test Clear',
        apply: 'Test Apply',
        matchAll: 'Test Match All',
        matchAny: 'Test Match Any',
        addRule: 'Test Add Rule',
        removeRule: 'Test Remove Rule',
        accept: 'Test Yes',
        reject: 'Test No',
        choose: 'Test Choose',
        upload: 'Test Upload',
        cancel: 'Test Cancel',
        dayNames: ['TestSun', 'TestMon', 'TestTue', 'TestWed', 'TestThu', 'TestFri', 'TestSat'],
        dayNamesShort: ['TSun', 'TMon', 'TTue', 'TWed', 'TThu', 'TFri', 'TSat'],
        dayNamesMin: ['TS', 'TM', 'TT', 'TW', 'TT', 'TF', 'TS'],
        monthNames: Array(12).fill('TestMonth'),
        monthNamesShort: Array(12).fill('TM'),
        today: 'Test Today',
        weekHeader: 'TW',
        firstDayOfWeek: 1,
        dateFormat: 'test/test/test',
        weak: 'Test Weak',
        medium: 'Test Medium',
        strong: 'Test Strong',
        passwordPrompt: 'Test Password',
        emptyFilterMessage: 'Test No Results',
        searchMessage: 'Test {0} results',
        selectionMessage: 'Test {0} selected',
        emptySelectionMessage: 'Test None Selected',
        emptySearchMessage: 'Test No Search Results',
        emptyMessage: 'Test No Options',
        aria: {
          trueLabel: 'Test True',
          falseLabel: 'Test False',
          nullLabel: 'Test Null',
          star: 'Test Star',
          stars: 'Test Stars',
          selectAll: 'Test Select All',
          unselectAll: 'Test Unselect All',
          close: 'Test Close',
          previous: 'Test Previous',
          next: 'Test Next',
          navigation: 'Test Navigation',
          scrollTop: 'Test Scroll Top',
          moveTop: 'Test Move Top',
          moveUp: 'Test Move Up',
          moveDown: 'Test Move Down',
          moveBottom: 'Test Move Bottom',
          moveToTarget: 'Test Move Target',
          moveToSource: 'Test Move Source',
          moveAllToTarget: 'Test Move All Target',
          moveAllToSource: 'Test Move All Source',
          pageLabel: 'Test Page',
          firstPageLabel: 'Test First Page',
          lastPageLabel: 'Test Last Page',
          nextPageLabel: 'Test Next Page',
          previousPageLabel: 'Test Previous Page',
          rowsPerPageLabel: 'Test Rows Per Page',
          jumpToPageDropdownLabel: 'Test Jump Dropdown',
          jumpToPageInputLabel: 'Test Jump Input',
          selectRow: 'Test Select Row',
          unselectRow: 'Test Unselect Row',
          expandRow: 'Test Expand Row',
          collapseRow: 'Test Collapse Row',
          showFilterMenu: 'Test Show Filter',
          hideFilterMenu: 'Test Hide Filter',
          filterOperator: 'Test Filter Op',
          filterConstraint: 'Test Filter Constraint',
          editRow: 'Test Edit Row',
          saveEdit: 'Test Save Edit',
          cancelEdit: 'Test Cancel Edit',
          listView: 'Test List View',
          gridView: 'Test Grid View',
          slide: 'Test Slide',
          slideNumber: 'Test Slide {slideNumber}',
          zoomImage: 'Test Zoom',
          zoomIn: 'Test Zoom In',
          zoomOut: 'Test Zoom Out',
          rotateRight: 'Test Rotate Right',
          rotateLeft: 'Test Rotate Left'
        }
      }

      // TypeScript ensures all properties are present
      expect(testLocale.startsWith).toBe('Test Start')
      expect(testLocale.aria.close).toBe('Test Close')
    })
  })
})

describe('Component Testing with PrimeVue', () => {
  it('can test components with custom PrimeVue config', async () => {
    // Example of testing a component with custom config
    const TestComponent = {
      template: '<Button label="Test" />'
    }

    // Create test config
    const testConfig = getPrimeVueConfig({ 
      ripple: false,
      locale: 'en'
    })

    // Mock the PrimeVue setup for testing
    const wrapper = mount(TestComponent, {
      global: {
        stubs: {
          Button: {
            props: ['label'],
            template: '<button>{{ label }}</button>'
          }
        },
        provide: {
          // Provide test configuration to components
          $primevue: { config: testConfig }
        }
      }
    })

    expect(wrapper.text()).toBe('Test')
  })
})

// Example of mocking PrimeVue services in tests
describe('PrimeVue Services Testing', () => {
  it('can mock Toast service', () => {
    const mockToast = {
      add: vi.fn(),
      removeAll: vi.fn(),
      remove: vi.fn()
    }

    // Use mock in component tests
    const wrapper = mount(TestComponent, {
      global: {
        provide: {
          $toast: mockToast
        }
      }
    })

    // Test toast interactions
    // ...
  })

  it('can mock Confirmation service', () => {
    const mockConfirm = {
      require: vi.fn(),
      close: vi.fn()
    }

    // Similar pattern for confirmation service
    // ...
  })
})

// Dummy component for testing
const TestComponent = {
  template: '<div>Test Component</div>'
}
