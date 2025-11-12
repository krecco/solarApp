import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchWebInfoList(ctx, { status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`web/info/list/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    updateWebInfoListStatus(ctx, { id, status }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`web/info/change-status/${id}/${status}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    createUserFromWebInfo(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`web/info/create-user/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
