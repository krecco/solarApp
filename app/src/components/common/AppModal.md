# AppModal Component

A beautiful, reusable modal component for the Admin Panel V2 application that follows the project's design standards and supports i18n.

## Features

- 🎨 Beautiful modern design with glassmorphism effects
- 🌐 Full i18n support
- 🌓 Dark mode compatible
- 📱 Responsive with customizable breakpoints
- 🎯 Icon support in header
- 📝 Subtitle support
- ⚡ Smooth animations
- 🔧 Highly customizable

## Usage

```vue
<template>
  <AppModal
    v-model="showModal"
    :header="$t('common.editUser')"
    :subtitle="$t('common.editingUser', { name: user.name })"
    icon="pi pi-user-edit"
    width="50rem"
    height="auto"
  >
    <!-- Your content here -->
    <YourFormComponent />
  </AppModal>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AppModal from '@/components/common/AppModal.vue'

const showModal = ref(false)
</script>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `boolean` | `false` | v-model binding for visibility |
| `header` | `string` | - | Modal header title |
| `title` | `string` | - | Alias for header |
| `subtitle` | `string` | - | Optional subtitle text |
| `icon` | `string` | - | PrimeIcons class (e.g., 'pi pi-user') |
| `width` | `string` | `'50rem'` | Modal width |
| `height` | `string` | `'auto'` | Modal height |
| `fluid` | `boolean` | `true` | Apply p-fluid class to body |
| `dismissableMask` | `boolean` | `true` | Close on mask click |
| `closable` | `boolean` | `true` | Show close button |
| `closeOnEscape` | `boolean` | `true` | Close on ESC key |
| `modalClass` | `string` | `''` | Additional CSS classes |
| `breakpoints` | `object` | `{ '960px': '75vw', '640px': '90vw' }` | Responsive breakpoints |

## Slots

### Default Slot
The main content of the modal.

```vue
<AppModal v-model="show">
  <p>Your content here</p>
</AppModal>
```

### Icon Slot
Custom icon content (overrides the `icon` prop).

```vue
<AppModal v-model="show">
  <template #icon>
    <img src="/custom-icon.svg" />
  </template>
</AppModal>
```

### Footer Slot
Custom footer content.

```vue
<AppModal v-model="show">
  <template #footer>
    <Button label="Cancel" @click="show = false" />
    <Button label="Save" />
  </template>
</AppModal>
```

## Size Variants

The modal supports various sizes through the `modalClass` prop:

- `app-modal-sm` - Small modal (30rem width)
- `app-modal-lg` - Large modal (65rem width)
- `app-modal-xl` - Extra large modal (80rem width)
- `app-modal-fullscreen` - Fullscreen modal

```vue
<AppModal v-model="show" modalClass="app-modal-lg">
  <!-- Content -->
</AppModal>
```

## Examples

### Basic Modal
```vue
<AppModal
  v-model="showModal"
  :header="$t('common.details')"
>
  <p>Simple modal content</p>
</AppModal>
```

### Modal with Icon and Subtitle
```vue
<AppModal
  v-model="showEditModal"
  :header="$t('users.editUser')"
  :subtitle="$t('users.editingUser', { name: selectedUser.name })"
  icon="pi pi-user-edit"
>
  <UserEditForm :user="selectedUser" />
</AppModal>
```

### Fullscreen Modal
```vue
<AppModal
  v-model="showFullscreen"
  :header="$t('reports.fullReport')"
  modalClass="app-modal-fullscreen"
>
  <ReportViewer />
</AppModal>
```

### Modal with Custom Footer
```vue
<AppModal
  v-model="showConfirm"
  :header="$t('common.confirm')"
  icon="pi pi-exclamation-triangle"
  width="30rem"
>
  <p>{{ $t('messages.confirm.delete') }}</p>
  
  <template #footer>
    <Button 
      :label="$t('common.cancel')" 
      severity="secondary"
      @click="showConfirm = false"
    />
    <Button 
      :label="$t('common.delete')" 
      severity="danger"
      @click="handleDelete"
    />
  </template>
</AppModal>
```

## Styling

The component uses CSS classes that integrate with the project's design system:

- Uses PrimeVue's Dialog component as the base
- Integrates with the project's theme variables
- Supports dark mode automatically
- Uses glassmorphism effects for modern look

## Migration Guide

If you're migrating from a custom Dialog implementation:

1. Replace `<Dialog>` with `<AppModal>`
2. Update props:
   - `visible` → use `v-model` instead
   - Custom `:pt` props → removed (styling is built-in)
   - `:style` → use `width` and `height` props
3. Remove any custom CSS - the component handles all styling
4. Add `icon` and `subtitle` props for enhanced UX

### Before:
```vue
<Dialog
  v-model:visible="showModal"
  :header="$t('users.editUser')"
  :modal="true"
  :style="{ width: '800px' }"
  class="custom-modal"
>
  <!-- Complex styling and overrides -->
</Dialog>
```

### After:
```vue
<AppModal
  v-model="showModal"
  :header="$t('users.editUser')"
  :subtitle="$t('users.editingUser', { name: user.name })"
  icon="pi pi-user-edit"
  width="50rem"
>
  <!-- Clean and simple -->
</AppModal>
```

## Best Practices

1. **Always use i18n keys** for headers and subtitles
2. **Use appropriate icons** from PrimeIcons to enhance UX
3. **Keep modals focused** - one primary action per modal
4. **Use proper widths**:
   - Forms: `50rem`
   - Confirmations: `30rem`
   - Large content: `65rem` or `app-modal-lg`
5. **Add subtitles** for context when editing/viewing specific items

## Accessibility

The component follows accessibility best practices:
- Proper ARIA attributes from PrimeVue Dialog
- Keyboard navigation (ESC to close)
- Focus management
- Screen reader friendly

## Related Components

- `Dialog` (PrimeVue) - Base component
- `Sidebar` - For side panel content
- `ConfirmDialog` - For simple confirmations
