import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchStats() {
      return new Promise((resolve, reject) => {
        axios
          .get('dash/stats')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
