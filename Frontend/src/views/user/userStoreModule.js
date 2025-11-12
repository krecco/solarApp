import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/basic-info/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUserAddress(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/address/${id}`)
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
    fetchUserPowerBill(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/user-power-bill/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    /*
    fetchUserFileContainers(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-file-containers/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    */
    fetchUserFileContainers(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-file-containers/${id}`)
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
    editAddress(ctx, { userId, addressData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/user/address/${userId}`, addressData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editBillData(ctx, { userId, billData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/user/user-power-bill/${userId}`, billData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editSepa(ctx, { userId, sepaData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/user/user-sepa-permission/${userId}`, sepaData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addUser(ctx, userData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/user/add-user-backend', userData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editUser(ctx, userData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/user/basic-info-edit', userData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getPlantInfo(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    generateDocument1(ctx, { ida, idb }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/power-forecast-calculation/${ida}/${idb}`)
          .then(response => resolve(response))
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

    getDashboardActivity(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/user-dashboard-activity/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    // move to separate store
    getActivity(ctx, { page }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/app-activity/${page}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    setActivityAsRead() {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/app-activity-read-all/')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getActivityNew() {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/app-activity/new')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updateActivity(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/app-activity-status/${id}/1`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addPowerPlant(ctx, { userId, postData }) {
      /*
      let campaignId = null
      if (typeof postData.campaign !== 'undefined') {
        if (typeof postData.campaign.value !== 'undefined') {
          campaignId = postData.campaign.value
        }
      }
      */
      //  tariff: postData.tariff.value,
      const cleanPostData = {
        nominalPower: postData.nominalPower,
        campaignId: postData.campaign.value,
        title: postData.title,
      }
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/add/${userId}`, cleanPostData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getPowerPlantList(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/get-user-list/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updateUserFileStatus(ctx, { userId, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/update-user-file-status/${userId}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addInvestmennt(ctx, { userId, postData }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/user/investment-add/${userId}`, postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getInvestmentList(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/investment-list/${userId}`)
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
    fetchCampaignOptions() {
      return new Promise((resolve, reject) => {
        axios
          .get('campaign/get-active-settings')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchWebInfoPlantSize(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/webinfo-plant-size/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    deleteUser(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/delete-user/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    passwordReset(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/password-reset/${userId}`)
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
  },
}
