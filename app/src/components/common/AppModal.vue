<template>
  <Dialog
    :visible="modelValue"
    @update:visible="emit('update:modelValue', $event)"
    :modal="true"
    :dismissableMask="dismissableMask"
    :closable="closable"
    :closeOnEscape="closeOnEscape"
    :draggable="draggable"
    :blockScroll="true"
    :style="computedStyle"
    :breakpoints="computedBreakpoints"
    :class="['app-modal', modalClass, { 'fixed-footer': fixedFooter && $slots.footer }]"
    :pt="{
      root: { 
        class: 'app-modal-root'
      },
      mask: { 
        class: 'app-modal-mask'
      },
      header: { 
        class: 'app-modal-header'
      },
      content: {
        class: 'app-modal-content'
      },
      closeButton: { 
        class: 'app-modal-close-btn'
      }
    }"
  >
    <template #header>
      <div class="app-modal-header-content">
        <div class="app-modal-header-icon" v-if="icon || $slots.icon">
          <slot name="icon">
            <i :class="icon" v-if="icon"></i>
          </slot>
        </div>
        <div class="app-modal-header-text">
          <h3 class="app-modal-title">{{ header || title }}</h3>
          <p class="app-modal-subtitle" v-if="subtitle">{{ subtitle }}</p>
        </div>
      </div>
    </template>

    <div :class="['app-modal-body', { 'p-fluid': fluid }]">
      <slot></slot>
    </div>

    <template #footer v-if="$slots.footer">
      <slot name="footer"></slot>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Dialog from 'primevue/dialog'

interface Props {
  modelValue: boolean
  header?: string
  title?: string // Alias for header
  subtitle?: string
  icon?: string
  width?: string
  height?: string
  fluid?: boolean
  dismissableMask?: boolean
  closable?: boolean
  closeOnEscape?: boolean
  draggable?: boolean
  modalClass?: string
  breakpoints?: Record<string, string>
  fixedFooter?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  width: '50rem',
  height: 'auto',
  fluid: true,
  dismissableMask: true,
  closable: true,
  closeOnEscape: true,
  draggable: false,
  modalClass: '',
  fixedFooter: false
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

// Computed styles
const computedStyle = computed(() => ({
  width: props.width,
  maxHeight: props.height === 'auto' ? '90vh' : props.height,
  height: props.height
}))

// Default responsive breakpoints
const computedBreakpoints = computed(() => {
  return props.breakpoints || {
    '960px': '75vw',
    '640px': '90vw'
  }
})
</script>

<style lang="scss">
/* Global styles for the App Modal */
.app-modal {
  /* Modal mask overlay */
  .app-modal-mask {
    background-color: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(2px);
  }

  /* Modal container */
  .app-modal-root {
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
    display: flex !important;
    flex-direction: column !important;
  }

  /* Modal header */
  .app-modal-header {
    background: linear-gradient(to bottom, #ffffff, #fafafa) !important;
    border-bottom: 1px solid var(--surface-border) !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem !important;
    
    .dark & {
      background: linear-gradient(to bottom, var(--surface-800), var(--surface-900)) !important;
    }
  }

  /* Header content layout */
  .app-modal-header-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex: 1;
    padding-right: 2rem; // Space for close button
  }

  /* Header icon */
  .app-modal-header-icon {
    flex-shrink: 0;
    font-size: 1.25rem;
    margin-top: 0.125rem;
    
    .pi {
      font-size: 1.25rem;
    }
  }

  /* Header text */
  .app-modal-header-text {
    flex: 1;
    min-width: 0; // Allow text truncation
  }

  .app-modal-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-color);
    line-height: 1.5;
  }

  .app-modal-subtitle {
    margin: 0.25rem 0 0 0;
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    line-height: 1.4;
  }

  /* Close button styling */
  .app-modal-close-btn {
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    background: var(--surface-100) !important;
    border: 1px solid var(--surface-border) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
    opacity: 1 !important;
    position: absolute !important;
    right: 1.5rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    
    &:hover {
      background: var(--surface-200) !important;
      border-color: var(--surface-300) !important;
      transform: translateY(-50%) scale(1.05) !important;
    }
    
    &:active {
      transform: translateY(-50%) scale(0.95) !important;
    }

    .dark & {
      background: var(--surface-700) !important;
      border-color: var(--surface-600) !important;
      
      &:hover {
        background: var(--surface-600) !important;
        border-color: var(--surface-500) !important;
      }
    }
    
    /* Style the X icon */
    .p-icon {
      width: 14px !important;
      height: 14px !important;
      
      &::before {
        content: '\2715' !important;
        font-family: 'primeicons' !important;
        font-size: 14px !important;
      }
    }
  }

  /* Modal content */
  .app-modal-content {
    padding: 0 !important;
    flex: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    background: var(--surface-0) !important;
    
    .dark & {
      background: var(--surface-900) !important;
    }
  }

  /* Modal body */
  .app-modal-body {
    padding: 1.5rem;
  }

  /* Footer styling (if used) */
  .p-dialog-footer {
    background: var(--surface-50) !important;
    border-top: 1px solid var(--surface-border) !important;
    padding: 1rem 1.5rem !important;
    border-radius: 0 0 12px 12px !important;
    
    .dark & {
      background: var(--surface-800) !important;
    }
  }

  /* Smaller buttons in modals */
  .p-button {
    font-size: 0.875rem !important;
    padding: 0.5rem 1rem !important;
    
    &.p-button-icon-only {
      width: 2rem !important;
      height: 2rem !important;
      padding: 0 !important;
    }
  }

  /* Form elements in modal */
  .p-inputtext,
  .p-dropdown,
  .p-password-input {
    font-size: 0.875rem !important;
  }

  label {
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    margin-bottom: 0.5rem !important;
    display: block;
  }

  small {
    font-size: 0.75rem !important;
  }

  /* Dropdown trigger buttons */
  .p-dropdown-trigger {
    width: 2rem !important;
  }

  /* Password toggle button */
  .p-password-toggle {
    width: 2rem !important;
    height: 2rem !important;
  }
}

/* Size variants */
.app-modal {
  &.app-modal-sm .app-modal-root {
    width: 30rem !important;
  }
  
  &.app-modal-lg .app-modal-root {
    width: 65rem !important;
  }
  
  &.app-modal-xl .app-modal-root {
    width: 80rem !important;
  }
  
  &.app-modal-fullscreen .app-modal-root {
    width: calc(100vw - 2rem) !important;
    height: calc(100vh - 2rem) !important;
    max-height: calc(100vh - 2rem) !important;
  }
}

/* Fixed footer variant */
.app-modal.fixed-footer {
  .app-modal-root {
    display: flex !important;
    flex-direction: column !important;
  }
  
  .app-modal-content {
    display: flex !important;
    flex-direction: column !important;
    padding-bottom: 0 !important;
  }
  
  .app-modal-body {
    flex: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding-bottom: 1.5rem !important;
    margin-bottom: 0 !important;
    
    /* Better scrollbar styling */
    &::-webkit-scrollbar {
      width: 8px;
    }
    
    &::-webkit-scrollbar-track {
      background: var(--surface-50);
      border-radius: 4px;
    }
    
    &::-webkit-scrollbar-thumb {
      background: var(--surface-300);
      border-radius: 4px;
      
      &:hover {
        background: var(--surface-400);
      }
    }
  }
  
  .p-dialog-footer {
    position: sticky !important;
    bottom: 0 !important;
    background: var(--surface-50) !important;
    border-top: 1px solid var(--surface-border) !important;
    padding: 1rem 1.5rem !important;
    margin-top: 0 !important;
    z-index: 10 !important;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05) !important;
    
    .dark & {
      background: var(--surface-800) !important;
      box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.2) !important;
    }
    
    /* Default footer button layout */
    display: flex !important;
    gap: 0.5rem !important;
    justify-content: flex-end !important;
    align-items: center !important;
    
    /* Support for custom layouts */
    &:has(.footer-actions) {
      justify-content: space-between !important;
    }
  }
}

/* Animations */
.p-dialog-enter-active {
  transition: all 0.15s ease-out;
}

.p-dialog-leave-active {
  transition: all 0.15s ease-in;
}

.p-dialog-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.p-dialog-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.p-dialog-mask.p-component-overlay {
  &.p-dialog-enter-from,
  &.p-dialog-leave-to {
    background-color: transparent !important;
  }
}
</style>
