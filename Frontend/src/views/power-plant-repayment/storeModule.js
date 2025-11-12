import axios from '@axios'

export default {
  namespaced: true,
  state: {
    updatedAt: new Date().getTime() + 100000,
    galleryUpdatedAd: 0,
    powerBillSavedAt: 0,
    propertyOwnerFetchedAd: 0,
    contractsGeneratedAt: 0,
    calculationAndLetterChangedAt: 0,
    contractsChangedAt: 0,
    contractRepaidSum: 0,
    repaymentDataUpdatedAt: 0,
    tempRefreshListAt: 0,
  },
  getters: {
    solarPlantUpdatedAt: state => state.updatedAt,
    solarPlantGalleryUpdatedAt: state => state.galleryUpdatedAd,
    powerBillSavedAt: state => state.powerBillSavedAt,
    propertyOwnerFetchedAd: state => state.propertyOwnerFetchedAd,
    contractsGeneratedAt: state => state.contractsGeneratedAt,
    calculationAndLetterChangedAt: state => state.calculationAndLetterChangedAt,
    contractsChangedAt: state => state.contractsChangedAt,
    contractRepaidSum: state => state.contractRepaidSum,
    repaymentDataUpdatedAt: state => state.repaymentDataUpdatedAt,
    tempRefreshListAt: state => state.tempRefreshListAt,
  },
  mutations: {
    updateSolarPlantUpdatedAt: state => {
      state.updatedAt = new Date().getTime()
    },
    updateSolarPlantGalleryUpdatedAt: state => {
      state.galleryUpdatedAd = new Date().getTime()
    },
    updatePowerBillSavedAt: state => {
      state.powerBillSavedAt = new Date().getTime()
    },
    updatePropertyOwnerFetchedAd: state => {
      state.propertyOwnerFetchedAd = new Date().getTime()
    },
    updateContractsGeneratedAt: state => {
      state.contractsGeneratedAt = new Date().getTime()
    },
    updateCalculationAndLetterChangedAt: state => {
      state.calculationAndLetterChangedAt = new Date().getTime()
    },
    updateContractsChangedAt: state => {
      state.contractsChangedAt = new Date().getTime()
    },
    updateContractRepaidSum: (state, newValue) => {
      state.contractRepaidSum = newValue
    },
    updateRepaymentDataUpdatedAt: state => {
      state.repaymentDataUpdatedAt = new Date().getTime()
    },
    updateTempRefreshListAt: state => {
      state.tempRefreshListAt = new Date().getTime()
    },
  },
  actions: {
    fetchList(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/solar-plant/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchListCsv(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/solar-plant/list-csv', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deletePlant(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/delete-plant/${plantId}`)
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

    //  to be deprecated
    cloneItem(ctx, { plantId, userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/clone/${plantId}/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    clonePlant(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/clone-plant/${plantId}`)
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

    fetchGalleryPlan(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-gallery-plan/${id}`)
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
          .get(`solar-plant/get-tariff/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchCampaign(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`solar-plant/get-campaign/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    //  to be deprecated
    updateFileStatus(ctx, { plantId, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/update-file-status/${plantId}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updatePlantStatus(ctx, { plantId, type, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/update-plant-status/${plantId}/${type}/${status}`)
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

    //  :href="`${apiUrl}/file-generator/project-participation/${$router.currentRoute.params.id}/${User.id}`"
    generateLetter(ctx, { plantId, userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/project-participation/${plantId}/${userId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  :href="`${apiUrl}/file-generator/contract-energy-saving/${$router.currentRoute.params.id}/${User.id}`"
    generateEnergySavingContract(ctx, { plantId, userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/contract-energy-saving/${plantId}/${userId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  :href="`${apiUrl}/file-generator/contract-billing-sheet/${$router.currentRoute.params.id}/${User.id}`"
    generateContractBillingSheet(ctx, { plantId, userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/contract-billing-sheet/${plantId}/${userId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  ${apiUrl}/file-generator/mandate-completion/${$router.currentRoute.params.id}
    generateMandateCompletion(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/mandate-completion/${plantId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  :href="`${apiUrl}/file-generator/mandate-billing/${$router.currentRoute.params.id}/false`"
    generateMandateBilling(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/mandate-billing/${plantId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  :href="`${apiUrl}/file-generator/mandate-billing-net/${$router.currentRoute.params.id}/false`"
    generateMandateBillingNet(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/mandate-billing-net/${plantId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    //  :href="`${apiUrl}/file-generator/mandate-billing-net/${$router.currentRoute.params.id}/false`"
    generateSepa(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/file-generator/sepa/${plantId}/true`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getPowerBillData(ctx, { id }) {
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
    updateInspection(ctx, { plantId, postData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/inspection-update/${plantId}`, postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    finishInspection(ctx, { plantId, postData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/inspection-finish/${plantId}`, postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updateWorkflowStatus(ctx, { plantId, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/update-workflow-status/${plantId}/${status}/skipEvent`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    /* this was added later on! Not planed -- asu */
    updateWorkflowStatusEventNotify(ctx, { plantId, status, email }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/update-workflow-status/${plantId}/${status}/${email}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    saveComment(ctx, { id, comment }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/update-container-comment/${id}`, comment)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    simpleStatusUpdate(ctx, { id, status, value }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/simple-status-update/${id}/${status}/${value}`)
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
    fetchCampaignOptions() {
      return new Promise((resolve, reject) => {
        axios
          .get('campaign/get-active-settings')
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

    getCampaignDropdown() {
      return new Promise((resolve, reject) => {
        axios
          .get('campaign/get-dropdown')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getTariffDropdown() {
      return new Promise((resolve, reject) => {
        axios
          .get('settings/get-dropdown')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    checkUploadedDocuments(ctx, { id, type }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/segment-upload-check/${id}/${type}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    // actions end -- do clean up or merge with
    fetchRepaymentLog(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/get-repayment-log/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchRepaymentData(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/get-repayment-data/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getFileBaseGenerated(ctx, { id, type }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-generated-files/${id}/${type}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchListRepayment(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/plant-repayment/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchRepaymentStats(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/plant-repayment/stats', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addEditPlantRepayment(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/plant-repayment/add-edit', postData.postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    markPaid(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/process-repayment-data/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    markPaidMultiple(ctx, { idPlant, calculationYear, calculationYearPeriod }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/process-repayment-multiple/${idPlant}/${calculationYear}/${calculationYearPeriod}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getPlantReminderList(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/repayment-reminder-list/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    generateLog(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/process-repayment-data-single/${plantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteLog(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/delete-log/${plantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    generateRepayment(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/process-repayment-log-reminders-single/${plantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteRepayment(ctx, { plantId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`plant-repayment/delete-repayment/${plantId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
