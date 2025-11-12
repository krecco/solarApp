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
      console.log(userData)
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
      console.log('updateDocument store')
      console.log(id)
      console.log(status)
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/update-document-status/${id}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getDocumentFiles(ctx, { id }) {
      console.log('getDocumentFiles store')
      console.log(id)
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/document-files/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteFile(ctx, { id }) {
      console.log('deleteFile store')
      console.log(id)
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/file-delete/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getDashboardActivity(ctx, { userId }) {
      console.log('getDashboardActivity store')
      console.log(userId)
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/user-dashboard-activity/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getActivity() {
      console.log('getActivity store')

      return new Promise((resolve, reject) => {
        axios
          .get('/user/app-activity')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addPowerPlant(ctx, { userId, postData }) {
      console.log(userId)
      console.log(postData)
      return new Promise((resolve, reject) => {
        axios
          .post(`/solar-plant/add/${userId}`, postData)
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

    getInvestmentList(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/investment-list/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getUserStatus(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-user-status/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getUserFilesStatus(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-user-files-status/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

  },
}
