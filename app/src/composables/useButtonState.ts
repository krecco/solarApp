import { ref, computed } from 'vue'

export type ButtonState = 'idle' | 'loading' | 'success' | 'error'

/**
 * Composable for managing button states with visual feedback
 * Shows loading → success → idle transitions
 */
export function useButtonState(options: {
  successDuration?: number
  errorDuration?: number
} = {}) {
  const { successDuration = 2000, errorDuration = 3000 } = options

  const state = ref<ButtonState>('idle')

  const isIdle = computed(() => state.value === 'idle')
  const isLoading = computed(() => state.value === 'loading')
  const isSuccess = computed(() => state.value === 'success')
  const isError = computed(() => state.value === 'error')

  const label = computed(() => {
    switch (state.value) {
      case 'loading':
        return 'Saving...'
      case 'success':
        return 'Saved'
      case 'error':
        return 'Error'
      default:
        return 'Save'
    }
  })

  const icon = computed(() => {
    switch (state.value) {
      case 'loading':
        return 'pi pi-spin pi-spinner'
      case 'success':
        return 'pi pi-check'
      case 'error':
        return 'pi pi-times'
      default:
        return undefined
    }
  })

  const severity = computed(() => {
    switch (state.value) {
      case 'success':
        return 'success'
      case 'error':
        return 'danger'
      default:
        return 'primary'
    }
  })

  /**
   * Execute an async action with automatic state management
   */
  const execute = async <T>(action: () => Promise<T>): Promise<T | undefined> => {
    try {
      state.value = 'loading'
      const result = await action()
      state.value = 'success'

      // Reset to idle after success duration
      setTimeout(() => {
        if (state.value === 'success') {
          state.value = 'idle'
        }
      }, successDuration)

      return result
    } catch (error) {
      state.value = 'error'

      // Reset to idle after error duration
      setTimeout(() => {
        if (state.value === 'error') {
          state.value = 'idle'
        }
      }, errorDuration)

      throw error
    }
  }

  /**
   * Manually set state to loading
   */
  const setLoading = () => {
    state.value = 'loading'
  }

  /**
   * Manually set state to success
   */
  const setSuccess = () => {
    state.value = 'success'
    setTimeout(() => {
      if (state.value === 'success') {
        state.value = 'idle'
      }
    }, successDuration)
  }

  /**
   * Manually set state to error
   */
  const setError = () => {
    state.value = 'error'
    setTimeout(() => {
      if (state.value === 'error') {
        state.value = 'idle'
      }
    }, errorDuration)
  }

  /**
   * Reset to idle state
   */
  const reset = () => {
    state.value = 'idle'
  }

  return {
    state,
    isIdle,
    isLoading,
    isSuccess,
    isError,
    label,
    icon,
    severity,
    execute,
    setLoading,
    setSuccess,
    setError,
    reset
  }
}
