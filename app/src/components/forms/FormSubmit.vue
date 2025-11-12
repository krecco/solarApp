<template>
  <div class="form-submit">
    <Button
      :type="type"
      :label="label"
      :icon="currentIcon"
      :loading="loading"
      :disabled="disabled || loading"
      :severity="severity"
      :size="size"
      :outlined="outlined"
      :rounded="rounded"
      :text="text"
      :raised="raised"
      :class="buttonClass"
      @click="handleClick"
    />
    <Button
      v-if="showCancel"
      type="button"
      :label="cancelLabel"
      :icon="cancelIcon"
      :disabled="loading"
      severity="secondary"
      :size="size"
      :outlined="!cancelOutlined"
      :text="cancelText"
      :class="cancelButtonClass"
      @click="handleCancel"
    />
    <slot name="extra" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Button from 'primevue/button';

/**
 * FormSubmit Component Props
 */
interface FormSubmitProps {
  type?: 'submit' | 'button' | 'reset';
  label?: string;
  icon?: string;
  loadingIcon?: string;
  loading?: boolean;
  disabled?: boolean;
  severity?: 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'danger' | 'help' | 'contrast';
  size?: 'small' | 'normal' | 'large';
  outlined?: boolean;
  rounded?: boolean;
  text?: boolean;
  raised?: boolean;
  showCancel?: boolean;
  cancelLabel?: string;
  cancelIcon?: string;
  cancelOutlined?: boolean;
  cancelText?: boolean;
  fullWidth?: boolean;
  alignment?: 'left' | 'center' | 'right' | 'between';
}

const props = withDefaults(defineProps<FormSubmitProps>(), {
  type: 'submit',
  label: 'Submit',
  icon: 'pi pi-check',
  loadingIcon: 'pi pi-spinner pi-spin',
  loading: false,
  disabled: false,
  severity: 'primary',
  size: 'normal',
  outlined: false,
  rounded: false,
  text: false,
  raised: true,
  showCancel: false,
  cancelLabel: 'Cancel',
  cancelIcon: 'pi pi-times',
  cancelOutlined: true,
  cancelText: false,
  fullWidth: false,
  alignment: 'left'
});

const emit = defineEmits<{
  click: [event: MouseEvent];
  cancel: [event: MouseEvent];
}>();

/**
 * Current icon (changes when loading)
 */
const currentIcon = computed(() => {
  return props.loading ? props.loadingIcon : props.icon;
});

/**
 * Button classes
 */
const buttonClass = computed(() => ({
  'p-button-sm': props.size === 'small',
  'p-button-lg': props.size === 'large',
  'w-full': props.fullWidth
}));

/**
 * Cancel button classes
 */
const cancelButtonClass = computed(() => ({
  'p-button-sm': props.size === 'small',
  'p-button-lg': props.size === 'large',
  'ml-2': props.alignment !== 'between'
}));

/**
 * Handle click event
 */
const handleClick = (event: MouseEvent) => {
  if (!props.loading && !props.disabled) {
    emit('click', event);
  }
};

/**
 * Handle cancel event
 */
const handleCancel = (event: MouseEvent) => {
  if (!props.loading) {
    emit('cancel', event);
  }
};
</script>

<style scoped lang="scss">
.form-submit {
  display: flex;
  gap: 0.5rem;
  
  &--left {
    justify-content: flex-start;
  }
  
  &--center {
    justify-content: center;
  }
  
  &--right {
    justify-content: flex-end;
  }
  
  &--between {
    justify-content: space-between;
  }
}

:deep(.p-button) {
  min-width: 100px;
  
  &.w-full {
    width: 100%;
  }
}
</style>
