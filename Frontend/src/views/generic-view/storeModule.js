import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    //  demo actions
    fetchDemoList(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/solar-plant/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    addDemoItem(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/solar-plant/add', postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchBaseData(ctx, { id }) {
      console.log(id)
      return new Promise((resolve, reject) => {
        axios
          .get(`/solar-plant/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    editBaseData(ctx, postData) {
      console.log(postData)
      return new Promise((resolve, reject) => {
        axios
          .post('/solar-plant/edit', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    // actions end
  },
}
