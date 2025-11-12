import { ref, onMounted, onUnmounted, watch } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'
import { useConfirm } from 'primevue/useconfirm'

/**
 * Composable to warn users about unsaved changes
 * Prevents accidental data loss when navigating away or closing browser
 */
export function useUnsavedChanges() {
  const confirm = useConfirm()
  const hasUnsavedChanges = ref(false)

  // Warn before closing/refreshing browser
  const beforeUnloadHandler = (e: BeforeUnloadEvent) => {
    if (hasUnsavedChanges.value) {
      e.preventDefault()
      e.returnValue = 'You have unsaved changes. Are you sure you want to leave?'
      return e.returnValue
    }
  }

  // Setup browser beforeunload listener
  onMounted(() => {
    window.addEventListener('beforeunload', beforeUnloadHandler)
  })

  onUnmounted(() => {
    window.removeEventListener('beforeunload', beforeUnloadHandler)
  })

  // Setup route leave guard
  onBeforeRouteLeave((to, from, next) => {
    if (hasUnsavedChanges.value) {
      confirm.require({
        message: 'You have unsaved changes. Are you sure you want to leave this page?',
        header: 'Unsaved Changes',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Leave',
        rejectLabel: 'Stay',
        acceptClass: 'p-button-danger',
        accept: () => {
          hasUnsavedChanges.value = false
          next()
        },
        reject: () => {
          next(false)
        }
      })
    } else {
      next()
    }
  })

  /**
   * Mark form as dirty (has unsaved changes)
   */
  const markDirty = () => {
    hasUnsavedChanges.value = true
  }

  /**
   * Mark form as clean (no unsaved changes)
   */
  const markClean = () => {
    hasUnsavedChanges.value = false
  }

  /**
   * Watch a form object for changes and automatically mark as dirty
   */
  const watchForm = (formData: any, options = { deep: true }) => {
    let isInitial = true
    watch(
      formData,
      () => {
        // Skip the initial watch trigger
        if (isInitial) {
          isInitial = false
          return
        }
        markDirty()
      },
      options
    )
  }

  return {
    hasUnsavedChanges,
    markDirty,
    markClean,
    watchForm
  }
}
