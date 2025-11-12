import { useToast } from 'primevue/usetoast'

/**
 * Composable to copy text to clipboard with visual feedback
 */
export function useCopyToClipboard() {
  const toast = useToast()

  /**
   * Copy text to clipboard and show toast notification
   */
  const copyToClipboard = async (text: string, label: string = 'Text') => {
    try {
      await navigator.clipboard.writeText(text)

      toast.add({
        severity: 'success',
        summary: 'Copied!',
        detail: `${label} copied to clipboard`,
        life: 2000,
        group: 'br' // Bottom right
      })

      return true
    } catch (error) {
      toast.add({
        severity: 'error',
        summary: 'Copy Failed',
        detail: 'Failed to copy to clipboard',
        life: 3000,
        group: 'br'
      })

      return false
    }
  }

  /**
   * Copy text with custom success message
   */
  const copyWithMessage = async (text: string, successMessage: string) => {
    try {
      await navigator.clipboard.writeText(text)

      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: successMessage,
        life: 2000,
        group: 'br'
      })

      return true
    } catch (error) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Could not copy to clipboard',
        life: 3000,
        group: 'br'
      })

      return false
    }
  }

  return {
    copyToClipboard,
    copyWithMessage
  }
}
