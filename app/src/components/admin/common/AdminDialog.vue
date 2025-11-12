<template>
  <Dialog
    v-model:visible="visible"
    :modal="modal"
    :header="header"
    :style="dialogStyle"
    :breakpoints="breakpoints"
    :closable="closable"
    :dismissableMask="dismissableMask"
    :closeOnEscape="closeOnEscape"
    :class="dialogClass"
    @update:visible="handleVisibleChange"
  >
    <template v-if="$slots.header" #header>
      <slot name="header" />
    </template>
    
    <div class="dialog-content">
      <slot />
    </div>
    
    <template v-if="showFooter" #footer>
      <slot name="footer">
        <Button
          v-if="showCancel"
          :label="cancelLabel"
          severity="secondary"
          outlined
          @click="handleCancel"
          :disabled="loading"
        />
        <Button
          v-if="showConfirm"
          :label="confirmLabel"
          :severity="confirmSeverity"
          @click="handleConfirm"
          :loading="loading"
          :disabled="disabled || loading"
        />
      </slot>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

interface Props {
  modelValue: boolean
  header?: string
  width?: string
  modal?: boolean
  closable?: boolean
  dismissableMask?: boolean
  closeOnEscape?: boolean
  showFooter?: boolean
  showCancel?: boolean
  showConfirm?: boolean
  cancelLabel?: string
  confirmLabel?: string
  confirmSeverity?: 'primary' | 'success' | 'info' | 'warning' | 'danger'
  loading?: boolean
  disabled?: boolean
  dialogClass?: string
}

const props = withDefaults(defineProps<Props>(), {
  width: '500px',
  modal: true,
  closable: true,
  dismissableMask: false,
  closeOnEscape: true,
  showFooter: true,
  showCancel: true,
  showConfirm: true,
  cancelLabel: 'Cancel',
  confirmLabel: 'Confirm',
  confirmSeverity: 'primary',
  loading: false,
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
  cancel: []
}>()

const visible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const dialogStyle = computed(() => ({
  width: props.width,
  maxWidth: '95vw'
}))

const breakpoints = {
  '960px': '75vw',
  '640px': '95vw'
}

const handleVisibleChange = (value: boolean) => {
  emit('update:modelValue', value)
}

const handleConfirm = () => {
  if (!props.loading && !props.disabled) {
    emit('confirm')
  }
}

const handleCancel = () => {
  emit('cancel')
  visible.value = false
}
</script>

<style scoped lang="scss">
.dialog-content {
  padding: 0.5rem 0;
}

:deep(.p-dialog) {
  .p-dialog-header {
    padding: 1.5rem;
    background: var(--surface-section);
    border-bottom: 1px solid var(--surface-border);
  }
  
  .p-dialog-content {
    padding: 1.5rem;
    background: var(--surface-section);
  }
  
  .p-dialog-footer {
    padding: 1.5rem;
    background: var(--surface-section);
    border-top: 1px solid var(--surface-border);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
  }
}
</style>
