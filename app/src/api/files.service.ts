import api from './index'

export interface FileItem {
  id: string
  file_container_id: string
  uploader_id: number
  uploader_type: string
  filename: string
  original_filename: string
  file_path: string
  file_size: number
  mime_type: string
  file_type: 'contract' | 'invoice' | 'identity' | 'verification' | 'other'
  description?: string
  verified: boolean
  verified_at?: string
  verified_by?: number
  uploader?: any
  verified_by_user?: any
  created_at: string
  updated_at: string
}

export interface FileUploadData {
  file: File
  container_type: 'investment' | 'solar_plant' | 'user'
  container_id: string
  file_type: 'contract' | 'invoice' | 'identity' | 'verification' | 'other'
  description?: string
}

export interface FileFilters {
  container_type: 'investment' | 'solar_plant' | 'user'
  container_id: string
  file_type?: string
  verified?: boolean
}

export const filesService = {
  /**
   * Upload a file
   */
  async upload(data: FileUploadData) {
    const formData = new FormData()
    formData.append('file', data.file)
    formData.append('container_type', data.container_type)
    formData.append('container_id', data.container_id)
    formData.append('file_type', data.file_type)
    if (data.description) {
      formData.append('description', data.description)
    }

    const response = await api.post('/api/v1/files/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data.data
  },

  /**
   * List files in a container
   */
  async getFiles(filters: FileFilters): Promise<FileItem[]> {
    const response = await api.get('/api/v1/files', { params: filters })
    return response.data.data
  },

  /**
   * Download a file
   */
  async download(fileId: string) {
    const response = await api.get(`/api/v1/files/${fileId}/download`, {
      responseType: 'blob',
    })
    return response.data
  },

  /**
   * Delete a file
   */
  async delete(fileId: string) {
    const response = await api.delete(`/api/v1/files/${fileId}`)
    return response.data
  },

  /**
   * Verify a file (admin/manager only)
   */
  async verify(fileId: string) {
    const response = await api.post(`/api/v1/files/${fileId}/verify`)
    return response.data.data
  },

  /**
   * Helper method to trigger file download
   */
  triggerDownload(blob: Blob, filename: string) {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  },
}

export default filesService
