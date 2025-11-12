import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    //  demo actions
    fetchList(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/solar-plant/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addItem(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/solar-plant/add', postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchBaseData(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    editBaseData(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/solar-plant/edit', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchFileContainers(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-file-containers/${id}`)
          .then(response => {
            response.data.payload.forEach(element => {
              /* eslint-disable no-param-reassign */
              element.files = []
              /* eslint-ebable no-param-reassign */
            })

            resolve(response)
          })
          .catch(error => reject(error))
      })
    },

    fetchGallery(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-gallery/${id}`)
          .then(response => {
            resolve(response)
          })
          .catch(error => reject(error))
      })
    },

    updateDocumentStatus(ctx, { id, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/update-document-status/${id}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getDocumentFiles(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/document-files/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteFile(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/file-delete/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    mapUserToProject(ctx, { userId, solarPlantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/add-user-to-project/${userId}/${solarPlantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getProjectUser(ctx, { solarPlantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-project-user/${solarPlantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getPropertyOwnerList(ctx, { solarPlantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/property-owner-list/${solarPlantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    removeOwnerFromProperty(ctx, { ownerId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/property-owner-remove/${ownerId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addOwnerToProperty(ctx, { solarPlantId, postData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/property-owner-add/${solarPlantId}`, postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchTarrifeOptions() {
      return new Promise((resolve, reject) => {
        axios
          .get('settings/get-active-settings')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchTarriff(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`solar-plant/get-tariff-frontend/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateFileStatus(ctx, { plantId, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/update-file-status/${plantId}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    generateCalculation(ctx, { plantId, userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/power-forecast-calculation/${plantId}/${userId}/true/prognoserechnung`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getPowerBillData(ctx, { id }) {
      console.log(id)
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/power-bill/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editBillData(ctx, { plantId, billData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/power-bill/${plantId}`, billData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getProgressStatus(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-progress-status/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    simpleStatusUpdateFrontend(ctx, { id, status, value }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/simple-status-update-frontend/${id}/${status}/${value}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    plantContractStatus(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-contract-status/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getFileBase(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/file-base/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getContractDownloadStatus(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/contract-download-status/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getContractUploadStatus(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/contract-upload-status/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getOfferDownloadStatus(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/offer-download-status/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUserSepa(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/user-sepa-permission/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editSepa(ctx, { userId, sepaData }) {
      console.log(userId)
      return new Promise((resolve, reject) => {
        axios
          .post(`/user/user-sepa-permission/${userId}`, { account: sepaData.value.account, bic: sepaData.value.bic, fullName: sepaData.value.fullName })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateViewBy(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/last-view/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchPlantPreviewInfo(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/plant-preview-info/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    sendMessageToBackoffice(ctx, { plantId, message }) {
      console.log(message)
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/frontend-calculation-message/${plantId}`, message)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    // actions end
  },
}
