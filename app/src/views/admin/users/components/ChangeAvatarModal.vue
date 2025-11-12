<template>
  <AppModal
    v-model="isOpen"
    :header="$t('admin.users.changeAvatar') || 'Change Avatar'"
    :subtitle="userName ? $t('admin.users.changingAvatarFor', { name: userName }) : ''"
    icon="pi pi-image"
    width="40rem"
    :fixed-footer="true"
  >
    <div class="avatar-upload-container">
      <!-- Current Avatar Preview -->
      <div class="current-avatar-section">
        <h4 class="section-title">{{ $t('admin.users.currentAvatar') || 'Current Avatar' }}</h4>
        <div class="avatar-preview-wrapper">
          <Avatar
            v-if="currentAvatarUrl"
            :image="currentAvatarUrl"
            size="xlarge"
            shape="circle"
            class="current-avatar"
          />
          <Avatar
            v-else
            :label="getInitials(userName || '')"
            size="xlarge"
            shape="circle"
            :style="{ backgroundColor: getAvatarColor(userName || ''), color: '#fff' }"
            class="current-avatar"
          />
        </div>
      </div>

      <Divider />

      <!-- File Upload Section -->
      <div class="upload-section">
        <h4 class="section-title">{{ $t('admin.users.uploadNewAvatar') || 'Upload New Avatar' }}</h4>

        <FileUpload
          ref="fileUploadRef"
          name="avatar"
          accept="image/*"
          :maxFileSize="2000000"
          :show-upload-button="false"
          :show-cancel-button="false"
          :multiple="false"
          :custom-upload="true"
          @select="onFileSelect"
          @clear="onClear"
          :pt="{
            root: { class: 'custom-file-upload' },
            content: { class: 'file-upload-content' }
          }"
        >
          <template #empty>
            <div class="upload-placeholder">
              <i class="pi pi-cloud-upload upload-icon"></i>
              <p class="upload-text">{{ $t('admin.users.dragDropAvatar') || 'Drag and drop an image here or click to browse' }}</p>
              <p class="upload-hint">{{ $t('admin.users.avatarRestrictions') || 'JPG, PNG, GIF, WEBP (Max 2MB)' }}</p>
            </div>
          </template>
        </FileUpload>
      </div>

      <!-- Preview Section -->
      <div v-if="previewUrl" class="preview-section">
        <Divider />
        <h4 class="section-title">{{ $t('admin.users.preview') || 'Preview' }}</h4>
        <div class="avatar-preview-wrapper">
          <Avatar
            :image="previewUrl"
            size="xlarge"
            shape="circle"
            class="preview-avatar"
          />
        </div>
        <Button
          :label="$t('common.remove') || 'Remove'"
          icon="pi pi-times"
          severity="danger"
          text
          size="small"
          @click="clearPreview"
          class="remove-preview-btn"
        />
      </div>
    </div>

    <template #footer>
      <div class="flex gap-2 w-full">
        <Button
          :label="$t('common.cancel')"
          severity="secondary"
          outlined
          @click="close"
          class="flex-1"
          size="small"
          :disabled="uploading"
        />
        <Button
          :label="$t('common.confirm')"
          severity="primary"
          @click="confirmUpload"
          class="flex-1"
          size="small"
          :loading="uploading"
          :disabled="!selectedFile"
          icon="pi pi-check"
        />
      </div>
    </template>
  </AppModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'primevue/usetoast'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import FileUpload from 'primevue/fileupload'
import AppModal from '@/components/common/AppModal.vue'

interface Props {
  modelValue: boolean
  currentAvatarUrl?: string | null
  userName?: string
  userId: number
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'avatar-changed', data: { avatarUrl: string }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { t } = useI18n()
const toast = useToast()

// State
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const fileUploadRef = ref<any>(null)
const selectedFile = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const uploading = ref(false)
const isClearing = ref(false)

// Methods
const getInitials = (name: string): string => {
  if (!name) return '?'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const getAvatarColor = (name: string): string => {
  const colors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4',
    '#FFEAA7', '#DDA5E9', '#FD79A8', '#A29BFE'
  ]
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
}

const onFileSelect = (event: any) => {
  const file = event.files[0]

  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('admin.users.invalidFileType') || 'Please select a valid image file',
      life: 3000
    })
    return
  }

  // Validate file size (2MB)
  if (file.size > 2000000) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: t('admin.users.fileTooLarge') || 'File size must be less than 2MB',
      life: 3000
    })
    return
  }

  selectedFile.value = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const onClear = () => {
  // Prevent infinite recursion
  if (isClearing.value) return
  clearPreview()
}

const clearPreview = () => {
  isClearing.value = true
  selectedFile.value = null
  previewUrl.value = null
  if (fileUploadRef.value) {
    fileUploadRef.value.clear()
  }
  // Reset the flag after a short delay
  setTimeout(() => {
    isClearing.value = false
  }, 100)
}

const confirmUpload = async () => {
  if (!selectedFile.value) return

  uploading.value = true

  try {
    const formData = new FormData()
    formData.append('avatar', selectedFile.value)

    // Import the API service dynamically
    const { updateUserAvatar } = await import('@/api/admin.service')
    const response = await updateUserAvatar(props.userId, formData)

    toast.add({
      severity: 'success',
      summary: t('common.success'),
      detail: t('admin.users.avatarUpdated') || 'Avatar updated successfully',
      life: 3000
    })

    emit('avatar-changed', { avatarUrl: response.data.avatar_url })
    close()
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: t('common.error'),
      detail: error.response?.data?.message || t('admin.users.avatarUpdateError') || 'Failed to update avatar',
      life: 3000
    })
  } finally {
    uploading.value = false
  }
}

const close = () => {
  if (!uploading.value) {
    clearPreview()
    isOpen.value = false
  }
}

// Reset state when modal is closed
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    clearPreview()
  }
})
</script>

<style scoped lang="scss">
.avatar-upload-container {
  padding: 0;
}

.current-avatar-section,
.upload-section,
.preview-section {
  margin-bottom: 1rem;
}

.section-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.avatar-preview-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
}

.current-avatar,
.preview-avatar {
  width: 120px !important;
  height: 120px !important;
  font-size: 2.5rem !important;
}

.upload-placeholder {
  text-align: center;
  padding: 3rem 1rem;
  border: 2px dashed var(--surface-border);
  border-radius: 8px;
  background: var(--surface-50);
  transition: all 0.3s ease;
  cursor: pointer;

  &:hover {
    border-color: var(--primary-color);
    background: var(--primary-50);
  }

  .dark & {
    background: var(--surface-800);

    &:hover {
      background: var(--surface-700);
    }
  }
}

.upload-icon {
  font-size: 3rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
  display: block;
}

.upload-text {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.upload-hint {
  font-size: 0.875rem;
  color: var(--text-color-secondary);
  margin: 0;
}

.remove-preview-btn {
  display: block;
  margin: 1rem auto 0;
}

:deep(.custom-file-upload) {
  .p-fileupload-buttonbar {
    display: none;
  }

  .file-upload-content {
    padding: 0;
    border: none;
    background: transparent;
  }

  .p-fileupload-content {
    padding: 0;
    border: none;
    background: transparent;
  }
}
</style>
