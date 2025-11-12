import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchBaseData(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`user/investment-get/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchCalculationData(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`investment/investment-calculation/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchList(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/investment/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    editBaseData(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/user/investment-update', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchFileContainers(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/investment-file-containers/${id}`)
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

    updateDocumentStatus(ctx, { id, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/update-document-status/${id}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updateInvestmentStatus(ctx, { plantId, type, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/update-investment-status/${plantId}/${type}/${status}`)
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

    updateFileStatus(ctx, { plantId, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/investment-update-file-status/${plantId}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getInvestmentUser(ctx, { investmentId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/get-investment-user/${investmentId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteInvestment(ctx, { investmentId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/investment/delete-investment/${investmentId}`)
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
